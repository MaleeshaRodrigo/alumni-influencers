<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Blind bidding: sealed amounts per user per cycle (see bids table).
 */
class Bid_model extends CI_Model {

	protected $table = 'bids';

	public function __construct()
	{
		parent::__construct();
		$this->load->database();
	}

	public function get_by_id($id)
	{
		return $this->db
			->where('id', (int) $id)
			->limit(1)
			->get($this->table)
			->row_array();
	}

	public function get_by_user_and_cycle($user_id, $cycle_id)
	{
		return $this->db
			->where('user_id', (int) $user_id)
			->where('cycle_id', (int) $cycle_id)
			->limit(1)
			->get($this->table)
			->row_array();
	}

	public function create_draft($user_id, $cycle_id, array $data = array())
	{
		$payload = array_merge(array(
			'user_id' => (int) $user_id,
			'cycle_id' => (int) $cycle_id,
			'amount' => 0,
			'currency' => 'GBP',
			'status' => 'draft'
		), $data);

		$ok = $this->db->insert($this->table, $payload);
		if (!$ok) {
			return FALSE;
		}

		return (int) $this->db->insert_id();
	}

	public function submit($id, array $data = array())
	{
		$payload = array_merge(array(
			'status' => 'submitted',
			'submitted_at' => date('Y-m-d H:i:s')
		), $data);

		return $this->db
			->where('id', (int) $id)
			->update($this->table, $payload);
	}

	public function withdraw($id, $user_id)
	{
		return $this->db
			->where('id', (int) $id)
			->where('user_id', (int) $user_id)
			->update($this->table, array('status' => 'withdrawn'));
	}

	public function list_for_cycle($cycle_id, $include_amounts = FALSE)
	{
		$this->db
			->where('cycle_id', (int) $cycle_id)
			->where_in('status', array('submitted', 'won', 'lost'))
			->order_by('created_at', 'ASC');

		if (!$include_amounts) {
			$this->db->select('id, user_id, cycle_id, status, submitted_at, revealed_at, created_at, updated_at');
		}

		return $this->db->get($this->table)->result_array();
	}

	public function list_winners_for_cycle($cycle_id)
	{
		return $this->db
			->where('cycle_id', (int) $cycle_id)
			->where('status', 'won')
			->get($this->table)
			->result_array();
	}

	public function update_status($id, $status, array $extra = array())
	{
		$payload = array_merge(array('status' => (string) $status), $extra);
		return $this->db
			->where('id', (int) $id)
			->update($this->table, $payload);
	}

	public function mark_revealed($id, ?DateTime $at = NULL)
	{
		if ($at === NULL) {
			$at = new DateTime();
		}

		return $this->db
			->where('id', (int) $id)
			->update($this->table, array(
				'revealed_at' => $at->format('Y-m-d H:i:s')
			));
	}

	public function attach_admin_note($id, $note)
	{
		return $this->db
			->where('id', (int) $id)
			->update($this->table, array(
				'admin_notes' => (string) $note
			));
	}

	public function current_cycle_id()
	{
		// Coursework rule: one blind cycle per calendar day.
		return (int) date('Ymd');
	}

	public function place_or_increase_for_cycle($user_id, $cycle_id, $new_amount, $currency = 'GBP')
	{
		$user_id = (int) $user_id;
		$cycle_id = (int) $cycle_id;
		$new_amount = (float) $new_amount;

		if ($new_amount <= 0) {
			return array('ok' => FALSE, 'error' => 'Bid must be greater than zero.');
		}

		$this->db->trans_begin();
		$row = $this->db
			->query(
				'SELECT * FROM `'.$this->table.'` WHERE `user_id` = ? AND `cycle_id` = ? LIMIT 1 FOR UPDATE',
				array($user_id, $cycle_id)
			)
			->row_array();

		if ($row) {
			$previous_amount = (float) $row['amount'];
			if ($new_amount <= $previous_amount) {
				$this->db->trans_rollback();
				return array(
					'ok' => FALSE,
					'error' => 'You can only increase your existing bid.',
					'previous_amount' => $previous_amount
				);
			}

			$ok = $this->db
				->where('id', (int) $row['id'])
				->update($this->table, array(
					'amount' => $new_amount,
					'currency' => strtoupper((string) $currency),
					'status' => 'submitted',
					'submitted_at' => date('Y-m-d H:i:s')
				));

			if (!$ok || $this->db->trans_status() === FALSE) {
				$this->db->trans_rollback();
				return array('ok' => FALSE, 'error' => 'Could not update your bid right now.');
			}

			$this->db->trans_commit();
			return array(
				'ok' => TRUE,
				'action' => 'increased',
				'bid_id' => (int) $row['id'],
				'previous_amount' => $previous_amount,
				'new_amount' => $new_amount
			);
		}

		$insert_ok = $this->db->insert($this->table, array(
			'user_id' => $user_id,
			'cycle_id' => $cycle_id,
			'amount' => $new_amount,
			'currency' => strtoupper((string) $currency),
			'status' => 'submitted',
			'submitted_at' => date('Y-m-d H:i:s')
		));

		if (!$insert_ok || $this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			return array('ok' => FALSE, 'error' => 'Could not place your bid right now.');
		}

		$bid_id = (int) $this->db->insert_id();
		$this->db->trans_commit();
		return array(
			'ok' => TRUE,
			'action' => 'placed',
			'bid_id' => $bid_id,
			'new_amount' => $new_amount
		);
	}

	public function blind_status_for_user_cycle($user_id, $cycle_id)
	{
		$user_bid = $this->get_by_user_and_cycle((int) $user_id, (int) $cycle_id);
		if (!$user_bid) {
			return array(
				'has_bid' => FALSE,
				'status' => 'no_bid',
				'cycle_id' => (int) $cycle_id
			);
		}

		$max_row = $this->db
			->select_max('amount', 'max_amount')
			->where('cycle_id', (int) $cycle_id)
			->where_in('status', array('submitted', 'won', 'lost'))
			->get($this->table)
			->row_array();

		$max_amount = isset($max_row['max_amount']) ? (float) $max_row['max_amount'] : 0.0;
		$is_winning = ((float) $user_bid['amount']) >= $max_amount && $max_amount > 0;

		return array(
			'has_bid' => TRUE,
			'status' => $is_winning ? 'winning' : 'losing',
			'cycle_id' => (int) $cycle_id
		);
	}
}

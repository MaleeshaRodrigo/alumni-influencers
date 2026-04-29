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
		$this->load->model('Feature_model', 'feature_model');
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

		$eligibility = $this->monthly_eligibility_for_user($user_id);
		if (!$eligibility['can_win_more']) {
			return array(
				'ok' => FALSE,
				'error' => 'Monthly featured win limit reached. Remaining slots: 0.',
				'eligibility' => $eligibility
			);
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

	public function monthly_eligibility_for_user($user_id, ?DateTime $at = NULL)
	{
		$profile_id = $this->profile_id_for_user((int) $user_id);
		if ($profile_id <= 0) {
			return array(
				'month' => ($at ?: new DateTime())->format('Y-m'),
				'wins_this_month' => 0,
				'has_event_bonus' => FALSE,
				'max_slots' => 3,
				'remaining_slots' => 0,
				'can_win_more' => FALSE,
				'reason' => 'Profile not found'
			);
		}

		return $this->feature_model->monthly_eligibility_for_profile($profile_id, $at);
	}

	public function remaining_monthly_slots_for_user($user_id, ?DateTime $at = NULL)
	{
		$eligibility = $this->monthly_eligibility_for_user((int) $user_id, $at);
		return (int) $eligibility['remaining_slots'];
	}

	public function history_for_user($user_id, $limit = 30)
	{
		$user_id = (int) $user_id;
		$limit = max(1, (int) $limit);

		$sql = '
			SELECT
				b.id,
				b.cycle_id,
				b.amount,
				b.currency,
				b.status,
				b.submitted_at,
				b.created_at,
				CASE
					WHEN b.amount >= (
						SELECT MAX(b2.amount)
						FROM `'.$this->table.'` b2
						WHERE b2.cycle_id = b.cycle_id
						  AND b2.status IN ("submitted","won","lost")
					) THEN "winning"
					ELSE "losing"
				END AS blind_status
			FROM `'.$this->table.'` b
			WHERE b.user_id = ?
			  AND b.status IN ("submitted","won","lost")
			ORDER BY b.cycle_id DESC, b.id DESC
			LIMIT '.$limit;

		return $this->db->query($sql, array($user_id))->result_array();
	}

	public function run_daily_winner($cycle_id, array $meta = array())
	{
		$cycle_id = (int) $cycle_id;
		$cycle_dt = $this->cycle_datetime_from_id($cycle_id);
		$lock_key = 'bids_daily_winner_'.$cycle_id;

		if (!$this->acquire_cycle_lock($lock_key)) {
			log_message('error', 'Winner selection lock failed for cycle_id='.$cycle_id);
			return array(
				'ok' => FALSE,
				'status' => 'lock_failed',
				'cycle_id' => $cycle_id
			);
		}

		$this->db->trans_begin();

		$existing_feature = $this->feature_model->get_by_cycle($cycle_id);
		if ($existing_feature) {
			$this->db->trans_rollback();
			$this->release_cycle_lock($lock_key);
			log_message('info', 'Winner selection skipped: already selected for cycle_id='.$cycle_id);
			return array(
				'ok' => TRUE,
				'status' => 'already_selected',
				'cycle_id' => $cycle_id,
				'feature_id' => (int) $existing_feature['id']
			);
		}

		$existing_winner = $this->db
			->where('cycle_id', $cycle_id)
			->where('status', 'won')
			->limit(1)
			->get($this->table)
			->row_array();
		if ($existing_winner) {
			$this->db->trans_rollback();
			$this->release_cycle_lock($lock_key);
			log_message('info', 'Winner selection skipped: won bid already exists for cycle_id='.$cycle_id);
			return array(
				'ok' => TRUE,
				'status' => 'already_selected',
				'cycle_id' => $cycle_id,
				'bid_id' => (int) $existing_winner['id']
			);
		}

		$candidates = $this->db
			->query(
				'SELECT * FROM `'.$this->table.'` WHERE `cycle_id` = ? AND `status` = "submitted" ORDER BY `amount` DESC, `submitted_at` ASC, `id` ASC FOR UPDATE',
				array($cycle_id)
			)
			->result_array();

		if (empty($candidates)) {
			$this->db->trans_rollback();
			$this->release_cycle_lock($lock_key);
			log_message('info', 'Winner selection found no submitted bids for cycle_id='.$cycle_id);
			return array(
				'ok' => TRUE,
				'status' => 'no_bids',
				'cycle_id' => $cycle_id
			);
		}

		$winner = NULL;
		$winner_profile_id = 0;
		$winner_eligibility = NULL;

		foreach ($candidates as $candidate) {
			$profile_id = $this->profile_id_for_user((int) $candidate['user_id']);
			if ($profile_id <= 0) {
				log_message('error', 'Winner candidate skipped (no profile): bid_id='.(int) $candidate['id']);
				continue;
			}

			$eligibility = $this->feature_model->monthly_eligibility_for_profile($profile_id, $cycle_dt);
			if (!$eligibility['can_win_more']) {
				log_message(
					'info',
					'Winner candidate skipped (monthly cap): bid_id='.(int) $candidate['id'].
					' user_id='.(int) $candidate['user_id'].
					' wins='.(int) $eligibility['wins_this_month'].
					' max='.(int) $eligibility['max_slots']
				);
				continue;
			}

			$winner = $candidate;
			$winner_profile_id = $profile_id;
			$winner_eligibility = $eligibility;
			break;
		}

		if (!$winner) {
			$this->db->trans_rollback();
			$this->release_cycle_lock($lock_key);
			log_message('info', 'Winner selection found no eligible candidates for cycle_id='.$cycle_id);
			return array(
				'ok' => TRUE,
				'status' => 'no_eligible_bids',
				'cycle_id' => $cycle_id
			);
		}

		$winner_id = (int) $winner['id'];
		$winner_user_id = (int) $winner['user_id'];

		$mark_winner_ok = $this->db
			->where('id', $winner_id)
			->update($this->table, array(
				'status' => 'won'
			));

		$mark_others_ok = $this->db
			->where('cycle_id', $cycle_id)
			->where('status', 'submitted')
			->where('id !=', $winner_id)
			->update($this->table, array(
				'status' => 'lost'
			));

		$feature_id = $this->feature_model->create_featured_for_winner($winner_profile_id, $cycle_id, $winner_id, $cycle_dt);
		if (!$feature_id || !$mark_winner_ok || $mark_others_ok === FALSE || $this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$this->release_cycle_lock($lock_key);
			log_message('error', 'Winner selection failed during DB update for cycle_id='.$cycle_id);
			return array(
				'ok' => FALSE,
				'status' => 'db_error',
				'cycle_id' => $cycle_id
			);
		}

		$this->db->trans_commit();
		$this->release_cycle_lock($lock_key);

		log_message(
			'info',
			'Winner selected: cycle_id='.$cycle_id.
			' winner_bid_id='.$winner_id.
			' winner_user_id='.$winner_user_id.
			' feature_id='.(int) $feature_id
		);

		return array(
			'ok' => TRUE,
			'status' => 'winner_selected',
			'cycle_id' => $cycle_id,
			'winner_bid_id' => $winner_id,
			'winner_user_id' => $winner_user_id,
			'feature_id' => (int) $feature_id,
			'remaining_slots_after_win' => isset($winner_eligibility['remaining_slots']) ? max(0, (int) $winner_eligibility['remaining_slots'] - 1) : NULL,
			'meta' => $meta
		);
	}

	private function profile_id_for_user($user_id)
	{
		$row = $this->db
			->select('id')
			->where('user_id', (int) $user_id)
			->limit(1)
			->get('profiles')
			->row_array();

		return $row ? (int) $row['id'] : 0;
	}

	private function cycle_datetime_from_id($cycle_id)
	{
		$cycle_id = (int) $cycle_id;
		$cycle_str = (string) $cycle_id;
		if (preg_match('/^[0-9]{8}$/', $cycle_str)) {
			$dt = DateTime::createFromFormat('Ymd', $cycle_str);
			if ($dt) {
				$dt->setTime(12, 0, 0);
				return $dt;
			}
		}

		return new DateTime();
	}

	private function acquire_cycle_lock($lock_key)
	{
		$row = $this->db->query('SELECT GET_LOCK(?, 5) AS lck', array((string) $lock_key))->row_array();
		return isset($row['lck']) && (int) $row['lck'] === 1;
	}

	private function release_cycle_lock($lock_key)
	{
		$this->db->query('SELECT RELEASE_LOCK(?)', array((string) $lock_key));
	}
}

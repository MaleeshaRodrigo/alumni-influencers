<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Featured alumni placements (featured_alumni table).
 */
class Feature_model extends CI_Model {

	protected $table = 'featured_alumni';

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

	public function get_by_cycle($cycle_id)
	{
		return $this->db
			->where('cycle_id', (int) $cycle_id)
			->limit(1)
			->get($this->table)
			->row_array();
	}

	public function has_feature_for_cycle($cycle_id)
	{
		return (bool) $this->db
			->select('id')
			->where('cycle_id', (int) $cycle_id)
			->limit(1)
			->get($this->table)
			->row_array();
	}

	public function list_active(?DateTime $at = NULL)
	{
		if ($at === NULL) {
			$at = new DateTime();
		}

		$now = $at->format('Y-m-d H:i:s');
		return $this->db
			->where('is_active', 1)
			->where('featured_from <=', $now)
			->where('featured_until >=', $now)
			->order_by('sort_order', 'ASC')
			->order_by('id', 'ASC')
			->get($this->table)
			->result_array();
	}

	public function list_for_profile($profile_id)
	{
		return $this->db
			->where('profile_id', (int) $profile_id)
			->order_by('featured_from', 'DESC')
			->get($this->table)
			->result_array();
	}

	public function create(array $data)
	{
		$ok = $this->db->insert($this->table, $data);
		if (!$ok) {
			return FALSE;
		}

		return (int) $this->db->insert_id();
	}

	public function update($id, array $data)
	{
		return $this->db
			->where('id', (int) $id)
			->update($this->table, $data);
	}

	public function deactivate($id)
	{
		return $this->db
			->where('id', (int) $id)
			->update($this->table, array('is_active' => 0));
	}

	public function link_winning_bid($feature_id, $bid_id)
	{
		return $this->db
			->where('id', (int) $feature_id)
			->update($this->table, array(
				'winning_bid_id' => (int) $bid_id
			));
	}

	public function count_monthly_wins_for_profile($profile_id, ?DateTime $at = NULL)
	{
		if ($at === NULL) {
			$at = new DateTime();
		}

		$start = (clone $at)->modify('first day of this month')->setTime(0, 0, 0);
		$end = (clone $at)->modify('last day of this month')->setTime(23, 59, 59);

		return (int) $this->db
			->where('profile_id', (int) $profile_id)
			->where('winning_bid_id IS NOT NULL', NULL, FALSE)
			->where('featured_from >=', $start->format('Y-m-d H:i:s'))
			->where('featured_from <=', $end->format('Y-m-d H:i:s'))
			->count_all_results($this->table);
	}

	public function has_event_bonus_for_profile($profile_id, ?DateTime $at = NULL)
	{
		if ($at === NULL) {
			$at = new DateTime();
		}

		$start = (clone $at)->modify('first day of this month')->setTime(0, 0, 0);
		$end = (clone $at)->modify('last day of this month')->setTime(23, 59, 59);
		$now = $at->format('Y-m-d H:i:s');

		$row = $this->db
			->select('id')
			->where('profile_id', (int) $profile_id)
			->where('awarded_at >=', $start->format('Y-m-d H:i:s'))
			->where('awarded_at <=', $end->format('Y-m-d H:i:s'))
			->group_start()
				->where('expires_at IS NULL', NULL, FALSE)
				->or_where('expires_at >=', $now)
			->group_end()
			->order_by('awarded_at', 'DESC')
			->limit(1)
			->get('alumni_event_bonus')
			->row_array();

		return !empty($row);
	}

	public function monthly_eligibility_for_profile($profile_id, ?DateTime $at = NULL)
	{
		if ($at === NULL) {
			$at = new DateTime();
		}

		$wins = $this->count_monthly_wins_for_profile((int) $profile_id, $at);
		$has_bonus = $this->has_event_bonus_for_profile((int) $profile_id, $at);
		$max_slots = 3 + ($has_bonus ? 1 : 0);
		$remaining = max(0, $max_slots - $wins);

		return array(
			'month' => $at->format('Y-m'),
			'wins_this_month' => $wins,
			'has_event_bonus' => $has_bonus,
			'max_slots' => $max_slots,
			'remaining_slots' => $remaining,
			'can_win_more' => $remaining > 0
		);
	}

	public function create_featured_for_winner($profile_id, $cycle_id, $winning_bid_id, ?DateTime $at = NULL)
	{
		if ($at === NULL) {
			$at = new DateTime();
		}

		$from = $at->format('Y-m-d H:i:s');
		$until = (clone $at)->modify('+1 day')->format('Y-m-d H:i:s');

		return $this->create(array(
			'profile_id' => (int) $profile_id,
			'cycle_id' => (int) $cycle_id,
			'winning_bid_id' => (int) $winning_bid_id,
			'featured_from' => $from,
			'featured_until' => $until,
			'sort_order' => 0,
			'is_active' => 1
		));
	}
}

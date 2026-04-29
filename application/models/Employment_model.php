<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Employment history (employment_history table).
 */
class Employment_model extends CI_Model {

	protected $table = 'employment_history';

	public function __construct()
	{
		parent::__construct();
		$this->load->database();
	}

	public function list_by_profile_id($profile_id)
	{
		return $this->db
			->where('profile_id', (int) $profile_id)
			->order_by('sort_order', 'ASC')
			->order_by('id', 'DESC')
			->get($this->table)
			->result_array();
	}

	public function get_by_id($id)
	{
		return $this->db
			->where('id', (int) $id)
			->limit(1)
			->get($this->table)
			->row_array();
	}

	public function get_by_id_and_profile($id, $profile_id)
	{
		return $this->db
			->where('id', (int) $id)
			->where('profile_id', (int) $profile_id)
			->limit(1)
			->get($this->table)
			->row_array();
	}

	public function create(array $data)
	{
		$inserted = $this->db->insert($this->table, $data);
		if (!$inserted) {
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

	public function delete($id)
	{
		return $this->db
			->where('id', (int) $id)
			->delete($this->table);
	}

	public function reorder_for_profile($profile_id, array $ordered_ids)
	{
		$profile_id = (int) $profile_id;
		$order = 0;
		foreach ($ordered_ids as $id) {
			$this->db
				->where('id', (int) $id)
				->where('profile_id', $profile_id)
				->update($this->table, array('sort_order' => $order));
			$order++;
		}

		return TRUE;
	}
}

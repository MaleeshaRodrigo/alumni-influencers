<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * One profile row per user (public-facing CV shell).
 */
class Profile_model extends CI_Model {

	protected $table = 'profiles';

	public function __construct()
	{
		parent::__construct();
		$this->load->database();
	}

	public function get_by_id($profile_id)
	{
		return $this->db
			->where('id', (int) $profile_id)
			->limit(1)
			->get($this->table)
			->row_array();
	}

	public function get_by_user_id($user_id)
	{
		return $this->db
			->where('user_id', (int) $user_id)
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

	public function update($profile_id, array $data)
	{
		return $this->db
			->where('id', (int) $profile_id)
			->update($this->table, $data);
	}

	public function delete($profile_id)
	{
		return $this->db
			->where('id', (int) $profile_id)
			->delete($this->table);
	}

	public function set_visibility($profile_id, $is_public)
	{
		return $this->db
			->where('id', (int) $profile_id)
			->update($this->table, array(
				'is_public' => (int) ((bool) $is_public)
			));
	}

	public function save_basic_by_user_id($user_id, array $data)
	{
		$user_id = (int) $user_id;
		$current = $this->get_by_user_id($user_id);

		if ($current) {
			$updated = $this->update((int) $current['id'], $data);
			if (!$updated) {
				return FALSE;
			}

			return (int) $current['id'];
		}

		$data['user_id'] = $user_id;
		$created_id = $this->create($data);
		if (!$created_id) {
			return FALSE;
		}

		return $created_id;
	}
}

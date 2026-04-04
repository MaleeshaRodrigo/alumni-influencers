<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * User accounts: credentials, verification, lockout (see users table).
 */
class User_model extends CI_Model {

	protected $table = 'users';

	public function __construct()
	{
		parent::__construct();
		$this->load->database();
	}

	// --- Registration & identity ---

	public function create(array $data)
	{
		if (isset($data['email'])) {
			$data['email'] = strtolower(trim((string) $data['email']));
		}

		$inserted = $this->db->insert($this->table, $data);
		if (!$inserted) {
			return FALSE;
		}

		return (int) $this->db->insert_id();
	}

	public function find_by_id($id)
	{
		return $this->db
			->where('id', (int) $id)
			->limit(1)
			->get($this->table)
			->row_array();
	}

	public function find_by_email($email)
	{
		return $this->db
			->where('email', strtolower(trim((string) $email)))
			->limit(1)
			->get($this->table)
			->row_array();
	}

	public function email_exists($email)
	{
		return (bool) $this->db
			->select('id')
			->where('email', strtolower(trim((string) $email)))
			->limit(1)
			->get($this->table)
			->row_array();
	}

	// --- Email verification tokens ---

	public function set_email_verification_token($user_id, $token_hash, DateTime $expires_at)
	{
		return $this->db
			->where('id', (int) $user_id)
			->update($this->table, array(
				'email_verify_token_hash' => (string) $token_hash,
				'email_verify_token_expires_at' => $expires_at->format('Y-m-d H:i:s')
			));
	}

	public function find_active_email_verify_token($token_hash)
	{
		return $this->db
			->where('email_verify_token_hash', (string) $token_hash)
			->where('email_verify_token_expires_at >=', date('Y-m-d H:i:s'))
			->where('email_verified_at IS NULL', NULL, FALSE)
			->where('status !=', 'deleted')
			->limit(1)
			->get($this->table)
			->row_array();
	}

	public function clear_email_verification_token($user_id)
	{
		return $this->db
			->where('id', (int) $user_id)
			->update($this->table, array(
				'email_verify_token_hash' => NULL,
				'email_verify_token_expires_at' => NULL
			));
	}

	public function mark_email_verified($user_id, ?DateTime $verified_at = NULL)
	{
		if ($verified_at === NULL) {
			$verified_at = new DateTime();
		}

		return $this->db
			->where('id', (int) $user_id)
			->update($this->table, array(
				'email_verified_at' => $verified_at->format('Y-m-d H:i:s'),
				'status' => 'active',
				'email_verify_token_hash' => NULL,
				'email_verify_token_expires_at' => NULL
			));
	}

	// --- Password reset tokens ---

	public function set_password_reset_token($user_id, $token_hash, DateTime $expires_at)
	{
		return $this->db
			->where('id', (int) $user_id)
			->update($this->table, array(
				'password_reset_token_hash' => (string) $token_hash,
				'password_reset_expires_at' => $expires_at->format('Y-m-d H:i:s')
			));
	}

	public function find_active_password_reset_token($token_hash)
	{
		return $this->db
			->where('password_reset_token_hash', (string) $token_hash)
			->where('password_reset_expires_at >=', date('Y-m-d H:i:s'))
			->where('status !=', 'deleted')
			->limit(1)
			->get($this->table)
			->row_array();
	}

	public function clear_password_reset_token($user_id)
	{
		return $this->db
			->where('id', (int) $user_id)
			->update($this->table, array(
				'password_reset_token_hash' => NULL,
				'password_reset_expires_at' => NULL
			));
	}

	public function update_password_hash($user_id, $password_hash)
	{
		return $this->db
			->where('id', (int) $user_id)
			->update($this->table, array(
				'password_hash' => (string) $password_hash
			));
	}

	// --- Login security ---

	public function record_failed_login($user_id)
	{
		$this->db->set('failed_login_count', 'failed_login_count + 1', FALSE);
		return $this->db
			->where('id', (int) $user_id)
			->update($this->table);
	}

	public function reset_failed_login($user_id)
	{
		return $this->db
			->where('id', (int) $user_id)
			->update($this->table, array('failed_login_count' => 0));
	}

	public function set_locked_until($user_id, DateTime $locked_until)
	{
		return $this->db
			->where('id', (int) $user_id)
			->update($this->table, array('locked_until' => $locked_until->format('Y-m-d H:i:s')));
	}

	public function clear_lock($user_id)
	{
		return $this->db
			->where('id', (int) $user_id)
			->update($this->table, array(
				'locked_until' => NULL,
				'failed_login_count' => 0
			));
	}

	public function touch_last_login($user_id, ?DateTime $at = NULL)
	{
		if ($at === NULL) {
			$at = new DateTime();
		}

		return $this->db
			->where('id', (int) $user_id)
			->update($this->table, array(
				'last_login_at' => $at->format('Y-m-d H:i:s')
			));
	}

	// --- Account state ---

	public function update_status($user_id, $status)
	{
		return $this->db
			->where('id', (int) $user_id)
			->update($this->table, array('status' => (string) $status));
	}

	public function find_login_candidate_by_email($email)
	{
		return $this->db
			->where('email', strtolower(trim((string) $email)))
			->where('status', 'active')
			->where('email_verified_at IS NOT NULL', NULL, FALSE)
			->group_start()
				->where('locked_until IS NULL', NULL, FALSE)
				->or_where('locked_until <', date('Y-m-d H:i:s'))
			->group_end()
			->limit(1)
			->get($this->table)
			->row_array();
	}
}

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
	}

	public function find_by_id($id)
	{
	}

	public function find_by_email($email)
	{
	}

	public function email_exists($email)
	{
	}

	// --- Email verification tokens ---

	public function set_email_verification_token($user_id, $token_hash, DateTime $expires_at)
	{
	}

	public function find_active_email_verify_token($token_hash)
	{
	}

	public function clear_email_verification_token($user_id)
	{
	}

	public function mark_email_verified($user_id, ?DateTime $verified_at = NULL)
	{
	}

	// --- Password reset tokens ---

	public function set_password_reset_token($user_id, $token_hash, DateTime $expires_at)
	{
	}

	public function find_active_password_reset_token($token_hash)
	{
	}

	public function clear_password_reset_token($user_id)
	{
	}

	public function update_password_hash($user_id, $password_hash)
	{
	}

	// --- Login security ---

	public function record_failed_login($user_id)
	{
	}

	public function reset_failed_login($user_id)
	{
	}

	public function set_locked_until($user_id, DateTime $locked_until)
	{
	}

	public function clear_lock($user_id)
	{
	}

	public function touch_last_login($user_id, ?DateTime $at = NULL)
	{
	}

	// --- Account state ---

	public function update_status($user_id, $status)
	{
	}
}

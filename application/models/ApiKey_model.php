<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * API keys: hashed storage, revocation, expiry (api_keys table).
 */
class ApiKey_model extends CI_Model {

	protected $table = 'api_keys';

	public function __construct()
	{
		parent::__construct();
		$this->load->database();
	}

	public function create_for_user($user_id, array $data)
	{
	}

	public function list_for_user($user_id, $include_revoked = FALSE)
	{
	}

	public function get_by_id($id)
	{
	}

	public function find_valid_by_hash($key_hash)
	{
	}

	public function revoke($id, $reason = NULL)
	{
	}

	public function touch_last_used($id, ?DateTime $at = NULL)
	{
	}

	public function is_expired_row(array $row)
	{
	}
}

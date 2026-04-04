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
		$payload = array(
			'user_id' => (int) $user_id,
			'name' => isset($data['name']) ? trim((string) $data['name']) : 'API Key',
			'key_prefix' => isset($data['key_prefix']) ? trim((string) $data['key_prefix']) : '',
			'key_hash' => isset($data['key_hash']) ? strtolower(trim((string) $data['key_hash'])) : '',
			'scopes' => isset($data['scopes']) ? trim((string) $data['scopes']) : '',
			'expires_at' => isset($data['expires_at']) && $data['expires_at'] !== '' ? (string) $data['expires_at'] : NULL
		);

		if ($payload['user_id'] <= 0 || $payload['key_prefix'] === '' || $payload['key_hash'] === '') {
			return FALSE;
		}

		$ok = $this->db->insert($this->table, $payload);
		if (!$ok) {
			return FALSE;
		}

		return (int) $this->db->insert_id();
	}

	public function list_for_user($user_id, $include_revoked = FALSE)
	{
		$this->db
			->where('user_id', (int) $user_id)
			->order_by('id', 'DESC');

		if (!$include_revoked) {
			$this->db->where('is_revoked', 0);
		}

		return $this->db->get($this->table)->result_array();
	}

	public function get_by_id($id)
	{
		return $this->db
			->where('id', (int) $id)
			->limit(1)
			->get($this->table)
			->row_array();
	}

	public function find_valid_by_hash($key_hash)
	{
		$row = $this->db
			->where('key_hash', strtolower(trim((string) $key_hash)))
			->where('is_revoked', 0)
			->limit(1)
			->get($this->table)
			->row_array();

		if (!$row) {
			return NULL;
		}

		if ($this->is_expired_row($row)) {
			return NULL;
		}

		return $row;
	}

	public function revoke($id, $reason = NULL)
	{
		return $this->db
			->where('id', (int) $id)
			->update($this->table, array(
				'is_revoked' => 1,
				'revoked_at' => date('Y-m-d H:i:s'),
				'revoked_reason' => $reason !== NULL ? trim((string) $reason) : NULL
			));
	}

	public function touch_last_used($id, ?DateTime $at = NULL)
	{
		if ($at === NULL) {
			$at = new DateTime();
		}

		return $this->db
			->where('id', (int) $id)
			->update($this->table, array(
				'last_used_at' => $at->format('Y-m-d H:i:s')
			));
	}

	public function is_expired_row(array $row)
	{
		if (!isset($row['expires_at']) || $row['expires_at'] === NULL || $row['expires_at'] === '') {
			return FALSE;
		}

		return strtotime((string) $row['expires_at']) <= time();
	}

	public function list_all($include_revoked = TRUE, $limit = 200)
	{
		$limit = max(1, (int) $limit);
		$this->db
			->select('api_keys.*, users.email AS owner_email')
			->from($this->table)
			->join('users', 'users.id = api_keys.user_id', 'left')
			->order_by('api_keys.id', 'DESC')
			->limit($limit);

		if (!$include_revoked) {
			$this->db->where('api_keys.is_revoked', 0);
		}

		return $this->db->get()->result_array();
	}
}

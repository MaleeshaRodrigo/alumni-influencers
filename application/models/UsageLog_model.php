<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * API request audit log (api_usage_logs table).
 */
class UsageLog_model extends CI_Model {

	protected $table = 'api_usage_logs';

	public function __construct()
	{
		parent::__construct();
		$this->load->database();
	}

	public function log(array $data)
	{
		$payload = array(
			'api_key_id' => isset($data['api_key_id']) && $data['api_key_id'] !== '' ? (int) $data['api_key_id'] : NULL,
			'route' => isset($data['route']) ? (string) $data['route'] : '',
			'http_method' => isset($data['http_method']) ? strtoupper((string) $data['http_method']) : 'GET',
			'ip_address' => isset($data['ip_address']) ? (string) $data['ip_address'] : '',
			'user_agent' => isset($data['user_agent']) && $data['user_agent'] !== '' ? (string) $data['user_agent'] : NULL,
			'response_code' => isset($data['response_code']) ? (int) $data['response_code'] : 0,
			'duration_ms' => isset($data['duration_ms']) && $data['duration_ms'] !== '' ? (int) $data['duration_ms'] : NULL
		);

		$ok = $this->db->insert($this->table, $payload);
		if (!$ok) {
			return FALSE;
		}

		return (int) $this->db->insert_id();
	}

	public function list_by_api_key($api_key_id, $limit = 100, $offset = 0)
	{
		$limit = max(1, (int) $limit);
		$offset = max(0, (int) $offset);

		return $this->db
			->where('api_key_id', (int) $api_key_id)
			->order_by('id', 'DESC')
			->limit($limit, $offset)
			->get($this->table)
			->result_array();
	}

	public function count_by_api_key($api_key_id, ?DateTime $since = NULL)
	{
		$this->db->where('api_key_id', (int) $api_key_id);
		if ($since !== NULL) {
			$this->db->where('created_at >=', $since->format('Y-m-d H:i:s'));
		}

		return (int) $this->db->count_all_results($this->table);
	}

	public function delete_older_than(DateTime $cutoff)
	{
		return $this->db
			->where('created_at <', $cutoff->format('Y-m-d H:i:s'))
			->delete($this->table);
	}

	public function list_recent($limit = 200)
	{
		$limit = max(1, (int) $limit);
		return $this->db
			->select('api_usage_logs.*, api_keys.name AS api_key_name, api_keys.key_prefix')
			->from($this->table)
			->join('api_keys', 'api_keys.id = api_usage_logs.api_key_id', 'left')
			->order_by('api_usage_logs.id', 'DESC')
			->limit($limit)
			->get()
			->result_array();
	}
}

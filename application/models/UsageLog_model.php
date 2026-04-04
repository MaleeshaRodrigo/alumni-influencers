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
	}

	public function list_by_api_key($api_key_id, $limit = 100, $offset = 0)
	{
	}

	public function count_by_api_key($api_key_id, ?DateTime $since = NULL)
	{
	}

	public function delete_older_than(DateTime $cutoff)
	{
	}
}

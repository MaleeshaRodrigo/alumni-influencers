<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Reusable bearer token validator + usage logger for API endpoints.
 */
class BearerTokenAuth
{
	/** @var CI_Controller */
	protected $CI;

	public function __construct()
	{
		$this->CI =& get_instance();
		$this->CI->load->model('ApiKey_model', 'api_key_model');
		$this->CI->load->model('UsageLog_model', 'usage_log_model');
	}

	public function validate_request(array $required_scopes = array())
	{
		$token = $this->extract_bearer_token();
		if ($token === NULL || $token === '') {
			log_message('error', 'Bearer auth failed: missing token route='.(string) $this->CI->uri->uri_string().' ip='.(string) $this->CI->input->ip_address());
			return array(
				'ok' => FALSE,
				'code' => 401,
				'error' => 'Missing bearer token.',
				'api_key' => NULL
			);
		}

		$key_hash = hash('sha256', $token);
		$api_key = $this->CI->api_key_model->find_valid_by_hash($key_hash);
		if (!$api_key) {
			log_message('error', 'Bearer auth failed: invalid token route='.(string) $this->CI->uri->uri_string().' ip='.(string) $this->CI->input->ip_address());
			return array(
				'ok' => FALSE,
				'code' => 401,
				'error' => 'Invalid or expired bearer token.',
				'api_key' => NULL
			);
		}

		$scopes = $this->parse_scopes(isset($api_key['scopes']) ? (string) $api_key['scopes'] : '');
		foreach ($required_scopes as $required_scope) {
			if (!in_array((string) $required_scope, $scopes, TRUE)) {
				log_message(
					'error',
					'Bearer auth failed: missing scope route='.(string) $this->CI->uri->uri_string().
					' api_key_id='.(int) $api_key['id'].
					' required_scope='.(string) $required_scope
				);
				return array(
					'ok' => FALSE,
					'code' => 403,
					'error' => 'Bearer token does not have required scope.',
					'api_key' => $api_key
				);
			}
		}

		$this->CI->api_key_model->touch_last_used((int) $api_key['id']);
		log_message('info', 'Bearer auth success: route='.(string) $this->CI->uri->uri_string().' api_key_id='.(int) $api_key['id']);
		return array(
			'ok' => TRUE,
			'code' => 200,
			'error' => NULL,
			'api_key' => $api_key
		);
	}

	public function log_usage($api_key_id, $response_code, $started_at = NULL)
	{
		$duration_ms = NULL;
		if (is_numeric($started_at)) {
			$duration_ms = (int) round((microtime(TRUE) - (float) $started_at) * 1000);
		}

		return $this->CI->usage_log_model->log(array(
			'api_key_id' => $api_key_id !== NULL ? (int) $api_key_id : NULL,
			'route' => (string) $this->CI->uri->uri_string(),
			'http_method' => (string) $this->CI->input->method(TRUE),
			'ip_address' => (string) $this->CI->input->ip_address(),
			'user_agent' => (string) $this->CI->input->user_agent(),
			'response_code' => (int) $response_code,
			'duration_ms' => $duration_ms
		));
	}

	public function deny_and_log($response_code, $message, $api_key_id = NULL, $started_at = NULL)
	{
		$this->log_usage($api_key_id, (int) $response_code, $started_at);
		return array(
			'ok' => FALSE,
			'code' => (int) $response_code,
			'error' => (string) $message
		);
	}

	protected function extract_bearer_token()
	{
		$header = $this->CI->input->get_request_header('Authorization', TRUE);
		if ($header === NULL || $header === '') {
			return NULL;
		}

		if (preg_match('/^\s*Bearer\s+(.+)\s*$/i', $header, $matches)) {
			return trim($matches[1]);
		}

		return NULL;
	}

	protected function parse_scopes($scope_string)
	{
		$parts = array_filter(array_map('trim', explode(',', (string) $scope_string)));
		return array_values(array_unique($parts));
	}
}

<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Public developer JSON API endpoints.
 */
class Publicapi extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Feature_model', 'feature_model');
		$this->load->model('UsageLog_model', 'usage_log_model');
		$this->load->library('RateLimiter');
		$this->config->load('security_hardening', TRUE);
	}

	public function featured_today()
	{
		$started_at = microtime(TRUE);
		$ip = (string) $this->input->ip_address();
		if ($this->is_rate_limited('public_api_featured_today', 'public_api_featured_today:'.$ip)) {
			log_message('error', 'Public API rate limit hit for ip='.$ip.' endpoint=featured_today');
			$this->log_usage(429, $started_at);
			return $this->json_response(array(
				'ok' => FALSE,
				'message' => 'Too many requests. Please try again later.'
			), 429);
		}

		$http_method = strtoupper($this->input->method(TRUE));
		if ($http_method !== 'GET') {
			$payload = array(
				'ok' => FALSE,
				'message' => 'Method not allowed. Use GET.'
			);
			log_message('error', 'Public API method blocked: method='.$http_method.' ip='.$ip);
			$this->ratelimiter->hit('public_api_featured_today:'.$ip, $this->rate_limit_window('public_api_featured_today'));
			$this->log_usage(405, $started_at);
			return $this->json_response($payload, 405);
		}

		$featured = $this->feature_model->public_featured_today();
		if (!$featured) {
			$payload = array(
				'ok' => FALSE,
				'message' => 'No featured alumnus for today.',
				'date' => date('Y-m-d'),
				'data' => NULL
			);

			log_message('info', 'Public API featured_today: no featured row for date='.date('Y-m-d'));
			$this->ratelimiter->hit('public_api_featured_today:'.$ip, $this->rate_limit_window('public_api_featured_today'));
			$this->log_usage(404, $started_at);
			return $this->json_response($payload, 404);
		}

		$data = array(
			'cycle_id' => (int) $featured['cycle_id'],
			'featured_from' => (string) $featured['featured_from'],
			'featured_until' => (string) $featured['featured_until'],
			'alumnus' => array(
				'profile_id' => (int) $featured['profile_id'],
				'display_name' => (string) $featured['display_name'],
				'bio' => isset($featured['bio']) ? (string) $featured['bio'] : '',
				'photo_url' => !empty($featured['photo_path']) ? base_url($featured['photo_path']) : NULL,
				'linkedin_url' => !empty($featured['linkedin_url']) ? (string) $featured['linkedin_url'] : NULL
			)
		);

		log_message(
			'info',
			'Public API featured_today served: cycle_id='.(int) $featured['cycle_id'].' profile_id='.(int) $featured['profile_id']
		);
		$this->ratelimiter->hit('public_api_featured_today:'.$ip, $this->rate_limit_window('public_api_featured_today'));
		$this->log_usage(200, $started_at);

		return $this->json_response(array(
			'ok' => TRUE,
			'message' => 'Featured alumnus found.',
			'date' => date('Y-m-d'),
			'data' => $data
		), 200);
	}

	private function json_response(array $payload, $status_code)
	{
		return $this->output
			->set_content_type('application/json', 'utf-8')
			->set_status_header((int) $status_code)
			->set_output(json_encode($payload, JSON_UNESCAPED_SLASHES));
	}

	private function log_usage($status_code, $started_at)
	{
		$duration_ms = (int) round((microtime(TRUE) - (float) $started_at) * 1000);
		$this->usage_log_model->log(array(
			'api_key_id' => NULL,
			'route' => (string) $this->uri->uri_string(),
			'http_method' => (string) $this->input->method(TRUE),
			'ip_address' => (string) $this->input->ip_address(),
			'user_agent' => (string) $this->input->user_agent(),
			'response_code' => (int) $status_code,
			'duration_ms' => $duration_ms
		));
	}

	private function rate_limit_window($name)
	{
		$limits = $this->config->item('rate_limits', 'security_hardening');
		if (!is_array($limits) || !isset($limits[$name]['window_seconds'])) {
			return 60;
		}

		return max(1, (int) $limits[$name]['window_seconds']);
	}

	private function is_rate_limited($name, $key)
	{
		$limits = $this->config->item('rate_limits', 'security_hardening');
		if (!is_array($limits) || !isset($limits[$name])) {
			return FALSE;
		}

		$limit = isset($limits[$name]['limit']) ? (int) $limits[$name]['limit'] : 0;
		$window = isset($limits[$name]['window_seconds']) ? (int) $limits[$name]['window_seconds'] : 60;
		if ($limit <= 0) {
			return FALSE;
		}

		return $this->ratelimiter->is_limited($key, $limit, $window);
	}
}

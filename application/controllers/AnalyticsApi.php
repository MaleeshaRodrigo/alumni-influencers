<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class AnalyticsApi extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('Analytics_model', 'analytics_model');
		$this->load->library('BearerTokenAuth', NULL, 'bearer_auth');
		$this->load->library('RateLimiter', NULL, 'ratelimiter');
		$this->load->library('session');
		$this->config->load('security_hardening', TRUE);
	}

	public function alumni_distribution()
	{
		$context = $this->begin_request(array('GET'), array('read:analytics'), 'analytics_api');
		if (!$context['ok']) return $context['response'];

		$by_degree = $this->analytics_model->get_alumni_distribution_by_degree();
		$by_year = $this->analytics_model->get_alumni_distribution_by_graduation_year();
		$by_industry = $this->analytics_model->get_industry_distribution();

		return $this->respond($context, array(
			'ok' => TRUE,
			'data' => array(
				'by_degree' => $by_degree,
				'by_year' => $by_year,
				'by_industry' => $by_industry
			)
		), 200);
	}

	public function skills_gap()
	{
		$context = $this->begin_request(array('GET'), array('read:analytics'), 'analytics_api');
		if (!$context['ok']) return $context['response'];

		$data = $this->analytics_model->get_skills_gap_data();

		return $this->respond($context, array(
			'ok' => TRUE,
			'data' => $data
		), 200);
	}

	public function career_pathways()
	{
		$context = $this->begin_request(array('GET'), array('read:analytics'), 'analytics_api');
		if (!$context['ok']) return $context['response'];

		$data = $this->analytics_model->get_career_pathways();

		return $this->respond($context, array(
			'ok' => TRUE,
			'data' => $data
		), 200);
	}

	public function trends()
	{
		$context = $this->begin_request(array('GET'), array('read:analytics'), 'analytics_api');
		if (!$context['ok']) return $context['response'];

		$cert_trends = $this->analytics_model->get_certification_trends();
		$top_courses = $this->analytics_model->get_top_short_courses();

		return $this->respond($context, array(
			'ok' => TRUE,
			'data' => array(
				'certification_trends' => $cert_trends,
				'top_short_courses' => $top_courses
			)
		), 200);
	}

	public function alumni_list()
	{
		$context = $this->begin_request(array('GET'), array('read:alumni'), 'analytics_api');
		if (!$context['ok']) return $context['response'];

		$filters = array(
			'programme' => $this->input->get('programme', TRUE),
			'graduation_year' => $this->input->get('graduation_year', TRUE),
			'industry' => $this->input->get('industry', TRUE)
		);

		$data = $this->analytics_model->get_alumni_list($filters);

		return $this->respond($context, array(
			'ok' => TRUE,
			'data' => $data
		), 200);
	}

	public function usage_stats()
	{
		$context = $this->begin_request(array('GET'), array('read:analytics'), 'analytics_api');
		if (!$context['ok']) return $context['response'];

		$this->load->model('UsageLog_model', 'usage_log_model');
		$this->load->model('ApiKey_model', 'api_key_model');

		$stats = array(
			'recent_logs' => $this->usage_log_model->list_recent(50),
			'api_keys' => $this->api_key_model->list_all(TRUE, 10)
		);

		return $this->respond($context, array(
			'ok' => TRUE,
			'data' => $stats
		), 200);
	}

	private function begin_request(array $allowed_methods, array $required_scopes, $rate_limit_name)
	{
		$started_at = microtime(TRUE);
		$method = strtoupper((string) $this->input->method(TRUE));
		$ip = (string) $this->input->ip_address();
		$rate_window = $this->rate_limit_window($rate_limit_name);
		$rate_key = $rate_limit_name.':'.$ip;

		$base_context = array(
			'ok' => FALSE,
			'started_at' => $started_at,
			'rate_key' => $rate_key,
			'rate_window' => $rate_window,
			'api_key_id' => NULL
		);

		if ($this->is_rate_limited($rate_limit_name, $rate_key)) {
			return array(
				'ok' => FALSE,
				'response' => $this->json_response(array('ok' => FALSE, 'message' => 'Too many requests.'), 429)
			);
		}

		if (!in_array($method, $allowed_methods, TRUE)) {
			return array(
				'ok' => FALSE,
				'response' => $this->json_response(array('ok' => FALSE, 'message' => 'Method not allowed.'), 405)
			);
		}

		// Allow session-based authentication for internal dashboard requests
		if ($this->session->userdata('is_authenticated')) {
			$base_context['ok'] = TRUE;
			return $base_context;
		}

		$auth = $this->bearer_auth->validate_request($required_scopes);
		if (!$auth['ok']) {
			return array(
				'ok' => FALSE,
				'response' => $this->json_response(array('ok' => FALSE, 'message' => $auth['error']), $auth['code'])
			);
		}

		$base_context['api_key_id'] = $auth['api_key']['id'];
		$base_context['ok'] = TRUE;
		return $base_context;
	}

	private function respond(array $context, array $payload, $status_code)
	{
		$this->bearer_auth->log_usage($context['api_key_id'], $status_code, $context['started_at']);
		$this->ratelimiter->hit($context['rate_key'], $context['rate_window']);
		return $this->json_response($payload, $status_code);
	}

	private function json_response(array $payload, $status_code)
	{
		return $this->output
			->set_content_type('application/json', 'utf-8')
			->set_status_header((int) $status_code)
			->set_output(json_encode($payload, JSON_UNESCAPED_SLASHES));
	}

	private function rate_limit_window($name)
	{
		$limits = $this->config->item('rate_limits', 'security_hardening');
		return (isset($limits[$name]['window_seconds'])) ? (int) $limits[$name]['window_seconds'] : 60;
	}

	private function is_rate_limited($name, $key)
	{
		$limits = $this->config->item('rate_limits', 'security_hardening');
		if (!isset($limits[$name])) return FALSE;
		return $this->ratelimiter->is_limited($key, $limits[$name]['limit'], $limits[$name]['window_seconds']);
	}
}

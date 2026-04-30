<?php

class AnalyticsApiTest extends TestCase
{
	private function newController()
	{
		$controller = $this->newController('AnalyticsApi');
		$controller->analytics_model = new FakeService();
		$controller->bearer_auth = new FakeService();
		$controller->ratelimiter = new FakeService();
		$controller->config->setGroup('security_hardening', array(
			'rate_limits' => array(
				'analytics_api' => array('limit' => 60, 'window_seconds' => 60)
			)
		));
		$controller->bearer_auth->setReturn('validate_request', array('ok' => TRUE, 'api_key' => array('id' => 12)));
		$controller->ratelimiter->setReturn('is_limited', FALSE);
		return $controller;
	}

	public function testAlumniDistributionReturnsAllSlices()
	{
		$controller = $this->newController();
		$controller->input->methodValue = 'GET';
		$controller->analytics_model->setReturn('get_alumni_distribution_by_degree', array(array('programme' => 'CS')));
		$controller->analytics_model->setReturn('get_alumni_distribution_by_graduation_year', array(array('year' => 2024)));
		$controller->analytics_model->setReturn('get_industry_distribution', array(array('sector' => 'Tech')));

		$controller->alumni_distribution();

		$payload = $this->jsonDecodeOutput($this->ci->output);
		$this->assertSame(200, $this->ci->output->statusCode);
		$this->assertSame('CS', $payload['data']['by_degree'][0]['programme']);
		$this->assertSame(1, $controller->bearer_auth->callCount('log_usage'));
		$this->assertSame(1, $controller->ratelimiter->callCount('hit'));
	}

	public function testUsageStatsReturnsRecentLogsAndKeys()
	{
		$controller = $this->newController();
		$controller->input->methodValue = 'GET';
		$controller->usage_log_model = new FakeService();
		$controller->api_key_model = new FakeService();
		$controller->usage_log_model->setReturn('list_recent', array(array('id' => 1)));
		$controller->api_key_model->setReturn('list_all', array(array('id' => 2)));

		$controller->usage_stats();

		$payload = $this->jsonDecodeOutput($this->ci->output);
		$this->assertSame(1, $payload['data']['recent_logs'][0]['id']);
		$this->assertSame(2, $payload['data']['api_keys'][0]['id']);
	}
}
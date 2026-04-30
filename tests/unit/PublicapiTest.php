<?php

class PublicapiTest extends TestCase
{
	private function makeController()
	{
		$controller = $this->newController('Publicapi');
		$controller->feature_model = new FakeService();
		$controller->usage_log_model = new FakeService();
		$controller->ratelimiter = new FakeService();
		$controller->config->setItem('base_url', 'https://example.test/');
		$controller->config->setGroup('security_hardening', array(
			'rate_limits' => array(
				'public_api_featured_today' => array('limit' => 120, 'window_seconds' => 60)
			)
		));
		$controller->ratelimiter->setReturn('is_limited', FALSE);
		return $controller;
	}

	public function testFeaturedTodayReturnsFeaturedAlumnus()
	{
		$controller = $this->makeController();
		$controller->input->methodValue = 'GET';
		$controller->uri->uriString = 'api/featured-today';
		$controller->feature_model->setReturn('public_featured_today', array(
			'cycle_id' => 20260501,
			'featured_from' => '2026-05-01 00:00:00',
			'featured_until' => '2026-05-02 00:00:00',
			'profile_id' => 9,
			'display_name' => 'Student One',
			'bio' => 'Bio',
			'photo_path' => 'uploads/photo.jpg',
			'linkedin_url' => 'https://linkedin.example/profile'
		));

		$controller->featured_today();

		$payload = $this->jsonDecodeOutput($this->ci->output);
		$this->assertSame(200, $this->ci->output->statusCode);
		$this->assertSame('Student One', $payload['data']['alumnus']['display_name']);
		$this->assertSame('https://example.test/uploads/photo.jpg', $payload['data']['alumnus']['photo_url']);
	}

	public function testFeaturedTodayReturns404WhenNoFeaturedRowExists()
	{
		$controller = $this->makeController();
		$controller->input->methodValue = 'GET';
		$controller->uri->uriString = 'api/featured-today';
		$controller->feature_model->setReturn('public_featured_today', FALSE);

		$controller->featured_today();

		$payload = $this->jsonDecodeOutput($this->ci->output);
		$this->assertSame(404, $this->ci->output->statusCode);
		$this->assertFalse($payload['ok']);
	}
}
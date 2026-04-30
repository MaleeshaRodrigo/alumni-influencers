<?php

class AdminControllerTest extends TestCase
{
	private function newControllerWithAdminUser()
	{
		$controller = $this->newController('Admin');
		$controller->form_validation = new FakeFormValidation();
		$controller->api_key_model = new FakeService();
		$controller->usage_log_model = new FakeService();
		$controller->session->userdata['auth_user_id'] = 1;
		$controller->session->userdata['is_authenticated'] = TRUE;
		$controller->session->userdata['auth_role'] = 'admin';
		$this->ci->db->queueGet(array(array(
			'id' => 1,
			'email' => 'admin@example.com',
			'role' => 'admin',
			'status' => 'active',
			'email_verified_at' => '2026-05-01 12:00:00'
		)));
		return $controller;
	}

	public function testApiKeysReturnsKeyListForAdmin()
	{
		$controller = $this->newControllerWithAdminUser();
		$controller->api_key_model->setReturn('list_all', array(array('id' => 1, 'name' => 'Key')));

		$controller->api_keys();

		$payload = $this->jsonDecodeOutput($this->ci->output);
		$this->assertTrue($payload['ok']);
		$this->assertSame(200, $this->ci->output->statusCode);
		$this->assertSame('Key', $payload['data']['keys'][0]['name']);
	}

	public function testCreateApiKeyReturnsTokenAndPrefix()
	{
		$controller = $this->newControllerWithAdminUser();
		$controller->form_validation->setReturn('run', TRUE);
		$controller->api_key_model->setReturn('create_for_user', 55);
		$controller->input->methodValue = 'POST';
		$controller->input->postData = array(
			'name' => 'Reporting Key',
			'scopes' => 'read:analytics',
			'expires_at' => '2026-05-01 12:00:00'
		);

		$controller->create_api_key();

		$payload = $this->jsonDecodeOutput($this->ci->output);
		$this->assertSame(201, $this->ci->output->statusCode);
		$this->assertSame(55, $payload['data']['id']);
		$this->assertSame(64, strlen($payload['data']['token']));
		$this->assertSame(12, strlen($payload['data']['prefix']));
	}

	public function testRevokeApiKeyReturnsSuccess()
	{
		$controller = $this->newControllerWithAdminUser();
		$controller->input->methodValue = 'POST';
		$controller->input->postData = array('reason' => 'cleanup');
		$controller->api_key_model->setReturn('get_by_id', array('id' => 9, 'is_revoked' => 0));
		$controller->api_key_model->setReturn('revoke', TRUE);

		$controller->revoke_api_key(9);

		$payload = $this->jsonDecodeOutput($this->ci->output);
		$this->assertTrue($payload['ok']);
		$this->assertSame(200, $this->ci->output->statusCode);
	}

	public function testUsageLogsReturnsScopedResults()
	{
		$controller = $this->newControllerWithAdminUser();
		$controller->input->getData = array('api_key_id' => 4);
		$controller->usage_log_model->setReturn('list_by_api_key', array(array('id' => 1)));
		$controller->usage_log_model->setReturn('count_by_api_key', 1);

		$controller->usage_logs();

		$payload = $this->jsonDecodeOutput($this->ci->output);
		$this->assertSame(4, $payload['data']['filter_api_key_id']);
		$this->assertSame(1, $payload['data']['total_for_key']);
	}
}
<?php

class BearerTokenAuthTest extends TestCase
{
	private function newAuth()
	{
		$this->ci->config->setItem('base_url', 'https://example.test/');
		$this->ci->uri->uriString = 'api/test';
		return new BearerTokenAuth();
	}

	public function testValidateRequestRejectsMissingToken()
	{
		$auth = $this->newAuth();

		$result = $auth->validate_request(array('read:analytics'));

		$this->assertFalse($result['ok']);
		$this->assertSame(401, $result['code']);
	}

	public function testValidateRequestRejectsMissingScope()
	{
		$auth = $this->newAuth();
		$this->ci->input->headers['Authorization'] = 'Bearer token123';
		$this->ci->db->queueGet(array(array(
			'id' => 8,
			'key_hash' => hash('sha256', 'token123'),
			'is_revoked' => 0,
			'scopes' => 'read:alumni'
		)));

		$result = $auth->validate_request(array('read:analytics'));

		$this->assertFalse($result['ok']);
		$this->assertSame(403, $result['code']);
	}

	public function testValidateRequestTouchesApiKeyOnSuccess()
	{
		$auth = $this->newAuth();
		$this->ci->input->headers['Authorization'] = 'Bearer token123';
		$this->ci->db->queueGet(array(array(
			'id' => 8,
			'key_hash' => hash('sha256', 'token123'),
			'is_revoked' => 0,
			'scopes' => 'read:analytics,read:alumni',
			'expires_at' => date('Y-m-d H:i:s', time() + 3600)
		)));

		$result = $auth->validate_request(array('read:analytics'));

		$this->assertTrue($result['ok']);
		$this->assertSame(1, $this->ci->db->updateCalls ? count($this->ci->db->updateCalls) : 0);
	}
}
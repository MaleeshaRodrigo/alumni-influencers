<?php

class AuthValidationTest extends TestCase
{
	private function newAuth()
	{
		$auth = $this->makeWithoutConstructor('Auth');
		$this->setProperty($auth, 'form_validation', new FakeFormValidation());
		$this->setProperty($auth, 'user_model', new FakeService());
		$this->setProperty($auth, 'config', $this->ci->config);
		return $auth;
	}

	public function testAllowedEmailDomainPasses()
	{
		$this->ci->config->setGroup('auth', array(
			'allowed_email_domains' => array('eastminster.ac.uk', 'iit.ac.lk')
		));
		$auth = $this->newAuth();

		$this->assertTrue($auth->_email_domain_allowed('student@iit.ac.lk'));
	}

	public function testDisallowedEmailDomainFails()
	{
		$this->ci->config->setGroup('auth', array(
			'allowed_email_domains' => array('eastminster.ac.uk', 'iit.ac.lk')
		));
		$auth = $this->newAuth();

		$this->assertFalse($auth->_email_domain_allowed('user@example.com'));
	}

	public function testEmailAvailabilityUsesUserModel()
	{
		$userModel = new FakeService();
		$userModel->setReturn('email_exists', FALSE);
		$auth = $this->makeWithoutConstructor('Auth');
		$this->setProperty($auth, 'form_validation', new FakeFormValidation());
		$this->setProperty($auth, 'user_model', $userModel);
		$this->setProperty($auth, 'config', $this->ci->config);

		$this->assertTrue($auth->_email_available('new@example.com'));
		$userModel->setReturn('email_exists', TRUE);
		$this->assertFalse($auth->_email_available('new@example.com'));
	}

	public function testStrongPasswordRuleEnforcesComplexity()
	{
		$this->ci->config->setGroup('auth', array(
			'password_min_length' => 12
		));
		$auth = $this->newAuth();

		$this->assertTrue($auth->_strong_password('Aa1!goodpass'));
		$this->assertFalse($auth->_strong_password('weakpass'));
	}
}
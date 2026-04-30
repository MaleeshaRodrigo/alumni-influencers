<?php

class UserModelTest extends TestCase
{
	private function newModel()
	{
		return new User_model();
	}

	public function testCreateNormalizesEmailBeforeInsert()
	{
		$model = $this->newModel();
		$this->ci->db->insertId = 42;

		$userId = $model->create(array(
			'email' => 'Test@Example.Com',
			'full_name' => 'Test User',
			'password_hash' => 'hash'
		));

		$this->assertSame(42, $userId);
		$this->assertSame('test@example.com', $this->ci->db->insertCalls[0][1]['email']);
	}

	public function testFindByEmailLowercasesLookup()
	{
		$model = $this->newModel();
		$this->ci->db->queueGet(array(array('id' => 1, 'email' => 'test@example.com')));

		$row = $model->find_by_email('TEST@example.com');

		$this->assertSame('test@example.com', $this->ci->db->whereCalls[0][2]);
		$this->assertSame(1, $row['id']);
	}

	public function testEmailExistsReturnsBoolean()
	{
		$model = $this->newModel();
		$this->ci->db->queueGet(array(array('id' => 1)));
		$this->assertTrue($model->email_exists('exists@example.com'));

		$this->ci->db->queueGet(array());
		$this->assertFalse($model->email_exists('missing@example.com'));
	}

	public function testVerificationTokenAndVerifiedStateUpdatesUseCorrectPayloads()
	{
		$model = $this->newModel();
		$expiresAt = new DateTime('2026-05-01 12:00:00');

		$this->assertTrue($model->set_email_verification_token(7, 'abc123', $expiresAt));
		$this->assertSame('abc123', $this->ci->db->updateCalls[0][1]['email_verify_token_hash']);

		$this->assertTrue($model->mark_email_verified(7, $expiresAt));
		$this->assertSame('active', $this->ci->db->updateCalls[1][1]['status']);
		$this->assertSame($expiresAt->format('Y-m-d H:i:s'), $this->ci->db->updateCalls[1][1]['email_verified_at']);
	}
}
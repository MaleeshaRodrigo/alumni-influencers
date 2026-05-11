<?php

class ApiKeyModelTest extends TestCase
{
	private function newModel()
	{
		return new ApiKey_model();
	}

	public function testCreateForUserRejectsInvalidPayload()
	{
		$model = $this->newModel();

		$this->assertFalse($model->create_for_user(0, array('key_prefix' => 'abc', 'key_hash' => 'hash')));
		$this->assertFalse($model->create_for_user(1, array('key_hash' => 'hash')));
	}

	public function testCreateForUserNormalizesAndInserts()
	{
		$model = $this->newModel();
		$this->ci->db->insertId = 99;

		$keyId = $model->create_for_user(5, array(
			'name' => ' Demo Key ',
			'key_prefix' => ' ABC123 ',
			'key_hash' => 'ABCDEF',
			'scopes' => 'read:analytics',
			'expires_at' => '2026-05-01 12:00:00'
		));

		$this->assertSame(99, $keyId);
		$this->assertSame('demo key', strtolower(trim($this->ci->db->insertCalls[0][1]['name'])));
		$this->assertSame('ABC123', trim($this->ci->db->insertCalls[0][1]['key_prefix']));
		$this->assertSame('abcdef', $this->ci->db->insertCalls[0][1]['key_hash']);
	}

	public function testFindValidByHashRejectsExpiredKeys()
	{
		$model = $this->newModel();
		$this->ci->db->queueGet(array(array(
			'id' => 10,
			'key_hash' => 'abc',
			'is_revoked' => 0,
			'expires_at' => date('Y-m-d H:i:s', time() - 60)
		)));

		$this->assertNull($model->find_valid_by_hash('abc'));
	}

	public function testRevokeAndTouchLastUsedUpdateExpectedFields()
	{
		$model = $this->newModel();
		$at = new DateTime('2026-05-01 12:00:00');

		$this->assertTrue($model->revoke(3, 'manual'));
		$this->assertSame(1, $this->ci->db->updateCalls[0][1]['is_revoked']);

		$this->assertTrue($model->touch_last_used(3, $at));
		$this->assertSame($at->format('Y-m-d H:i:s'), $this->ci->db->updateCalls[1][1]['last_used_at']);
	}
}
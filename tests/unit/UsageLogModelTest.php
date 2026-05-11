<?php

class UsageLogModelTest extends TestCase
{
	private function newModel()
	{
		return new UsageLog_model();
	}

	public function testLogNormalizesPayload()
	{
		$model = $this->newModel();
		$this->ci->db->insertId = 77;

		$logId = $model->log(array(
			'api_key_id' => '12',
			'route' => '/api/test',
			'http_method' => 'post',
			'ip_address' => '127.0.0.1',
			'user_agent' => 'Agent',
			'response_code' => '200',
			'duration_ms' => '15'
		));

		$this->assertSame(77, $logId);
		$this->assertSame('POST', $this->ci->db->insertCalls[0][1]['http_method']);
		$this->assertSame(200, $this->ci->db->insertCalls[0][1]['response_code']);
	}

	public function testListAndCountByApiKeyUseExpectedQueries()
	{
		$model = $this->newModel();
		$this->ci->db->queueGet(array(array('id' => 1), array('id' => 2)));
		$logs = $model->list_by_api_key(5, 2, 1);
		$this->assertCount(2, $logs);

		$this->ci->db->queueCount(9);
		$this->assertSame(9, $model->count_by_api_key(5, new DateTime('2026-05-01 00:00:00')));
	}

	public function testDeleteOlderThanUsesCutoffDate()
	{
		$model = $this->newModel();
		$cutoff = new DateTime('2026-04-01 12:00:00');

		$this->assertTrue($model->delete_older_than($cutoff));
		$this->assertSame($cutoff->format('Y-m-d H:i:s'), $this->ci->db->whereCalls[0][2]);
	}
}
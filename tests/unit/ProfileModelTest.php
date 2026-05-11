<?php

class ProfileModelTest extends TestCase
{
	private function newModel()
	{
		return new Profile_model();
	}

	public function testSaveBasicByUserIdCreatesProfileWhenMissing()
	{
		$model = $this->newModel();
		$this->ci->db->queueGet(array());
		$this->ci->db->insertId = 31;

		$profileId = $model->save_basic_by_user_id(7, array(
			'display_name' => 'New Alumni',
			'bio' => 'Bio'
		));

		$this->assertSame(31, $profileId);
		$this->assertSame(7, $this->ci->db->insertCalls[0][1]['user_id']);
		$this->assertSame('New Alumni', $this->ci->db->insertCalls[0][1]['display_name']);
	}

	public function testSaveBasicByUserIdUpdatesExistingProfile()
	{
		$model = $this->newModel();
		$this->ci->db->queueGet(array(array('id' => 14, 'user_id' => 7, 'display_name' => 'Old Name')));

		$profileId = $model->save_basic_by_user_id(7, array(
			'display_name' => 'Updated Name'
		));

		$this->assertSame(14, $profileId);
		$this->assertSame('Updated Name', $this->ci->db->updateCalls[0][1]['display_name']);
	}
}
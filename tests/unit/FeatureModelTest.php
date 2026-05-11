<?php

class FeatureModelTest extends TestCase
{
	private function newModel()
	{
		return new Feature_model();
	}

	public function testMonthlyEligibilityUsesCurrentMonthWindow()
	{
		$model = $this->newModel();
		$this->ci->db->queueCount(2);
		$this->ci->db->queueGet(array(array('id' => 1)));

		$eligibility = $model->monthly_eligibility_for_profile(9, new DateTime('2026-05-15 12:00:00'));

		$this->assertSame(2, $eligibility['wins_this_month']);
		$this->assertSame(4, $eligibility['max_slots']);
		$this->assertTrue($eligibility['can_win_more']);
	}

	public function testCreateFeaturedForWinnerSetsDateWindow()
	{
		$model = $this->newModel();
		$this->ci->db->insertId = 14;
		$at = new DateTime('2026-05-01 10:00:00');

		$this->assertSame(14, $model->create_featured_for_winner(3, 20260501, 8, $at));
		$this->assertSame(3, $this->ci->db->insertCalls[0][1]['profile_id']);
		$this->assertSame('2026-05-01 10:00:00', $this->ci->db->insertCalls[0][1]['featured_from']);
	}

	public function testPublicFeaturedTodayReturnsJoinedRow()
	{
		$model = $this->newModel();
		$this->ci->db->queueGet(array(array(
			'cycle_id' => 20260501,
			'featured_from' => '2026-05-01 00:00:00',
			'featured_until' => '2026-05-02 00:00:00',
			'profile_id' => 3,
			'display_name' => 'Student One',
			'bio' => 'Bio',
			'photo_path' => NULL,
			'linkedin_url' => NULL
		)));

		$row = $model->public_featured_today(new DateTime('2026-05-01 12:00:00'));
		$this->assertSame('Student One', $row['display_name']);
	}
}
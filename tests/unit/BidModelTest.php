<?php

class BidModelTest extends TestCase
{
	private function newModel(FakeService $featureModel = NULL)
	{
		$model = new Bid_model();
		$model->feature_model = $featureModel ?: new FakeService();
		return $model;
	}

	public function testMonthlyEligibilityDelegatesToFeatureModel()
	{
		$featureModel = new FakeService();
		$featureModel->setReturn('monthly_eligibility_for_profile', array(
			'month' => '2026-05',
			'wins_this_month' => 1,
			'has_event_bonus' => FALSE,
			'max_slots' => 3,
			'remaining_slots' => 2,
			'can_win_more' => TRUE
		));

		$model = $this->newModel($featureModel);
		$this->ci->db->queueGet(array(array('id' => 11)));

		$eligibility = $model->monthly_eligibility_for_user(7, new DateTime('2026-05-15 12:00:00'));

		$this->assertSame(2, $eligibility['remaining_slots']);
		$this->assertSame(1, $featureModel->callCount('monthly_eligibility_for_profile'));
	}

	public function testPlaceOrIncreasePlacesNewBidWhenEligible()
	{
		$featureModel = new FakeService();
		$featureModel->setReturn('monthly_eligibility_for_profile', array(
			'can_win_more' => TRUE,
			'remaining_slots' => 3,
			'wins_this_month' => 0,
			'has_event_bonus' => FALSE,
			'max_slots' => 3,
			'month' => '2026-05'
		));

		$model = $this->newModel($featureModel);
		$this->ci->db->queueGet(array(array('id' => 11)));
		$this->ci->db->queueQuery(array());
		$this->ci->db->insertId = 88;

		$result = $model->place_or_increase_for_cycle(7, 20260501, 25.50, 'usd');

		$this->assertTrue($result['ok']);
		$this->assertSame('placed', $result['action']);
		$this->assertSame('USD', $this->ci->db->insertCalls[0][1]['currency']);
		$this->assertSame(25.5, $this->ci->db->insertCalls[0][1]['amount']);
	}

	public function testPlaceOrIncreaseRejectsNonIncreasingBid()
	{
		$featureModel = new FakeService();
		$featureModel->setReturn('monthly_eligibility_for_profile', array(
			'can_win_more' => TRUE,
			'remaining_slots' => 3,
			'wins_this_month' => 0,
			'has_event_bonus' => FALSE,
			'max_slots' => 3,
			'month' => '2026-05'
		));

		$model = $this->newModel($featureModel);
		$this->ci->db->queueGet(array(array('id' => 11)));
		$this->ci->db->queueQuery(array(array('id' => 33, 'amount' => 50)));

		$result = $model->place_or_increase_for_cycle(7, 20260501, 40, 'GBP');

		$this->assertFalse($result['ok']);
		$this->assertSame('You can only increase your existing bid.', $result['error']);
		$this->assertSame(array(array('begin', FALSE), array('rollback')), $this->ci->db->transactions);
	}

	public function testRunDailyWinnerSelectsFirstEligibleCandidate()
	{
		$featureModel = new FakeService();
		$featureModel->setReturn('get_by_cycle', NULL);
		$featureModel->setReturn('monthly_eligibility_for_profile', array(
			'can_win_more' => TRUE,
			'remaining_slots' => 3,
			'wins_this_month' => 0,
			'has_event_bonus' => FALSE,
			'max_slots' => 3,
			'month' => '2026-05'
		));
		$featureModel->setReturn('create_featured_for_winner', 123);

		$model = $this->newModel($featureModel);
		$this->ci->db->queueGet(array());
		$this->ci->db->queueQuery(array(array('id' => 21, 'user_id' => 7, 'amount' => 100, 'submitted_at' => '2026-05-01 10:00:00')));
		$this->ci->db->queueGet(array(array('id' => 14)));
		$this->ci->db->insertReturn = TRUE;
		$this->ci->db->updateReturn = TRUE;

		$result = $model->run_daily_winner(20260501, array('source' => 'test'));

		$this->assertTrue($result['ok']);
		$this->assertSame('winner_selected', $result['status']);
		$this->assertSame(21, $result['winner_bid_id']);
		$this->assertSame(123, $result['feature_id']);
		$this->assertSame('won', $this->ci->db->updateCalls[0][1]['status']);
		$this->assertSame('lost', $this->ci->db->updateCalls[1][1]['status']);
	}
}
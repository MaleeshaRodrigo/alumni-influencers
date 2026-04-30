<?php

class AnalyticsModelTest extends TestCase
{
	private function newModel()
	{
		return new Analytics_model();
	}

	public function testGetAlumniListAppliesFilters()
	{
		$model = $this->newModel();
		$this->ci->db->queueGet(array(array('id' => 1, 'display_name' => 'Student One')));

		$rows = $model->get_alumni_list(array(
			'programme' => 'Computer',
			'graduation_year' => '2024',
			'industry' => 'Engineering'
		));

		$this->assertCount(1, $rows);
		$likeCalls = array_values(array_filter($this->ci->db->whereCalls, function ($call) {
			return isset($call[0]) && $call[0] === 'like';
		}));
		$this->assertCount(2, $likeCalls);
	}

	public function testGetSkillsGapDataReturnsGroupedRows()
	{
		$model = $this->newModel();
		$this->ci->db->queueGet(array(array('skill' => 'Leadership', 'count' => 4)));

		$rows = $model->get_skills_gap_data();

		$this->assertSame('Leadership', $rows[0]['skill']);
		$this->assertSame('c.name as skill, count(*) as count', $this->ci->db->selectCalls[0][0]);
	}

	public function testCertificationTrendsAndDegreeDistributionReturnRows()
	{
		$model = $this->newModel();
		$this->ci->db->queueGet(array(array('programme' => 'CS', 'count' => 10)));
		$this->assertSame('CS', $model->get_alumni_distribution_by_degree()[0]['programme']);

		$this->ci->db->queueGet(array(array('year' => 2024, 'count' => 5)));
		$this->assertSame(2024, $model->get_alumni_distribution_by_graduation_year()[0]['year']);
	}
}
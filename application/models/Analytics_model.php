<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Analytics_model extends CI_Model {

	public function __construct()
	{
		parent::__construct();
		$this->load->database();
	}

	public function get_alumni_distribution_by_degree()
	{
		return $this->db
			->select('field_of_study as programme, count(*) as count')
			->group_by('field_of_study')
			->get('degrees')
			->result_array();
	}

	public function get_alumni_distribution_by_graduation_year()
	{
		return $this->db
			->select('YEAR(completed_on) as year, count(*) as count')
			->where('completed_on IS NOT NULL')
			->group_by('year')
			->order_by('year', 'DESC')
			->get('degrees')
			->result_array();
	}

	public function get_industry_distribution()
	{
		// Since we don't have a sector column, we use employer or job_title groups
		// For the sake of this coursework, I'll group by employer as a proxy for sector if not available
		return $this->db
			->select('employer as sector, count(*) as count')
			->group_by('employer')
			->order_by('count', 'DESC')
			->limit(10)
			->get('employment_history')
			->result_array();
	}

	public function get_skills_gap_data()
	{
		// Identify certifications acquired post-graduation
		// We join degrees and certifications on profile_id where cert.issued_on > degree.completed_on
		return $this->db
			->select('c.name as skill, count(*) as count')
			->from('certifications c')
			->join('degrees d', 'c.profile_id = d.profile_id')
			->where('c.issued_on > d.completed_on')
			->group_by('c.name')
			->order_by('count', 'DESC')
			->limit(10)
			->get()
			->result_array();
	}

	public function get_career_pathways()
	{
		return $this->db
			->select('d.field_of_study as degree, e.job_title, count(*) as count')
			->from('degrees d')
			->join('employment_history e', 'd.profile_id = e.profile_id')
			->where('e.is_current', 1)
			->group_by('d.field_of_study, e.job_title')
			->order_by('count', 'DESC')
			->limit(20)
			->get()
			->result_array();
	}

	public function get_certification_trends()
	{
		return $this->db
			->select('YEAR(issued_on) as year, name as certification, count(*) as count')
			->where('issued_on IS NOT NULL')
			->group_by('year, name')
			->order_by('year', 'ASC')
			->get('certifications')
			->result_array();
	}

	public function get_top_short_courses()
	{
		return $this->db
			->select('title, count(*) as count')
			->group_by('title')
			->order_by('count', 'DESC')
			->limit(10)
			->get('short_courses')
			->result_array();
	}

	public function get_alumni_list($filters = array())
	{
		$this->db->select('p.id, p.display_name, d.field_of_study as programme, d.completed_on as graduation_date, e.employer as industry');
		$this->db->from('profiles p');
		$this->db->join('degrees d', 'p.id = d.profile_id', 'left');
		$this->db->join('employment_history e', 'p.id = e.profile_id AND e.is_current = 1', 'left');

		if (!empty($filters['programme'])) {
			$this->db->like('d.field_of_study', $filters['programme']);
		}
		if (!empty($filters['graduation_year'])) {
			$this->db->where('YEAR(d.completed_on)', $filters['graduation_year']);
		}
		if (!empty($filters['industry'])) {
			$this->db->like('e.employer', $filters['industry']);
		}

		return $this->db->get()->result_array();
	}
}

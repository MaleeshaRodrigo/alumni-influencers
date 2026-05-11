<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->helper(array('form', 'url'));
		$this->load->library('session');
		$this->load->library('form_validation');
		$this->load->model(array('Profile_model', 'Degree_model', 'Certification_model', 'Employment_model', 'Licence_model'));
	}

	public function index()
	{
		$this->load->view('dashboard/main');
	}

	public function graphs()
	{
		$this->load->view('dashboard/graphs');
	}

	public function alumni()
	{
		$this->load->view('dashboard/alumni');
	}

	public function security()
	{
		$this->load->view('dashboard/security');
	}

	public function login()
	{
		$this->load->view('dashboard/login');
	}

	public function register()
	{
		$this->load->view('dashboard/register');
	}

	public function profile($profile_id = NULL)
	{
		if (!$profile_id) {
			show_404();
		}

		// Get profile data
		$profile = $this->Profile_model->get_by_id($profile_id);
		if (!$profile) {
			show_404();
		}

		// Get related data
		$data['profile'] = $profile;
		$data['degrees'] = $this->db->where('profile_id', $profile_id)->get('degrees')->result_array();
		$data['certifications'] = $this->db->where('profile_id', $profile_id)->get('certifications')->result_array();
		$data['employment'] = $this->db->where('profile_id', $profile_id)->get('employment_history')->result_array();
		$data['licences'] = $this->db->where('profile_id', $profile_id)->get('licences')->result_array();

		$this->load->view('dashboard/profile', $data);
	}
}

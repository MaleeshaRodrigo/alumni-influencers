<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @property CI_Output $output
 * @property CI_Session $session
 */
class Home extends MY_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->library('session');
	}

	/**
	 * Smart landing page: shows dashboard if logged in, otherwise shows login/welcome
	 */
	public function index()
	{
		// Check if user is logged in via session
		if ($this->session->userdata('user_id')) {
			// User is logged in - redirect to dashboard
			redirect('dashboard', 'refresh');
		} else {
			// User is not logged in - show login page
			$this->load->view('dashboard/login');
		}
	}

	public function ping()
	{
		$this->output
			->set_content_type('application/json')
			->set_output(json_encode(array('status' => 'ok')));
	}
}

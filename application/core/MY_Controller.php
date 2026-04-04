<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Controller extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
	}

	protected function render($view, $data = array())
	{
		$layout_data = array(
			'page_title' => isset($data['page_title']) ? $data['page_title'] : 'Alumni Influencers'
		);

		$this->load->view('layouts/header', $layout_data);
		$this->load->view($view, $data);
		$this->load->view('layouts/footer');
	}

	/**
	 * Enforces authenticated + verified account.
	 * Returns the current user row when checks pass.
	 */
	protected function require_verified_user()
	{
		$user_id = (int) $this->session->userdata('auth_user_id');
		$is_authenticated = (bool) $this->session->userdata('is_authenticated');

		if (!$is_authenticated || $user_id <= 0) {
			$this->session->set_flashdata('auth_error', 'Please log in first.');
			redirect('auth/login');
			exit;
		}

		$this->load->model('User_model', 'user_model');
		$user = $this->user_model->find_by_id($user_id);

		if (!$user || (string) $user['status'] !== 'active' || empty($user['email_verified_at'])) {
			$this->session->unset_userdata(array('auth_user_id', 'auth_role', 'is_authenticated', 'logged_in_at'));
			$this->session->set_flashdata('auth_error', 'Please verify your account before accessing profiles.');
			redirect('auth/login');
			exit;
		}

		return $user;
	}
}

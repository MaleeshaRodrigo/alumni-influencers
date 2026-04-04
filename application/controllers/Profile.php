<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Basic alumni profile management.
 *
 * @property CI_Input $input
 * @property CI_Session $session
 * @property CI_Form_validation $form_validation
 * @property Profile_model $profile_model
 */
class Profile extends MY_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Profile_model', 'profile_model');
		$this->load->helper(array('form', 'url'));
		$this->load->library('form_validation');
	}

	public function dashboard()
	{
		$user = $this->require_verified_user();
		$profile = $this->profile_model->get_by_user_id((int) $user['id']);

		$data = array(
			'page_title' => 'Profile Dashboard',
			'user' => $user,
			'profile' => $profile
		);

		$this->render('profile/dashboard', $data);
	}

	public function basic()
	{
		$user = $this->require_verified_user();
		$profile = $this->profile_model->get_by_user_id((int) $user['id']);

		$data = array(
			'page_title' => 'Edit Basic Profile',
			'profile' => $profile
		);

		$this->render('profile/edit_basic', $data);
	}

	public function save_basic()
	{
		$user = $this->require_verified_user();
		if (strtoupper($this->input->method()) !== 'POST') {
			redirect('profile/basic');
			return;
		}

		$this->form_validation->set_rules('full_name', 'Full Name', 'trim|required|max_length[150]');
		$this->form_validation->set_rules('bio', 'Bio', 'trim|max_length[5000]');
		$this->form_validation->set_rules('linkedin_url', 'LinkedIn URL', 'trim|max_length[512]|callback__valid_linkedin_url');

		if ($this->form_validation->run() === FALSE) {
			$this->session->set_flashdata('profile_error', validation_errors('<p style="margin:4px 0;">', '</p>'));
			redirect('profile/basic');
			return;
		}

		$payload = array(
			'display_name' => trim((string) $this->input->post('full_name', TRUE)),
			'bio' => trim((string) $this->input->post('bio', TRUE)),
			'linkedin_url' => trim((string) $this->input->post('linkedin_url', TRUE))
		);

		if ($payload['bio'] === '') {
			$payload['bio'] = NULL;
		}
		if ($payload['linkedin_url'] === '') {
			$payload['linkedin_url'] = NULL;
		}

		$profile_id = $this->profile_model->save_basic_by_user_id((int) $user['id'], $payload);
		if (!$profile_id) {
			log_message('error', 'Profile basic save failed for user_id='.(int) $user['id']);
			$this->session->set_flashdata('profile_error', 'Could not save your profile right now.');
			redirect('profile/basic');
			return;
		}

		log_message('info', 'Profile basic saved: user_id='.(int) $user['id'].' profile_id='.(int) $profile_id);
		$this->session->set_flashdata('profile_success', 'Basic profile saved successfully.');
		redirect('profile/dashboard');
	}

	public function _valid_linkedin_url($url)
	{
		$url = trim((string) $url);
		if ($url === '') {
			return TRUE;
		}

		if (!filter_var($url, FILTER_VALIDATE_URL)) {
			$this->form_validation->set_message('_valid_linkedin_url', 'Please enter a valid LinkedIn URL.');
			return FALSE;
		}

		$parts = parse_url($url);
		$host = isset($parts['host']) ? strtolower((string) $parts['host']) : '';
		$path = isset($parts['path']) ? strtolower((string) $parts['path']) : '';

		if (preg_match('/(^|\\.)linkedin\\.com$/', $host) && strpos($path, '/in/') === 0) {
			return TRUE;
		}

		$this->form_validation->set_message('_valid_linkedin_url', 'LinkedIn URL must be from linkedin.com/in/...');
		return FALSE;
	}
}

<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class TestAnalytics extends CI_Controller {

	public function index()
	{
		$this->load->model('ApiKey_model', 'api_key_model');
		$this->load->model('User_model', 'user_model');

		// 1. Ensure a user exists
		$user = $this->user_model->find_by_email('admin@eastminster.ac.uk');
		if (!$user) {
			echo "Creating admin user...\n";
			$user_id = $this->user_model->create(array(
				'email' => 'admin@eastminster.ac.uk',
				'full_name' => 'Admin User',
				'password_hash' => password_hash('Admin@12345678', PASSWORD_BCRYPT),
				'role' => 'admin',
				'status' => 'active'
			));
			$this->user_model->mark_email_verified($user_id, new DateTime());
		} else {
			$user_id = $user['id'];
		}

		// 2. Create the scoped API key for Dashboard
		$key_plain = 'DASHBOARD_INTERNAL_KEY';
		$key_hash = hash('sha256', $key_plain);
		
		$existing_key = $this->api_key_model->find_valid_by_hash($key_hash);
		if (!$existing_key) {
			echo "Creating Dashboard API Key...\n";
			$this->api_key_model->create(array(
				'user_id' => $user_id,
				'name' => 'University Analytics Dashboard',
				'key_hash' => $key_hash,
				'scopes' => 'read:alumni,read:analytics',
				'status' => 'active'
			));
		}

		// 3. Create a scoped API key for AR App
		$ar_key_plain = 'AR_APP_MOBILE_KEY';
		$ar_key_hash = hash('sha256', $ar_key_plain);
		$existing_ar_key = $this->api_key_model->find_valid_by_hash($ar_key_hash);
		if (!$existing_ar_key) {
			echo "Creating AR App API Key...\n";
			$this->api_key_model->create(array(
				'user_id' => $user_id,
				'name' => 'Mobile AR App',
				'key_hash' => $ar_key_hash,
				'scopes' => 'read:alumni_of_day',
				'status' => 'active'
			));
		}

		echo "Setup complete.\n";
		echo "Dashboard Key: " . $key_plain . "\n";
		echo "AR App Key: " . $ar_key_plain . "\n";
	}
}

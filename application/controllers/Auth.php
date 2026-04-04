<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Registration and email verification only (Step 3 scope).
 *
 * @property CI_Input $input
 * @property CI_Session $session
 * @property CI_Config $config
 * @property CI_Form_validation $form_validation
 * @property CI_Email $email
 * @property User_model $user_model
 */
class Auth extends MY_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('User_model', 'user_model');
		$this->load->helper(array('form', 'url'));
		$this->load->library('form_validation');
		$this->config->load('auth', TRUE);
	}

	public function register()
	{
		$data = array(
			'page_title' => 'Register',
			'allowed_domains' => $this->allowed_email_domains()
		);

		$this->render('auth/register', $data);
	}

	public function do_register()
	{
		if (strtoupper($this->input->method()) !== 'POST') {
			redirect('register');
			return;
		}

		$this->form_validation->set_rules('email', 'University Email', 'trim|required|valid_email|max_length[255]|callback__email_domain_allowed|callback__email_available');
		$this->form_validation->set_rules('password', 'Password', 'required|callback__strong_password');
		$this->form_validation->set_rules('password_confirm', 'Confirm Password', 'required|matches[password]');

		if ($this->form_validation->run() === FALSE) {
			$this->session->set_flashdata('auth_error', validation_errors('<p style="margin:4px 0;">', '</p>'));
			redirect('register');
			return;
		}

		$email = strtolower(trim((string) $this->input->post('email', TRUE)));
		$password = (string) $this->input->post('password', FALSE);
		$password_hash = password_hash($password, PASSWORD_DEFAULT);

		if ($password_hash === FALSE) {
			log_message('error', 'Registration failed: password hashing error for '.$email);
			$this->session->set_flashdata('auth_error', 'Registration failed. Please try again.');
			redirect('register');
			return;
		}

		$user_id = $this->user_model->create(array(
			'email' => $email,
			'password_hash' => $password_hash,
			'status' => 'pending_verification',
			'role' => 'alumni'
		));

		if (!$user_id) {
			log_message('error', 'Registration failed: insert error for '.$email);
			$this->session->set_flashdata('auth_error', 'Unable to create your account right now.');
			redirect('register');
			return;
		}

		try {
			$raw_token = bin2hex(random_bytes(32));
		} catch (Exception $e) {
			log_message('error', 'Registration failed: token generation error for '.$email.' - '.$e->getMessage());
			$this->session->set_flashdata('auth_error', 'Could not generate verification token. Please try again.');
			redirect('register');
			return;
		}
		$token_hash = hash('sha256', $raw_token);
		$ttl_seconds = (int) $this->auth_config_item('verification_token_ttl_seconds', 86400);
		$expires_at = new DateTime('+'.$ttl_seconds.' seconds');

		$token_saved = $this->user_model->set_email_verification_token($user_id, $token_hash, $expires_at);
		if (!$token_saved) {
			log_message('error', 'Registration warning: failed to persist verify token for user_id='.$user_id);
			$this->session->set_flashdata('auth_error', 'Account created, but verification setup failed. Contact support.');
			redirect('register');
			return;
		}

		$verification_link = site_url('auth/verify-email/'.$raw_token);
		$email_sent = $this->send_verification_email($email, $verification_link, $expires_at);

		if (!$email_sent && ENVIRONMENT !== 'production') {
			$this->session->set_flashdata('dev_verify_link', $verification_link);
		}

		log_message('info', 'Registration success: user_id='.$user_id.' email='.$email.' email_sent='.(int) $email_sent);

		$this->session->set_flashdata('verify_email', $email);
		$this->session->set_flashdata('auth_success', 'Registration successful. Please verify your email before logging in.');
		redirect('auth/verify-notice');
	}

	public function verify_notice()
	{
		$data = array(
			'page_title' => 'Verify Email'
		);

		$this->render('auth/verify_notice', $data);
	}

	public function verify_email($token = NULL)
	{
		$token = is_string($token) ? trim($token) : '';

		if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
			log_message('error', 'Email verification failed: malformed token');
			$this->session->set_flashdata('auth_error', 'Invalid verification link.');
			redirect('register');
			return;
		}

		$token_hash = hash('sha256', $token);
		$user = $this->user_model->find_active_email_verify_token($token_hash);

		if (!$user) {
			log_message('error', 'Email verification failed: invalid or expired token');
			$this->session->set_flashdata('auth_error', 'Verification link is invalid or expired.');
			redirect('register');
			return;
		}

		$verified = $this->user_model->mark_email_verified((int) $user['id']);
		if (!$verified) {
			log_message('error', 'Email verification failed: DB update failed for user_id='.$user['id']);
			$this->session->set_flashdata('auth_error', 'Could not verify your account right now.');
			redirect('register');
			return;
		}

		log_message('info', 'Email verification success: user_id='.$user['id'].' email='.$user['email']);
		$this->session->set_flashdata('auth_success', 'Email verified successfully. You can now log in.');
		redirect('register');
	}

	public function _email_domain_allowed($email)
	{
		$email = strtolower(trim((string) $email));
		$parts = explode('@', $email);
		$domain = isset($parts[1]) ? strtolower($parts[1]) : '';
		$allowed = $this->allowed_email_domains();

		if (in_array($domain, $allowed, TRUE)) {
			return TRUE;
		}

		log_message('error', 'Registration blocked: disallowed domain='.$domain);
		$this->form_validation->set_message('_email_domain_allowed', 'Use an approved university email domain.');
		return FALSE;
	}

	public function _email_available($email)
	{
		$email = strtolower(trim((string) $email));
		if (!$this->user_model->email_exists($email)) {
			return TRUE;
		}

		log_message('error', 'Registration blocked: duplicate email='.$email);
		$this->form_validation->set_message('_email_available', 'An account with this email already exists.');
		return FALSE;
	}

	public function _strong_password($password)
	{
		$password = (string) $password;
		$min_length = (int) $this->auth_config_item('password_min_length', 12);

		$checks = array(
			strlen($password) >= $min_length,
			(bool) preg_match('/[A-Z]/', $password),
			(bool) preg_match('/[a-z]/', $password),
			(bool) preg_match('/[0-9]/', $password),
			(bool) preg_match('/[^A-Za-z0-9]/', $password),
			!preg_match('/\s/', $password)
		);

		if (!in_array(FALSE, $checks, TRUE)) {
			return TRUE;
		}

		$this->form_validation->set_message(
			'_strong_password',
			'Password must be at least '.$min_length.' characters and include upper, lower, number, and special character (no spaces).'
		);
		return FALSE;
	}

	private function send_verification_email($to_email, $verification_link, DateTime $expires_at)
	{
		$this->load->library('email');

		$from_email = getenv('MAIL_FROM') ? getenv('MAIL_FROM') : 'no-reply@alumni-influencers.local';
		$from_name = getenv('MAIL_FROM_NAME') ? getenv('MAIL_FROM_NAME') : 'Alumni Influencers';

		$this->email->clear(TRUE);
		$this->email->from($from_email, $from_name);
		$this->email->to($to_email);
		$this->email->subject('Verify your Alumni Influencers account');
		$this->email->message(
			"Welcome to Alumni Influencers.\n\n".
			"Verify your email by opening this link:\n".$verification_link."\n\n".
			"This link expires at ".$expires_at->format('Y-m-d H:i:s').".\n"
		);

		$sent = $this->email->send(FALSE);
		if ($sent) {
			return TRUE;
		}

		log_message('error', 'Email send failed for '.$to_email);
		if (ENVIRONMENT !== 'production') {
			log_message('info', 'DEV verification link for '.$to_email.': '.$verification_link);
		}

		return FALSE;
	}

	private function allowed_email_domains()
	{
		$domains = $this->auth_config_item('allowed_email_domains', array('my.sliit.lk', 'sliit.lk'));
		$clean = array();

		foreach ((array) $domains as $domain) {
			$domain = strtolower(trim((string) $domain));
			if ($domain !== '') {
				$clean[] = $domain;
			}
		}

		return array_values(array_unique($clean));
	}

	private function auth_config_item($key, $default = NULL)
	{
		$item = $this->config->item($key, 'auth');
		return $item !== NULL ? $item : $default;
	}
}

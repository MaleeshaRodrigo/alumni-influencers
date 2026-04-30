<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Authentication controller:
 * - Step 3: registration + email verification
 * - Step 4: login/logout + password reset
 *
 * @property CI_Input $input
 * @property CI_Session $session
 * @property CI_Config $config
 * @property CI_Form_validation $form_validation
 * @property CI_Email $email
 * @property User_model $user_model
 * @property Profile_model $profile_model
 */
class Auth extends MY_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('User_model', 'user_model');
		$this->load->model('Profile_model', 'profile_model');
		$this->load->helper(array('form', 'url'));
		$this->load->library('form_validation');
		$this->load->library('RateLimiter');
		$this->config->load('auth', TRUE);
		$this->config->load('security_hardening', TRUE);
	}

	public function do_register()
	{
		if (strtoupper($this->input->method()) !== 'POST') {
			return $this->json_response(array('ok' => FALSE, 'message' => 'Request processed.', 'data' => NULL), 400);
			return;
		}

		$ip = (string) $this->input->ip_address();
		if ($this->is_rate_limited('auth_register', 'auth_register:' . $ip)) {
			log_message('error', 'Registration rate limit hit for ip=' . $ip);
			$this->session->set_flashdata('auth_error', 'Too many registration attempts. Please try again later.');
			return $this->json_response(array('ok' => FALSE, 'message' => 'Request processed.', 'data' => NULL), 400);
			return;
		}

		$this->form_validation->set_rules('full_name', 'Full Name', 'trim|required|max_length[150]');
		$this->form_validation->set_rules('email', 'University Email', 'trim|required|valid_email|max_length[255]|callback__email_domain_allowed|callback__email_available');
		$this->form_validation->set_rules('password', 'Password', 'required|callback__strong_password');
		$this->form_validation->set_rules('password_confirm', 'Confirm Password', 'required|matches[password]');

		if ($this->form_validation->run() === FALSE) {
			$this->ratelimiter->hit('auth_register:' . $ip, $this->rate_limit_window('auth_register'));
			$this->session->set_flashdata('auth_error', validation_errors('<p style="margin:4px 0;">', '</p>'));
			return $this->json_response(array('ok' => FALSE, 'message' => 'Request processed.', 'data' => NULL), 400);
			return;
		}

		$full_name = trim((string) $this->input->post('full_name', TRUE));
		$email = strtolower(trim((string) $this->input->post('email', TRUE)));
		$password = (string) $this->input->post('password', FALSE);
		$password_hash = password_hash($password, PASSWORD_DEFAULT);

		if ($password_hash === FALSE) {
			log_message('error', 'Registration failed: password hashing error for ' . $email);
			$this->session->set_flashdata('auth_error', 'Registration failed. Please try again.');
			return $this->json_response(array('ok' => FALSE, 'message' => 'Request processed.', 'data' => NULL), 400);
			return;
		}

		$this->db->trans_begin();

		$user_id = $this->user_model->create(array(
			'email' => $email,
			'password_hash' => $password_hash,
			'status' => 'pending_verification',
			'role' => 'alumni'
		));

		if (!$user_id) {
			$this->db->trans_rollback();
			$this->ratelimiter->hit('auth_register:' . $ip, $this->rate_limit_window('auth_register'));
			log_message('error', 'Registration failed: insert error for ' . $email);
			$this->session->set_flashdata('auth_error', 'Unable to create your account right now.');
			return $this->json_response(array('ok' => FALSE, 'message' => 'Request processed.', 'data' => NULL), 400);
			return;
		}

		$profile_id = $this->profile_model->create(array(
			'user_id' => $user_id,
			'display_name' => $full_name
		));

		if (!$profile_id) {
			$this->db->trans_rollback();
			log_message('error', 'Registration failed: profile insert error for user_id=' . $user_id);
			$this->session->set_flashdata('auth_error', 'Unable to create your profile right now.');
			return $this->json_response(array('ok' => FALSE, 'message' => 'Request processed.', 'data' => NULL), 400);
			return;
		}

		try {
			$raw_token = bin2hex(random_bytes(32));
		} catch (Exception $e) {
			$this->db->trans_rollback();
			log_message('error', 'Registration failed: token generation error for ' . $email . ' - ' . $e->getMessage());
			$this->session->set_flashdata('auth_error', 'Could not generate verification token. Please try again.');
			return $this->json_response(array('ok' => FALSE, 'message' => 'Request processed.', 'data' => NULL), 400);
			return;
		}

		$token_hash = hash('sha256', $raw_token);
		$ttl_seconds = (int) $this->auth_config_item('verification_token_ttl_seconds', 86400);
		$expires_at = new DateTime('+' . $ttl_seconds . ' seconds');

		$token_saved = $this->user_model->set_email_verification_token($user_id, $token_hash, $expires_at);
		if (!$token_saved) {
			$this->db->trans_rollback();
			log_message('error', 'Registration warning: failed to persist verify token for user_id=' . $user_id);
			$this->session->set_flashdata('auth_error', 'Account created, but verification setup failed. Contact support.');
			return $this->json_response(array('ok' => FALSE, 'message' => 'Request processed.', 'data' => NULL), 400);
			return;
		}

		$this->db->trans_commit();

		$verification_link = site_url('auth/verify_email/' . $raw_token);
		$email_sent = $this->send_verification_email($email, $verification_link, $expires_at);

		if (!$email_sent && ENVIRONMENT !== 'production') {
			$this->session->set_flashdata('dev_verify_link', $verification_link);
		}

		log_message('info', 'Registration success: user_id=' . $user_id . ' email=' . $email . ' email_sent=' . (int) $email_sent);
		$this->ratelimiter->clear('auth_register:' . $ip);

		$this->session->set_flashdata('verify_email', $email);
		$this->session->set_flashdata('auth_success', 'Registration successful. Please verify your email before logging in.');
		return $this->json_response(array('ok' => FALSE, 'message' => 'Request processed.', 'data' => NULL), 400);
	}

	public function verify_email($token = NULL)
	{
		$token = is_string($token) ? trim($token) : '';

		if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
			log_message('error', 'Email verification failed: malformed token');
			$this->session->set_flashdata('auth_error', 'Invalid verification link.');
			return $this->json_response(array('ok' => FALSE, 'message' => 'Request processed.', 'data' => NULL), 400);
			return;
		}

		$token_hash = hash('sha256', $token);
		$user = $this->user_model->find_active_email_verify_token($token_hash);

		if (!$user) {
			log_message('error', 'Email verification failed: invalid or expired token');
			$this->session->set_flashdata('auth_error', 'Verification link is invalid or expired.');
			return $this->json_response(array('ok' => FALSE, 'message' => 'Request processed.', 'data' => NULL), 400);
			return;
		}

		$verified = $this->user_model->mark_email_verified((int) $user['id']);
		if (!$verified) {
			log_message('error', 'Email verification failed: DB update failed for user_id=' . $user['id']);
			$this->session->set_flashdata('auth_error', 'Could not verify your account right now.');
			return $this->json_response(array('ok' => FALSE, 'message' => 'Request processed.', 'data' => NULL), 400);
			return;
		}

		log_message('info', 'Email verification success: user_id=' . $user['id'] . ' email=' . $user['email']);
		$this->session->set_flashdata('auth_success', 'Email verified successfully. You can now log in.');
		return $this->json_response(array('ok' => FALSE, 'message' => 'Request processed.', 'data' => NULL), 400);
	}

	public function do_login()
	{
		if (strtoupper($this->input->method()) !== 'POST') {
			return $this->json_response(array('ok' => FALSE, 'message' => 'Request processed.', 'data' => NULL), 400);
			return;
		}

		$ip = (string) $this->input->ip_address();
		if ($this->is_rate_limited('auth_login_ip', 'auth_login_ip:' . $ip)) {
			log_message('error', 'Login rate limit hit (ip)=' . $ip);
			$this->session->set_flashdata('auth_error', 'Too many login attempts. Please try again later.');
			return $this->json_response(array('ok' => FALSE, 'message' => 'Request processed.', 'data' => NULL), 400);
			return;
		}

		$this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email|max_length[255]');
		$this->form_validation->set_rules('password', 'Password', 'required');

		if ($this->form_validation->run() === FALSE) {
			$this->ratelimiter->hit('auth_login_ip:' . $ip, $this->rate_limit_window('auth_login_ip'));
			$this->session->set_flashdata('auth_error', validation_errors('<p style="margin:4px 0;">', '</p>'));
			return $this->json_response(array('ok' => FALSE, 'message' => 'Request processed.', 'data' => NULL), 400);
			return;
		}

		$email = strtolower(trim((string) $this->input->post('email', TRUE)));
		$identity_key = 'auth_login_identity:' . hash('sha256', $email);
		if ($this->is_rate_limited('auth_login_identity', $identity_key)) {
			log_message('error', 'Login rate limit hit (identity hash) ip=' . $ip);
			$this->session->set_flashdata('auth_error', 'Too many login attempts for this account. Please try again later.');
			return $this->json_response(array('ok' => FALSE, 'message' => 'Request processed.', 'data' => NULL), 400);
			return;
		}

		$password = (string) $this->input->post('password', FALSE);
		$user = $this->user_model->find_by_email($email);

		if (!$user) {
			$this->ratelimiter->hit('auth_login_ip:' . $ip, $this->rate_limit_window('auth_login_ip'));
			$this->ratelimiter->hit($identity_key, $this->rate_limit_window('auth_login_identity'));
			log_message('error', 'Login failed: unknown email=' . $email);
			$this->session->set_flashdata('auth_error', 'Invalid email or password.');
			return $this->json_response(array('ok' => FALSE, 'message' => 'Request processed.', 'data' => NULL), 400);
			return;
		}

		$user_id = (int) $user['id'];
		if (!empty($user['locked_until']) && strtotime((string) $user['locked_until']) > time()) {
			log_message('error', 'Login blocked: account locked user_id=' . $user_id);
			$this->session->set_flashdata('auth_error', 'Account temporarily locked. Try again later.');
			return $this->json_response(array('ok' => FALSE, 'message' => 'Request processed.', 'data' => NULL), 400);
			return;
		}

		if (!password_verify($password, (string) $user['password_hash'])) {
			$this->user_model->record_failed_login($user_id);
			$this->apply_lockout_if_needed($user);
			$this->ratelimiter->hit('auth_login_ip:' . $ip, $this->rate_limit_window('auth_login_ip'));
			$this->ratelimiter->hit($identity_key, $this->rate_limit_window('auth_login_identity'));
			log_message('error', 'Login failed: bad password user_id=' . $user_id);
			$this->session->set_flashdata('auth_error', 'Invalid email or password.');
			return $this->json_response(array('ok' => FALSE, 'message' => 'Request processed.', 'data' => NULL), 400);
			return;
		}

		if ((string) $user['status'] !== 'active' || empty($user['email_verified_at'])) {
			$this->ratelimiter->hit('auth_login_ip:' . $ip, $this->rate_limit_window('auth_login_ip'));
			$this->ratelimiter->hit($identity_key, $this->rate_limit_window('auth_login_identity'));
			log_message('error', 'Login blocked: unverified/inactive user_id=' . $user_id);
			$this->session->set_flashdata('auth_error', 'Please verify your email before logging in.');
			return $this->json_response(array('ok' => FALSE, 'message' => 'Request processed.', 'data' => NULL), 400);
			return;
		}

		$this->user_model->clear_lock($user_id);
		$this->user_model->touch_last_login($user_id);
		$this->set_authenticated_session($user);
		$this->ratelimiter->clear('auth_login_ip:' . $ip);
		$this->ratelimiter->clear($identity_key);

		log_message('info', 'Login success: user_id=' . $user_id . ' email=' . $user['email']);
		$this->session->set_flashdata('auth_success', 'Login successful.');
		return $this->json_response(array('ok' => FALSE, 'message' => 'Request processed.', 'data' => NULL), 400);
	}

	public function logout()
	{
		$user_id = (int) $this->session->userdata('auth_user_id');

		$this->session->unset_userdata(array(
			'auth_user_id',
			'auth_role',
			'is_authenticated',
			'logged_in_at'
		));
		$this->session->sess_regenerate(TRUE);

		if ($user_id > 0) {
			log_message('info', 'Logout success: user_id=' . $user_id);
		}

		$this->session->set_flashdata('auth_success', 'You have been logged out.');
		return $this->json_response(array('ok' => FALSE, 'message' => 'Request processed.', 'data' => NULL), 400);
	}

	public function send_reset()
	{
		if (strtoupper($this->input->method()) !== 'POST') {
			return $this->json_response(array('ok' => FALSE, 'message' => 'Request processed.', 'data' => NULL), 400);
			return;
		}

		$ip = (string) $this->input->ip_address();
		if ($this->is_rate_limited('auth_reset_request_ip', 'auth_reset_request_ip:' . $ip)) {
			log_message('error', 'Password reset rate limit hit (ip)=' . $ip);
			$this->session->set_flashdata('auth_error', 'Too many reset requests. Please try again later.');
			return $this->json_response(array('ok' => FALSE, 'message' => 'Request processed.', 'data' => NULL), 400);
			return;
		}

		$this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email|max_length[255]');
		if ($this->form_validation->run() === FALSE) {
			$this->ratelimiter->hit('auth_reset_request_ip:' . $ip, $this->rate_limit_window('auth_reset_request_ip'));
			$this->session->set_flashdata('auth_error', validation_errors('<p style="margin:4px 0;">', '</p>'));
			return $this->json_response(array('ok' => FALSE, 'message' => 'Request processed.', 'data' => NULL), 400);
			return;
		}

		$email = strtolower(trim((string) $this->input->post('email', TRUE)));
		$identity_key = 'auth_reset_request_identity:' . hash('sha256', $email);
		if ($this->is_rate_limited('auth_reset_request_identity', $identity_key)) {
			log_message('error', 'Password reset rate limit hit (identity hash) ip=' . $ip);
			$this->session->set_flashdata('auth_error', 'Too many reset requests for this account. Please try again later.');
			return $this->json_response(array('ok' => FALSE, 'message' => 'Request processed.', 'data' => NULL), 400);
			return;
		}

		$user = $this->user_model->find_by_email($email);
		$generic_message = 'If an account exists for that email, a reset link has been sent.';

		if (!$user || (string) $user['status'] === 'deleted') {
			$this->ratelimiter->hit('auth_reset_request_ip:' . $ip, $this->rate_limit_window('auth_reset_request_ip'));
			$this->ratelimiter->hit($identity_key, $this->rate_limit_window('auth_reset_request_identity'));
			log_message('error', 'Password reset requested for unknown/deleted account email=' . $email);
			$this->session->set_flashdata('auth_success', $generic_message);
			return $this->json_response(array('ok' => FALSE, 'message' => 'Request processed.', 'data' => NULL), 400);
			return;
		}

		try {
			$raw_token = bin2hex(random_bytes(32));
		} catch (Exception $e) {
			log_message('error', 'Password reset token generation failed for user_id=' . $user['id'] . ' - ' . $e->getMessage());
			$this->session->set_flashdata('auth_error', 'Could not start password reset right now. Please try again.');
			return $this->json_response(array('ok' => FALSE, 'message' => 'Request processed.', 'data' => NULL), 400);
			return;
		}

		$token_hash = hash('sha256', $raw_token);
		$ttl_seconds = (int) $this->auth_config_item('password_reset_ttl_seconds', 3600);
		$expires_at = new DateTime('+' . $ttl_seconds . ' seconds');

		$saved = $this->user_model->set_password_reset_token((int) $user['id'], $token_hash, $expires_at);
		if (!$saved) {
			$this->ratelimiter->hit('auth_reset_request_ip:' . $ip, $this->rate_limit_window('auth_reset_request_ip'));
			$this->ratelimiter->hit($identity_key, $this->rate_limit_window('auth_reset_request_identity'));
			log_message('error', 'Password reset token save failed for user_id=' . $user['id']);
			$this->session->set_flashdata('auth_error', 'Could not start password reset right now. Please try again.');
			return $this->json_response(array('ok' => FALSE, 'message' => 'Request processed.', 'data' => NULL), 400);
			return;
		}

		$reset_link = site_url('auth/reset_password/' . $raw_token);
		$email_sent = $this->send_reset_email((string) $user['email'], $reset_link, $expires_at);
		if (!$email_sent && ENVIRONMENT !== 'production') {
			$this->session->set_flashdata('dev_reset_link', $reset_link);
		}

		log_message('info', 'Password reset requested: user_id=' . $user['id'] . ' email_sent=' . (int) $email_sent);
		$this->ratelimiter->hit('auth_reset_request_ip:' . $ip, $this->rate_limit_window('auth_reset_request_ip'));
		$this->ratelimiter->hit($identity_key, $this->rate_limit_window('auth_reset_request_identity'));
		$this->session->set_flashdata('auth_success', $generic_message);
		return $this->json_response(array('ok' => FALSE, 'message' => 'Request processed.', 'data' => NULL), 400);
	}

	public function do_reset_password()
	{
		if (strtoupper($this->input->method()) !== 'POST') {
			return $this->json_response(array('ok' => FALSE, 'message' => 'Request processed.', 'data' => NULL), 400);
			return;
		}

		$this->form_validation->set_rules('token', 'Token', 'required');
		$this->form_validation->set_rules('password', 'Password', 'required|callback__strong_password');
		$this->form_validation->set_rules('password_confirm', 'Confirm Password', 'required|matches[password]');

		$token = trim((string) $this->input->post('token', TRUE));

		if ($this->form_validation->run() === FALSE) {
			$this->session->set_flashdata('auth_error', validation_errors('<p style="margin:4px 0;">', '</p>'));
			return $this->json_response(array('ok' => FALSE, 'message' => 'Request processed.', 'data' => NULL), 400);
			return;
		}

		if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
			log_message('error', 'Password reset submit failed: malformed token');
			$this->session->set_flashdata('auth_error', 'Invalid reset token.');
			return $this->json_response(array('ok' => FALSE, 'message' => 'Request processed.', 'data' => NULL), 400);
			return;
		}

		$token_hash = hash('sha256', $token);
		$user = $this->user_model->find_active_password_reset_token($token_hash);
		if (!$user) {
			log_message('error', 'Password reset submit failed: expired/invalid token');
			$this->session->set_flashdata('auth_error', 'Reset token is invalid or expired.');
			return $this->json_response(array('ok' => FALSE, 'message' => 'Request processed.', 'data' => NULL), 400);
			return;
		}

		$new_password = (string) $this->input->post('password', FALSE);
		$new_hash = password_hash($new_password, PASSWORD_DEFAULT);
		if ($new_hash === FALSE) {
			log_message('error', 'Password reset failed: hash error user_id=' . $user['id']);
			$this->session->set_flashdata('auth_error', 'Could not update password right now.');
			return $this->json_response(array('ok' => FALSE, 'message' => 'Request processed.', 'data' => NULL), 400);
			return;
		}

		$updated = $this->user_model->update_password_hash((int) $user['id'], $new_hash);
		if (!$updated) {
			log_message('error', 'Password reset failed: DB update error user_id=' . $user['id']);
			$this->session->set_flashdata('auth_error', 'Could not update password right now.');
			return $this->json_response(array('ok' => FALSE, 'message' => 'Request processed.', 'data' => NULL), 400);
			return;
		}

		$this->user_model->clear_password_reset_token((int) $user['id']);
		$this->user_model->clear_lock((int) $user['id']);

		log_message('info', 'Password reset success: user_id=' . $user['id']);
		$this->session->set_flashdata('auth_success', 'Password updated. You can now log in.');
		return $this->json_response(array('ok' => FALSE, 'message' => 'Request processed.', 'data' => NULL), 400);
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

		log_message('error', 'Registration blocked: disallowed domain=' . $domain);
		$this->form_validation->set_message('_email_domain_allowed', 'Use an approved university email domain.');
		return FALSE;
	}

	public function _email_available($email)
	{
		$email = strtolower(trim((string) $email));
		if (!$this->user_model->email_exists($email)) {
			return TRUE;
		}

		log_message('error', 'Registration blocked: duplicate email=' . $email);
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
			'Password must be at least ' . $min_length . ' characters and include upper, lower, number, and special character (no spaces).'
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
			"Welcome to Alumni Influencers.\n\n" .
				"Verify your email by opening this link:\n" . $verification_link . "\n\n" .
				"This link expires at " . $expires_at->format('Y-m-d H:i:s') . ".\n"
		);

		$sent = $this->email->send(FALSE);
		if ($sent) {
			return TRUE;
		}

		log_message('error', 'Email send failed for ' . $to_email);
		if (ENVIRONMENT !== 'production') {
			log_message('info', 'DEV verification link for ' . $to_email . ': ' . $verification_link);
		}

		return FALSE;
	}

	private function send_reset_email($to_email, $reset_link, DateTime $expires_at)
	{
		$this->load->library('email');

		$from_email = getenv('MAIL_FROM') ? getenv('MAIL_FROM') : 'no-reply@alumni-influencers.local';
		$from_name = getenv('MAIL_FROM_NAME') ? getenv('MAIL_FROM_NAME') : 'Alumni Influencers';

		$this->email->clear(TRUE);
		$this->email->from($from_email, $from_name);
		$this->email->to($to_email);
		$this->email->subject('Reset your Alumni Influencers password');
		$this->email->message(
			"You requested a password reset.\n\n" .
				"Open this link to set a new password:\n" . $reset_link . "\n\n" .
				"This link expires at " . $expires_at->format('Y-m-d H:i:s') . ".\n"
		);

		$sent = $this->email->send(FALSE);
		if ($sent) {
			return TRUE;
		}

		log_message('error', 'Reset email send failed for ' . $to_email);
		if (ENVIRONMENT !== 'production') {
			log_message('info', 'DEV reset link for ' . $to_email . ': ' . $reset_link);
		}

		return FALSE;
	}

	private function set_authenticated_session(array $user)
	{
		$this->session->sess_regenerate(TRUE);
		$this->session->set_userdata(array(
			'auth_user_id' => (int) $user['id'],
			'auth_role' => (string) $user['role'],
			'is_authenticated' => TRUE,
			'logged_in_at' => date('Y-m-d H:i:s')
		));
	}

	private function apply_lockout_if_needed(array $user)
	{
		$max_attempts = (int) $this->auth_config_item('max_failed_logins', 5);
		$lock_minutes = (int) $this->auth_config_item('lockout_minutes', 15);
		$next_count = (int) $user['failed_login_count'] + 1;

		if ($next_count < $max_attempts) {
			return;
		}

		$locked_until = new DateTime('+' . $lock_minutes . ' minutes');
		$this->user_model->set_locked_until((int) $user['id'], $locked_until);
		log_message('error', 'Account lock applied: user_id=' . $user['id'] . ' until=' . $locked_until->format('Y-m-d H:i:s'));
	}

	private function is_authenticated()
	{
		return (bool) $this->session->userdata('is_authenticated');
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

	private function rate_limit_window($name)
	{
		$limits = $this->config->item('rate_limits', 'security_hardening');
		if (!is_array($limits) || !isset($limits[$name]['window_seconds'])) {
			return 60;
		}

		return max(1, (int) $limits[$name]['window_seconds']);
	}

	private function is_rate_limited($name, $key)
	{
		$limits = $this->config->item('rate_limits', 'security_hardening');
		if (!is_array($limits) || !isset($limits[$name])) {
			return FALSE;
		}

		$limit = isset($limits[$name]['limit']) ? (int) $limits[$name]['limit'] : 0;
		$window = isset($limits[$name]['window_seconds']) ? (int) $limits[$name]['window_seconds'] : 60;
		if ($limit <= 0) {
			return FALSE;
		}

		return $this->ratelimiter->is_limited($key, $limit, $window);
	}

    private function json_response(array $payload, $status_code)
    {
            return $this->output
                    ->set_content_type('application/json', 'utf-8')
                    ->set_status_header((int) $status_code)
                    ->set_output(json_encode($payload, JSON_UNESCAPED_SLASHES));
    }
}
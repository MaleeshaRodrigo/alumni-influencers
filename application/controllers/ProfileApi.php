<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Authenticated profile JSON API.
 *
 * @property CI_Input $input
 * @property CI_Output $output
 * @property CI_Config $config
 * @property CI_Form_validation $form_validation
 * @property CI_Upload $upload
 * @property BearerTokenAuth $bearer_auth
 * @property RateLimiter $ratelimiter
 * @property Profile_model $profile_model
 * @property Degree_model $degree_model
 * @property Certification_model $certification_model
 * @property Licence_model $licence_model
 * @property Course_model $course_model
 * @property Employment_model $employment_model
 * @property User_model $user_model
 */
class ProfileApi extends CI_Controller
{
	/** @var array<string, string> */
	private $extra_validation_errors = array();

	public function __construct()
	{
		parent::__construct();
		$this->load->model('Profile_model', 'profile_model');
		$this->load->model('Degree_model', 'degree_model');
		$this->load->model('Certification_model', 'certification_model');
		$this->load->model('Licence_model', 'licence_model');
		$this->load->model('Course_model', 'course_model');
		$this->load->model('Employment_model', 'employment_model');
		$this->load->model('User_model', 'user_model');
		$this->load->library('BearerTokenAuth', NULL, 'bearer_auth');
		$this->load->library('RateLimiter');
		$this->load->library('form_validation');
		$this->load->helper(array('url', 'form'));
		$this->config->load('security_hardening', TRUE);
	}

	public function profile()
	{
		$context = $this->begin_request(array('GET'), FALSE, 'profile_api_read');
		if (!$context['ok']) {
			return $context['response'];
		}

		$profile = $this->profile_model->get_by_user_id((int) $context['user']['id']);
		return $this->respond($context, array(
			'ok' => TRUE,
			'message' => 'Profile fetched successfully.',
			'data' => $profile
		), 200);
	}

	public function basic()
	{
		$context = $this->begin_request(array('GET'), FALSE, 'profile_api_read');
		if (!$context['ok']) {
			return $context['response'];
		}

		$profile = $this->profile_model->get_by_user_id((int) $context['user']['id']);
		return $this->respond($context, array(
			'ok' => TRUE,
			'message' => 'Basic profile fetched successfully.',
			'data' => $profile
		), 200);
	}

	public function save_basic()
	{
		$context = $this->begin_request(array('POST'), TRUE, 'profile_api_write');
		if (!$context['ok']) {
			return $context['response'];
		}

		$this->normalize_basic_payload();
		$this->form_validation->set_rules('display_name', 'Display Name', 'trim|required|max_length[150]');
		$this->form_validation->set_rules('bio', 'Bio', 'trim|max_length[5000]');
		$this->form_validation->set_rules('linkedin_url', 'LinkedIn URL', 'trim|max_length[512]|callback__valid_linkedin_url');
		$this->form_validation->set_rules('is_public', 'Public Profile', 'trim|in_list[0,1,true,false]');

		if ($this->form_validation->run() === FALSE) {
			return $this->validation_failed($context, 'Validation failed for basic profile.');
		}

		$payload = array(
			'display_name' => trim((string) $this->input->post('display_name', TRUE)),
			'bio' => trim((string) $this->input->post('bio', TRUE)),
			'linkedin_url' => trim((string) $this->input->post('linkedin_url', TRUE))
		);
		if ($payload['bio'] === '') {
			$payload['bio'] = NULL;
		}
		if ($payload['linkedin_url'] === '') {
			$payload['linkedin_url'] = NULL;
		}

		$is_public_raw = strtolower(trim((string) $this->input->post('is_public', TRUE)));
		if ($is_public_raw !== '') {
			$payload['is_public'] = in_array($is_public_raw, array('1', 'true'), TRUE) ? 1 : 0;
		}

		$current_profile = $this->profile_model->get_by_user_id((int) $context['user']['id']);
		$old_photo_path = $current_profile && !empty($current_profile['photo_path']) ? (string) $current_profile['photo_path'] : NULL;
		$new_photo_path = NULL;

		if (isset($_FILES['profile_image']) && !empty($_FILES['profile_image']['name'])) {
			$upload_result = $this->upload_profile_photo('profile_image');
			if (!$upload_result['ok']) {
				return $this->respond($context, array(
					'ok' => FALSE,
					'message' => 'Profile image upload failed.',
					'errors' => array('profile_image' => (string) $upload_result['error'])
				), 400);
			}

			$new_photo_path = $upload_result['relative_path'];
			$payload['photo_path'] = $new_photo_path;
		}

		$profile_id = $this->profile_model->save_basic_by_user_id((int) $context['user']['id'], $payload);
		if (!$profile_id) {
			if ($new_photo_path !== NULL) {
				$this->safe_delete_profile_photo($new_photo_path);
			}
			log_message('error', 'Profile API basic save failed user_id='.(int) $context['user']['id']);
			return $this->respond($context, array(
				'ok' => FALSE,
				'message' => 'Could not save profile right now.',
				'data' => NULL
			), 500);
		}

		if ($new_photo_path !== NULL && $old_photo_path !== NULL && $old_photo_path !== $new_photo_path) {
			$this->safe_delete_profile_photo($old_photo_path);
		}

		$profile = $this->profile_model->get_by_id((int) $profile_id);
		return $this->respond($context, array(
			'ok' => TRUE,
			'message' => 'Basic profile saved successfully.',
			'data' => $profile
		), 200);
	}

	public function degrees()
	{
		return $this->list_section('degrees');
	}

	public function add_degree()
	{
		return $this->create_section('degrees');
	}

	public function update_degree($id)
	{
		return $this->update_section('degrees', (int) $id);
	}

	public function delete_degree($id)
	{
		return $this->delete_section('degrees', (int) $id);
	}

	public function certifications()
	{
		return $this->list_section('certifications');
	}

	public function add_certification()
	{
		return $this->create_section('certifications');
	}

	public function update_certification($id)
	{
		return $this->update_section('certifications', (int) $id);
	}

	public function delete_certification($id)
	{
		return $this->delete_section('certifications', (int) $id);
	}

	public function licences()
	{
		return $this->list_section('licences');
	}

	public function add_licence()
	{
		return $this->create_section('licences');
	}

	public function update_licence($id)
	{
		return $this->update_section('licences', (int) $id);
	}

	public function delete_licence($id)
	{
		return $this->delete_section('licences', (int) $id);
	}

	public function courses()
	{
		return $this->list_section('courses');
	}

	public function add_course()
	{
		return $this->create_section('courses');
	}

	public function update_course($id)
	{
		return $this->update_section('courses', (int) $id);
	}

	public function delete_course($id)
	{
		return $this->delete_section('courses', (int) $id);
	}

	public function employment()
	{
		return $this->list_section('employment');
	}

	public function add_employment()
	{
		return $this->create_section('employment');
	}

	public function update_employment($id)
	{
		return $this->update_section('employment', (int) $id);
	}

	public function delete_employment($id)
	{
		return $this->delete_section('employment', (int) $id);
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

	public function _valid_optional_date($date)
	{
		$date = trim((string) $date);
		if ($date === '') {
			return TRUE;
		}

		$dt = DateTime::createFromFormat('Y-m-d', $date);
		if ($dt && $dt->format('Y-m-d') === $date) {
			return TRUE;
		}

		$this->form_validation->set_message('_valid_optional_date', 'Use a valid date in YYYY-MM-DD format.');
		return FALSE;
	}

	public function _valid_optional_web_url($value)
	{
		$value = trim((string) $value);
		if ($value === '') {
			return TRUE;
		}

		if (preg_match('#^https?://#i', $value) && filter_var($value, FILTER_VALIDATE_URL)) {
			return TRUE;
		}

		if (!preg_match('#^https?://#i', $value)) {
			return TRUE;
		}

		$this->form_validation->set_message('_valid_optional_web_url', 'Enter a valid URL starting with http:// or https://');
		return FALSE;
	}

	private function list_section($section)
	{
		$context = $this->begin_request(array('GET'), FALSE, 'profile_api_read');
		if (!$context['ok']) {
			return $context['response'];
		}

		$profile = $this->profile_for_sections($context);
		if (!$profile) {
			return $this->respond($context, array(
				'ok' => FALSE,
				'message' => 'Complete basic profile first before managing sections.',
				'data' => NULL
			), 404);
		}

		$config = $this->section_config($section);
		$items = $this->{$config['model']}->list_by_profile_id((int) $profile['id']);

		return $this->respond($context, array(
			'ok' => TRUE,
			'message' => ucfirst($section).' fetched successfully.',
			'data' => array(
				'profile_id' => (int) $profile['id'],
				'items' => $items
			)
		), 200);
	}

	private function create_section($section)
	{
		$context = $this->begin_request(array('POST'), TRUE, 'profile_api_write');
		if (!$context['ok']) {
			return $context['response'];
		}

		$profile = $this->profile_for_sections($context);
		if (!$profile) {
			return $this->respond($context, array(
				'ok' => FALSE,
				'message' => 'Complete basic profile first before managing sections.',
				'data' => NULL
			), 404);
		}

		$this->apply_section_validation($section);
		if (!$this->form_validation->run() || !$this->validate_section_date_ranges($section)) {
			return $this->validation_failed($context, 'Validation failed for section item.');
		}

		$config = $this->section_config($section);
		$payload = $this->section_payload($section, (int) $profile['id']);
		$id = $this->{$config['model']}->create($payload);
		if (!$id) {
			log_message('error', 'Profile API section create failed section='.$section.' profile_id='.(int) $profile['id']);
			return $this->respond($context, array(
				'ok' => FALSE,
				'message' => 'Could not create section item right now.',
				'data' => NULL
			), 500);
		}

		$created = $this->{$config['model']}->get_by_id_and_profile((int) $id, (int) $profile['id']);
		return $this->respond($context, array(
			'ok' => TRUE,
			'message' => $config['entity_label'].' created.',
			'data' => $created
		), 201);
	}

	private function update_section($section, $id)
	{
		$context = $this->begin_request(array('POST'), TRUE, 'profile_api_write');
		if (!$context['ok']) {
			return $context['response'];
		}

		$profile = $this->profile_for_sections($context);
		if (!$profile) {
			return $this->respond($context, array(
				'ok' => FALSE,
				'message' => 'Complete basic profile first before managing sections.',
				'data' => NULL
			), 404);
		}

		$config = $this->section_config($section);
		$model = $this->{$config['model']};
		$existing = $model->get_by_id_and_profile((int) $id, (int) $profile['id']);
		if (!$existing) {
			return $this->respond($context, array(
				'ok' => FALSE,
				'message' => 'Record not found.',
				'data' => NULL
			), 404);
		}

		$this->apply_section_validation($section);
		if (!$this->form_validation->run() || !$this->validate_section_date_ranges($section)) {
			return $this->validation_failed($context, 'Validation failed for section item.');
		}

		$payload = $this->section_payload($section, (int) $profile['id'], FALSE);
		$ok = $model->update((int) $id, $payload);
		if (!$ok) {
			log_message('error', 'Profile API section update failed section='.$section.' id='.(int) $id);
			return $this->respond($context, array(
				'ok' => FALSE,
				'message' => 'Could not update section item right now.',
				'data' => NULL
			), 500);
		}

		$updated = $model->get_by_id_and_profile((int) $id, (int) $profile['id']);
		return $this->respond($context, array(
			'ok' => TRUE,
			'message' => $config['entity_label'].' updated.',
			'data' => $updated
		), 200);
	}

	private function delete_section($section, $id)
	{
		$context = $this->begin_request(array('POST'), TRUE, 'profile_api_write');
		if (!$context['ok']) {
			return $context['response'];
		}

		$profile = $this->profile_for_sections($context);
		if (!$profile) {
			return $this->respond($context, array(
				'ok' => FALSE,
				'message' => 'Complete basic profile first before managing sections.',
				'data' => NULL
			), 404);
		}

		$config = $this->section_config($section);
		$model = $this->{$config['model']};
		$existing = $model->get_by_id_and_profile((int) $id, (int) $profile['id']);
		if (!$existing) {
			return $this->respond($context, array(
				'ok' => FALSE,
				'message' => 'Record not found.',
				'data' => NULL
			), 404);
		}

		$ok = $model->delete((int) $id);
		if (!$ok) {
			log_message('error', 'Profile API section delete failed section='.$section.' id='.(int) $id);
			return $this->respond($context, array(
				'ok' => FALSE,
				'message' => 'Could not delete section item right now.',
				'data' => NULL
			), 500);
		}

		return $this->respond($context, array(
			'ok' => TRUE,
			'message' => $config['entity_label'].' deleted.',
			'data' => array('id' => (int) $id)
		), 200);
	}

	private function begin_request(array $allowed_methods, $write_scope_required, $rate_limit_name)
	{
		$started_at = microtime(TRUE);
		$method = strtoupper((string) $this->input->method(TRUE));
		$ip = (string) $this->input->ip_address();
		$rate_window = $this->rate_limit_window($rate_limit_name);
		$rate_key = $rate_limit_name.':'.$ip;

		$base_context = array(
			'ok' => FALSE,
			'started_at' => $started_at,
			'rate_key' => $rate_key,
			'rate_window' => $rate_window,
			'api_key_id' => NULL,
			'user' => NULL
		);

		if ($this->is_rate_limited($rate_limit_name, $rate_key)) {
			return array(
				'ok' => FALSE,
				'response' => $this->json_response(array(
					'ok' => FALSE,
					'message' => 'Too many requests. Please try again later.',
					'data' => NULL
				), 429)
			);
		}

		if (!in_array($method, $allowed_methods, TRUE)) {
			$payload = array(
				'ok' => FALSE,
				'message' => 'Method not allowed. Use '.implode(', ', $allowed_methods).'.',
				'data' => NULL
			);
			return array(
				'ok' => FALSE,
				'response' => $this->respond($base_context, $payload, 405)
			);
		}

		$auth = $this->bearer_auth->validate_request(array());
		if (!$auth['ok']) {
			$payload = array(
				'ok' => FALSE,
				'message' => (string) $auth['error'],
				'data' => NULL
			);
			return array(
				'ok' => FALSE,
				'response' => $this->respond($base_context, $payload, (int) $auth['code'])
			);
		}

		$api_key = $auth['api_key'];
		$base_context['api_key_id'] = (int) $api_key['id'];

		$required_any = $write_scope_required
			? array('profile.write', 'profile.manage')
			: array('profile.read', 'profile.write', 'profile.manage');
		if (!$this->api_key_has_any_scope($api_key, $required_any)) {
			return array(
				'ok' => FALSE,
				'response' => $this->respond($base_context, array(
					'ok' => FALSE,
					'message' => 'Bearer token does not have required scope.',
					'data' => NULL
				), 403)
			);
		}

		$user = $this->user_model->find_by_id((int) $api_key['user_id']);
		if (!$user || (string) $user['status'] !== 'active' || empty($user['email_verified_at'])) {
			return array(
				'ok' => FALSE,
				'response' => $this->respond($base_context, array(
					'ok' => FALSE,
					'message' => 'API key owner account is not active and verified.',
					'data' => NULL
				), 403)
			);
		}

		$base_context['ok'] = TRUE;
		$base_context['user'] = $user;
		return $base_context;
	}

	private function respond(array $context, array $payload, $status_code)
	{
		if (isset($context['api_key_id'])) {
			$this->bearer_auth->log_usage($context['api_key_id'], (int) $status_code, $context['started_at']);
		}

		if (isset($context['rate_key']) && isset($context['rate_window'])) {
			$this->ratelimiter->hit((string) $context['rate_key'], (int) $context['rate_window']);
		}

		return $this->json_response($payload, (int) $status_code);
	}

	private function validation_failed(array $context, $message)
	{
		return $this->respond($context, array(
			'ok' => FALSE,
			'message' => (string) $message,
			'errors' => $this->validation_errors_payload(),
			'data' => NULL
		), 400);
	}

	private function validation_errors_payload()
	{
		$errors = $this->form_validation->error_array();
		if (!is_array($errors)) {
			$errors = array();
		}

		if (!empty($this->extra_validation_errors)) {
			$errors = array_merge($errors, $this->extra_validation_errors);
		}

		$this->extra_validation_errors = array();
		return $errors;
	}

	private function normalize_basic_payload()
	{
		$display_name = trim((string) $this->input->post('display_name', TRUE));
		if ($display_name === '') {
			$fallback = trim((string) $this->input->post('full_name', TRUE));
			if ($fallback !== '') {
				$_POST['display_name'] = $fallback;
			}
		}
	}

	private function profile_for_sections(array $context)
	{
		return $this->profile_model->get_by_user_id((int) $context['user']['id']);
	}

	private function apply_section_validation($section)
	{
		$this->extra_validation_errors = array();

		if ($section === 'degrees') {
			$this->form_validation->set_rules('institution', 'Institution', 'trim|required|max_length[255]');
			$this->form_validation->set_rules('qualification', 'Qualification', 'trim|required|max_length[255]');
			$this->form_validation->set_rules('field_of_study', 'Field of Study', 'trim|max_length[255]');
			$this->form_validation->set_rules('grade_or_classification', 'Grade / Classification', 'trim|max_length[100]');
			$this->form_validation->set_rules('started_on', 'Start Date', 'trim|callback__valid_optional_date');
			$this->form_validation->set_rules('completed_on', 'Completion Date', 'trim|callback__valid_optional_date');
			return;
		}

		if ($section === 'certifications') {
			$this->form_validation->set_rules('name', 'Certification Name', 'trim|required|max_length[255]');
			$this->form_validation->set_rules('issuer', 'Issuer', 'trim|max_length[255]');
			$this->form_validation->set_rules('credential_id', 'Credential ID / URL', 'trim|max_length[128]|callback__valid_optional_web_url');
			$this->form_validation->set_rules('issued_on', 'Issued Date', 'trim|callback__valid_optional_date');
			$this->form_validation->set_rules('expires_on', 'Expiry Date', 'trim|callback__valid_optional_date');
			return;
		}

		if ($section === 'licences') {
			$this->form_validation->set_rules('title', 'Licence Title', 'trim|required|max_length[255]');
			$this->form_validation->set_rules('issuing_body', 'Issuing Body', 'trim|max_length[255]');
			$this->form_validation->set_rules('licence_number', 'Licence Number', 'trim|max_length[128]');
			$this->form_validation->set_rules('jurisdiction', 'Jurisdiction', 'trim|max_length[128]');
			$this->form_validation->set_rules('valid_from', 'Valid From', 'trim|callback__valid_optional_date');
			$this->form_validation->set_rules('valid_to', 'Valid To', 'trim|callback__valid_optional_date');
			return;
		}

		if ($section === 'courses') {
			$this->form_validation->set_rules('title', 'Course Title', 'trim|required|max_length[255]');
			$this->form_validation->set_rules('provider', 'Provider', 'trim|max_length[255]|callback__valid_optional_web_url');
			$this->form_validation->set_rules('completed_on', 'Completion Date', 'trim|callback__valid_optional_date');
			$this->form_validation->set_rules('hours', 'Hours', 'trim|numeric');
			return;
		}

		if ($section === 'employment') {
			$this->form_validation->set_rules('employer', 'Employer', 'trim|required|max_length[255]');
			$this->form_validation->set_rules('job_title', 'Job Title', 'trim|required|max_length[255]');
			$this->form_validation->set_rules('location', 'Location', 'trim|max_length[255]');
			$this->form_validation->set_rules('started_on', 'Start Date', 'trim|callback__valid_optional_date');
			$this->form_validation->set_rules('ended_on', 'End Date', 'trim|callback__valid_optional_date');
			$this->form_validation->set_rules('description', 'Description', 'trim');
		}
	}

	private function validate_section_date_ranges($section)
	{
		$start = '';
		$end = '';

		if ($section === 'degrees') {
			$start = trim((string) $this->input->post('started_on', TRUE));
			$end = trim((string) $this->input->post('completed_on', TRUE));
		} elseif ($section === 'certifications') {
			$start = trim((string) $this->input->post('issued_on', TRUE));
			$end = trim((string) $this->input->post('expires_on', TRUE));
		} elseif ($section === 'licences') {
			$start = trim((string) $this->input->post('valid_from', TRUE));
			$end = trim((string) $this->input->post('valid_to', TRUE));
		} elseif ($section === 'employment') {
			$start = trim((string) $this->input->post('started_on', TRUE));
			$end = trim((string) $this->input->post('ended_on', TRUE));
			$is_current = (int) $this->input->post('is_current', TRUE) === 1;
			if ($is_current) {
				return TRUE;
			}
		}

		if ($start === '' || $end === '') {
			return TRUE;
		}

		$from_dt = DateTime::createFromFormat('Y-m-d', $start);
		$to_dt = DateTime::createFromFormat('Y-m-d', $end);
		if (!$from_dt || !$to_dt) {
			return TRUE;
		}

		if ($to_dt < $from_dt) {
			$this->extra_validation_errors['date_range'] = 'End/expiry date cannot be before start/issued date.';
			return FALSE;
		}

		return TRUE;
	}

	private function section_payload($section, $profile_id, $include_profile = TRUE)
	{
		$data = array();
		if ($include_profile) {
			$data['profile_id'] = (int) $profile_id;
		}

		if ($section === 'degrees') {
			$data['institution'] = trim((string) $this->input->post('institution', TRUE));
			$data['qualification'] = trim((string) $this->input->post('qualification', TRUE));
			$data['field_of_study'] = $this->null_if_blank($this->input->post('field_of_study', TRUE));
			$data['grade_or_classification'] = $this->null_if_blank($this->input->post('grade_or_classification', TRUE));
			$data['started_on'] = $this->null_if_blank($this->input->post('started_on', TRUE));
			$data['completed_on'] = $this->null_if_blank($this->input->post('completed_on', TRUE));
			return $data;
		}

		if ($section === 'certifications') {
			$data['name'] = trim((string) $this->input->post('name', TRUE));
			$data['issuer'] = $this->null_if_blank($this->input->post('issuer', TRUE));
			$data['credential_id'] = $this->null_if_blank($this->input->post('credential_id', TRUE));
			$data['issued_on'] = $this->null_if_blank($this->input->post('issued_on', TRUE));
			$data['expires_on'] = $this->null_if_blank($this->input->post('expires_on', TRUE));
			return $data;
		}

		if ($section === 'licences') {
			$data['title'] = trim((string) $this->input->post('title', TRUE));
			$data['issuing_body'] = $this->null_if_blank($this->input->post('issuing_body', TRUE));
			$data['licence_number'] = $this->null_if_blank($this->input->post('licence_number', TRUE));
			$data['jurisdiction'] = $this->null_if_blank($this->input->post('jurisdiction', TRUE));
			$data['valid_from'] = $this->null_if_blank($this->input->post('valid_from', TRUE));
			$data['valid_to'] = $this->null_if_blank($this->input->post('valid_to', TRUE));
			return $data;
		}

		if ($section === 'courses') {
			$data['title'] = trim((string) $this->input->post('title', TRUE));
			$data['provider'] = $this->null_if_blank($this->input->post('provider', TRUE));
			$data['completed_on'] = $this->null_if_blank($this->input->post('completed_on', TRUE));
			$hours = trim((string) $this->input->post('hours', TRUE));
			$data['hours'] = $hours === '' ? NULL : $hours;
			return $data;
		}

		if ($section === 'employment') {
			$is_current = (int) $this->input->post('is_current', TRUE) === 1;

			$data['employer'] = trim((string) $this->input->post('employer', TRUE));
			$data['job_title'] = trim((string) $this->input->post('job_title', TRUE));
			$data['location'] = $this->null_if_blank($this->input->post('location', TRUE));
			$data['started_on'] = $this->null_if_blank($this->input->post('started_on', TRUE));
			$data['ended_on'] = $is_current ? NULL : $this->null_if_blank($this->input->post('ended_on', TRUE));
			$data['is_current'] = $is_current ? 1 : 0;
			$data['description'] = $this->null_if_blank($this->input->post('description', TRUE));
		}

		return $data;
	}

	private function section_config($section)
	{
		$config = array(
			'degrees' => array(
				'model' => 'degree_model',
				'entity_label' => 'Degree'
			),
			'certifications' => array(
				'model' => 'certification_model',
				'entity_label' => 'Certification'
			),
			'licences' => array(
				'model' => 'licence_model',
				'entity_label' => 'Licence'
			),
			'courses' => array(
				'model' => 'course_model',
				'entity_label' => 'Course'
			),
			'employment' => array(
				'model' => 'employment_model',
				'entity_label' => 'Employment record'
			)
		);

		return $config[$section];
	}

	private function api_key_has_any_scope(array $api_key, array $required_scopes)
	{
		$scope_string = isset($api_key['scopes']) ? (string) $api_key['scopes'] : '';
		$actual_scopes = array_filter(array_map('trim', explode(',', $scope_string)));
		if (empty($required_scopes)) {
			return TRUE;
		}

		foreach ($required_scopes as $required_scope) {
			if (in_array((string) $required_scope, $actual_scopes, TRUE)) {
				return TRUE;
			}
		}

		return FALSE;
	}

	private function null_if_blank($value)
	{
		$value = trim((string) $value);
		return $value === '' ? NULL : $value;
	}

	private function upload_profile_photo($field_name)
	{
		$upload_dir = $this->profile_upload_directory();
		if (!is_dir($upload_dir) && !mkdir($upload_dir, 0755, TRUE)) {
			log_message('error', 'Profile API image upload failed: directory create failed');
			return array('ok' => FALSE, 'error' => 'Upload folder is not writable.');
		}

		$config = array(
			'upload_path' => $upload_dir,
			'allowed_types' => 'jpg|jpeg|png|webp',
			'max_size' => 2048,
			'encrypt_name' => TRUE,
			'file_ext_tolower' => TRUE,
			'remove_spaces' => TRUE,
			'detect_mime' => TRUE
		);

		$this->load->library('upload');
		$this->upload->initialize($config, TRUE);

		if (!$this->upload->do_upload($field_name)) {
			$error = strip_tags((string) $this->upload->display_errors('', ''));
			return array(
				'ok' => FALSE,
				'error' => $error !== '' ? $error : 'Image upload failed.'
			);
		}

		$data = $this->upload->data();
		return array(
			'ok' => TRUE,
			'relative_path' => 'uploads/profile_images/'.$data['file_name']
		);
	}

	private function safe_delete_profile_photo($relative_path)
	{
		$relative_path = trim((string) $relative_path);
		if ($relative_path === '' || strpos($relative_path, 'uploads/profile_images/') !== 0) {
			return;
		}

		$absolute = FCPATH.$relative_path;
		$base_dir = realpath($this->profile_upload_directory());
		$file_real = realpath($absolute);
		if ($base_dir === FALSE || $file_real === FALSE) {
			return;
		}

		$normalized_base = rtrim(str_replace('\\', '/', $base_dir), '/').'/';
		$normalized_file = str_replace('\\', '/', $file_real);
		if (strpos($normalized_file, $normalized_base) !== 0) {
			return;
		}

		if (is_file($file_real)) {
			@unlink($file_real);
		}
	}

	private function profile_upload_directory()
	{
		return rtrim(FCPATH, '/\\').DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.'profile_images'.DIRECTORY_SEPARATOR;
	}

	private function json_response(array $payload, $status_code)
	{
		return $this->output
			->set_content_type('application/json', 'utf-8')
			->set_status_header((int) $status_code)
			->set_output(json_encode($payload, JSON_UNESCAPED_SLASHES));
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
}

<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Alumni profile management:
 * - Step 5: basic profile
 * - Step 6: repeatable section CRUD
 *
 * @property CI_Input $input
 * @property CI_Session $session
 * @property CI_Form_validation $form_validation
 * @property Profile_model $profile_model
 * @property Degree_model $degree_model
 * @property Certification_model $certification_model
 * @property Licence_model $licence_model
 * @property Course_model $course_model
 * @property Employment_model $employment_model
 */
class Profile extends MY_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Profile_model', 'profile_model');
		$this->load->model('Degree_model', 'degree_model');
		$this->load->model('Certification_model', 'certification_model');
		$this->load->model('Licence_model', 'licence_model');
		$this->load->model('Course_model', 'course_model');
		$this->load->model('Employment_model', 'employment_model');
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

	public function degrees()
	{
		$this->render_section_page('degrees');
	}

	public function add_degree()
	{
		$this->save_section_item('degrees');
	}

	public function edit_degree($id)
	{
		$this->render_section_page('degrees', (int) $id);
	}

	public function update_degree($id)
	{
		$this->update_section_item('degrees', (int) $id);
	}

	public function delete_degree($id)
	{
		$this->delete_section_item('degrees', (int) $id);
	}

	public function certifications()
	{
		$this->render_section_page('certifications');
	}

	public function add_certification()
	{
		$this->save_section_item('certifications');
	}

	public function edit_certification($id)
	{
		$this->render_section_page('certifications', (int) $id);
	}

	public function update_certification($id)
	{
		$this->update_section_item('certifications', (int) $id);
	}

	public function delete_certification($id)
	{
		$this->delete_section_item('certifications', (int) $id);
	}

	public function licences()
	{
		$this->render_section_page('licences');
	}

	public function add_licence()
	{
		$this->save_section_item('licences');
	}

	public function edit_licence($id)
	{
		$this->render_section_page('licences', (int) $id);
	}

	public function update_licence($id)
	{
		$this->update_section_item('licences', (int) $id);
	}

	public function delete_licence($id)
	{
		$this->delete_section_item('licences', (int) $id);
	}

	public function courses()
	{
		$this->render_section_page('courses');
	}

	public function add_course()
	{
		$this->save_section_item('courses');
	}

	public function edit_course($id)
	{
		$this->render_section_page('courses', (int) $id);
	}

	public function update_course($id)
	{
		$this->update_section_item('courses', (int) $id);
	}

	public function delete_course($id)
	{
		$this->delete_section_item('courses', (int) $id);
	}

	public function employment()
	{
		$this->render_section_page('employment');
	}

	public function add_employment()
	{
		$this->save_section_item('employment');
	}

	public function edit_employment($id)
	{
		$this->render_section_page('employment', (int) $id);
	}

	public function update_employment($id)
	{
		$this->update_section_item('employment', (int) $id);
	}

	public function delete_employment($id)
	{
		$this->delete_section_item('employment', (int) $id);
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

	private function render_section_page($section, $edit_id = NULL)
	{
		$profile = $this->require_profile_for_sections();
		$config = $this->section_config($section);
		$model = $this->{$config['model']};

		$items = $model->list_by_profile_id((int) $profile['id']);
		$edit_item = NULL;

		if ($edit_id !== NULL) {
			$edit_item = $model->get_by_id_and_profile((int) $edit_id, (int) $profile['id']);
			if (!$edit_item) {
				$this->session->set_flashdata('section_error', 'Record not found or access denied.');
				redirect($config['list_route']);
				return;
			}
		}

		$data = array(
			'page_title' => $config['title'],
			'items' => $items,
			'edit_item' => $edit_item,
			'config' => $config
		);

		$this->render($config['view'], $data);
	}

	private function save_section_item($section)
	{
		$profile = $this->require_profile_for_sections();
		$config = $this->section_config($section);

		if (strtoupper($this->input->method()) !== 'POST') {
			redirect($config['list_route']);
			return;
		}

		$this->apply_section_validation($section);
		if (!$this->form_validation->run() || !$this->validate_section_date_ranges($section)) {
			$this->session->set_flashdata('section_error', validation_errors('<p style="margin:4px 0;">', '</p>') ?: 'Please fix the highlighted errors.');
			redirect($config['list_route']);
			return;
		}

		$payload = $this->section_payload($section, (int) $profile['id']);
		$id = $this->{$config['model']}->create($payload);

		if (!$id) {
			log_message('error', 'Section create failed: '.$section.' profile_id='.$profile['id']);
			$this->session->set_flashdata('section_error', 'Could not save record right now.');
			redirect($config['list_route']);
			return;
		}

		log_message('info', 'Section create success: '.$section.' profile_id='.$profile['id'].' id='.$id);
		$this->session->set_flashdata('section_success', $config['entity_label'].' added.');
		redirect($config['list_route']);
	}

	private function update_section_item($section, $id)
	{
		$profile = $this->require_profile_for_sections();
		$config = $this->section_config($section);
		$model = $this->{$config['model']};

		if (strtoupper($this->input->method()) !== 'POST') {
			redirect($config['list_route']);
			return;
		}

		$existing = $model->get_by_id_and_profile((int) $id, (int) $profile['id']);
		if (!$existing) {
			$this->session->set_flashdata('section_error', 'Record not found or access denied.');
			redirect($config['list_route']);
			return;
		}

		$this->apply_section_validation($section);
		if (!$this->form_validation->run() || !$this->validate_section_date_ranges($section)) {
			$this->session->set_flashdata('section_error', validation_errors('<p style="margin:4px 0;">', '</p>') ?: 'Please fix the highlighted errors.');
			redirect($config['edit_route_prefix'].'/'.(int) $id);
			return;
		}

		$payload = $this->section_payload($section, (int) $profile['id'], FALSE);
		$ok = $model->update((int) $id, $payload);

		if (!$ok) {
			log_message('error', 'Section update failed: '.$section.' profile_id='.$profile['id'].' id='.(int) $id);
			$this->session->set_flashdata('section_error', 'Could not update record right now.');
			redirect($config['edit_route_prefix'].'/'.(int) $id);
			return;
		}

		log_message('info', 'Section update success: '.$section.' profile_id='.$profile['id'].' id='.(int) $id);
		$this->session->set_flashdata('section_success', $config['entity_label'].' updated.');
		redirect($config['list_route']);
	}

	private function delete_section_item($section, $id)
	{
		$profile = $this->require_profile_for_sections();
		$config = $this->section_config($section);
		$model = $this->{$config['model']};

		if (strtoupper($this->input->method()) !== 'POST') {
			redirect($config['list_route']);
			return;
		}

		$existing = $model->get_by_id_and_profile((int) $id, (int) $profile['id']);
		if (!$existing) {
			$this->session->set_flashdata('section_error', 'Record not found or access denied.');
			redirect($config['list_route']);
			return;
		}

		$ok = $model->delete((int) $id);
		if (!$ok) {
			log_message('error', 'Section delete failed: '.$section.' profile_id='.$profile['id'].' id='.(int) $id);
			$this->session->set_flashdata('section_error', 'Could not delete record right now.');
			redirect($config['list_route']);
			return;
		}

		log_message('info', 'Section delete success: '.$section.' profile_id='.$profile['id'].' id='.(int) $id);
		$this->session->set_flashdata('section_success', $config['entity_label'].' deleted.');
		redirect($config['list_route']);
	}

	private function apply_section_validation($section)
	{
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

		return $this->date_not_before($start, $end);
	}

	private function date_not_before($from, $to)
	{
		$from_dt = DateTime::createFromFormat('Y-m-d', $from);
		$to_dt = DateTime::createFromFormat('Y-m-d', $to);

		if (!$from_dt || !$to_dt) {
			return TRUE;
		}

		if ($to_dt < $from_dt) {
			$this->form_validation->set_message('_valid_optional_date', 'End/expiry date cannot be before start/issued date.');
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
				'title' => 'Degrees',
				'view' => 'profile/degrees',
				'model' => 'degree_model',
				'entity_label' => 'Degree',
				'list_route' => 'profile/degrees',
				'edit_route_prefix' => 'profile/degrees/edit'
			),
			'certifications' => array(
				'title' => 'Certifications',
				'view' => 'profile/certifications',
				'model' => 'certification_model',
				'entity_label' => 'Certification',
				'list_route' => 'profile/certifications',
				'edit_route_prefix' => 'profile/certifications/edit'
			),
			'licences' => array(
				'title' => 'Licences',
				'view' => 'profile/licences',
				'model' => 'licence_model',
				'entity_label' => 'Licence',
				'list_route' => 'profile/licences',
				'edit_route_prefix' => 'profile/licences/edit'
			),
			'courses' => array(
				'title' => 'Short Courses',
				'view' => 'profile/courses',
				'model' => 'course_model',
				'entity_label' => 'Course',
				'list_route' => 'profile/courses',
				'edit_route_prefix' => 'profile/courses/edit'
			),
			'employment' => array(
				'title' => 'Employment History',
				'view' => 'profile/employment',
				'model' => 'employment_model',
				'entity_label' => 'Employment record',
				'list_route' => 'profile/employment',
				'edit_route_prefix' => 'profile/employment/edit'
			)
		);

		return $config[$section];
	}

	private function require_profile_for_sections()
	{
		$user = $this->require_verified_user();
		$profile = $this->profile_model->get_by_user_id((int) $user['id']);

		if (!$profile) {
			$this->session->set_flashdata('profile_error', 'Please complete your basic profile first.');
			redirect('profile/basic');
			exit;
		}

		return $profile;
	}

	private function null_if_blank($value)
	{
		$value = trim((string) $value);
		return $value === '' ? NULL : $value;
	}
}

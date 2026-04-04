<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Admin API key + usage log management.
 */
class Admin extends MY_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->helper(array('url', 'form'));
		$this->load->library('form_validation');
		$this->load->model('ApiKey_model', 'api_key_model');
		$this->load->model('UsageLog_model', 'usage_log_model');
		$this->load->model('User_model', 'user_model');
	}

	public function api_keys()
	{
		$admin = $this->require_admin_user();
		$keys = $this->api_key_model->list_all(TRUE, 300);

		log_message('info', 'Admin viewed API keys list: user_id='.(int) $admin['id'].' count='.count($keys));

		$data = array(
			'page_title' => 'Admin - API Keys',
			'keys' => $keys,
			'raw_token_once' => (string) $this->session->flashdata('created_api_token')
		);
		$this->render('admin/api_keys', $data);
	}

	public function create_api_key()
	{
		$admin = $this->require_admin_user();
		if (strtoupper($this->input->method()) !== 'POST') {
			redirect('admin/api_keys');
			return;
		}

		$this->form_validation->set_rules('name', 'Key Name', 'trim|required|min_length[3]|max_length[100]');
		$this->form_validation->set_rules('scopes', 'Scopes', 'trim|max_length[512]|regex_match[/^[a-zA-Z0-9_,:\\-\\s]*$/]');
		$this->form_validation->set_rules('expires_at', 'Expires At', 'trim|max_length[19]');
		$this->form_validation->set_rules('user_id', 'User ID', 'trim|integer');

		if ($this->form_validation->run() === FALSE) {
			$this->session->set_flashdata('admin_error', validation_errors('<p style="margin:4px 0;">', '</p>'));
			redirect('admin/api_keys');
			return;
		}

		$target_user_id = (int) $this->input->post('user_id', TRUE);
		if ($target_user_id <= 0) {
			$target_user_id = (int) $admin['id'];
		}

		$target_user = $this->user_model->find_by_id($target_user_id);
		if (!$target_user) {
			log_message('error', 'API key create blocked: unknown target user_id='.$target_user_id.' admin_id='.(int) $admin['id']);
			$this->session->set_flashdata('admin_error', 'Target user does not exist.');
			redirect('admin/api_keys');
			return;
		}

		$expires_at_raw = trim((string) $this->input->post('expires_at', TRUE));
		$expires_at = NULL;
		if ($expires_at_raw !== '') {
			$dt = DateTime::createFromFormat('Y-m-d H:i:s', $expires_at_raw);
			if (!$dt || $dt->format('Y-m-d H:i:s') !== $expires_at_raw) {
				$this->session->set_flashdata('admin_error', 'Invalid expires_at format. Use YYYY-MM-DD HH:MM:SS.');
				redirect('admin/api_keys');
				return;
			}
			$expires_at = $dt->format('Y-m-d H:i:s');
		}

		try {
			$raw_token = bin2hex(random_bytes(32));
		} catch (Exception $ex) {
			log_message('error', 'API key generation failed: '.$ex->getMessage());
			$this->session->set_flashdata('admin_error', 'Could not generate API token at this time.');
			redirect('admin/api_keys');
			return;
		}

		$key_hash = hash('sha256', $raw_token);
		$key_prefix = substr($raw_token, 0, 12);
		$key_id = $this->api_key_model->create_for_user($target_user_id, array(
			'name' => (string) $this->input->post('name', TRUE),
			'key_prefix' => $key_prefix,
			'key_hash' => $key_hash,
			'scopes' => trim((string) $this->input->post('scopes', TRUE)),
			'expires_at' => $expires_at
		));

		if (!$key_id) {
			log_message('error', 'API key create failed: admin_id='.(int) $admin['id'].' target_user_id='.$target_user_id);
			$this->session->set_flashdata('admin_error', 'Failed to create API key.');
			redirect('admin/api_keys');
			return;
		}

		log_message(
			'info',
			'API key created: admin_id='.(int) $admin['id'].
			' key_id='.(int) $key_id.
			' target_user_id='.$target_user_id.
			' prefix='.$key_prefix
		);

		$this->session->set_flashdata('admin_success', 'API key created successfully. Copy the token now.');
		$this->session->set_flashdata('created_api_token', $raw_token);
		redirect('admin/api_keys');
	}

	public function revoke_api_key($id = NULL)
	{
		$admin = $this->require_admin_user();
		if (strtoupper($this->input->method()) !== 'POST') {
			show_error('Method Not Allowed', 405);
		}

		$key_id = (int) $id;
		if ($key_id <= 0) {
			$this->session->set_flashdata('admin_error', 'Invalid API key ID.');
			redirect('admin/api_keys');
			return;
		}

		$key = $this->api_key_model->get_by_id($key_id);
		if (!$key) {
			$this->session->set_flashdata('admin_error', 'API key not found.');
			redirect('admin/api_keys');
			return;
		}

		if ((int) $key['is_revoked'] === 1) {
			$this->session->set_flashdata('admin_success', 'API key is already revoked.');
			redirect('admin/api_keys');
			return;
		}

		$reason = trim((string) $this->input->post('reason', TRUE));
		$ok = $this->api_key_model->revoke($key_id, $reason !== '' ? $reason : 'Revoked by admin');
		if (!$ok) {
			$this->session->set_flashdata('admin_error', 'Failed to revoke API key.');
			redirect('admin/api_keys');
			return;
		}

		log_message('info', 'API key revoked: admin_id='.(int) $admin['id'].' key_id='.$key_id);
		$this->session->set_flashdata('admin_success', 'API key revoked.');
		redirect('admin/api_keys');
	}

	public function usage_logs()
	{
		$admin = $this->require_admin_user();
		$api_key_id = (int) $this->input->get('api_key_id', TRUE);

		if ($api_key_id > 0) {
			$logs = $this->usage_log_model->list_by_api_key($api_key_id, 300, 0);
			$total_for_key = $this->usage_log_model->count_by_api_key($api_key_id);
		} else {
			$logs = $this->usage_log_model->list_recent(300);
			$total_for_key = NULL;
		}

		log_message(
			'info',
			'Admin viewed usage logs: user_id='.(int) $admin['id'].
			' filter_api_key_id='.$api_key_id.
			' count='.count($logs)
		);

		$data = array(
			'page_title' => 'Admin - API Usage Logs',
			'logs' => $logs,
			'filter_api_key_id' => $api_key_id,
			'total_for_key' => $total_for_key
		);
		$this->render('admin/usage_logs', $data);
	}

	private function require_admin_user()
	{
		$user = $this->require_verified_user();
		if (!isset($user['role']) || (string) $user['role'] !== 'admin') {
			log_message('error', 'Admin access denied: user_id='.(int) $user['id']);
			show_error('Forbidden', 403);
		}

		return $user;
	}
}

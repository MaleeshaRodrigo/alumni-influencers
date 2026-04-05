<?php
defined('BASEPATH') OR exit('No direct script access allowed');

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

return $this->json_response(array(
'ok' => TRUE,
'message' => 'API keys fetched successfully.',
'data' => array('keys' => $keys)
), 200);
}

public function create_api_key()
{
$admin = $this->require_admin_user();
if (strtoupper($this->input->method()) !== 'POST') {
return $this->json_response(array('ok' => FALSE, 'message' => 'Method not allowed. Use POST.', 'data' => NULL), 405);
}

$this->form_validation->set_rules('name', 'Key Name', 'trim|required|min_length[3]|max_length[100]');
$this->form_validation->set_rules('scopes', 'Scopes', 'trim|max_length[512]|regex_match[/^[a-zA-Z0-9_,:\-\s]*$/]');
$this->form_validation->set_rules('expires_at', 'Expires At', 'trim|max_length[19]');
$this->form_validation->set_rules('user_id', 'User ID', 'trim|integer');

if ($this->form_validation->run() === FALSE) {
return $this->json_response(array('ok' => FALSE, 'message' => 'Validation failed.', 'errors' => $this->form_validation->error_array(), 'data' => NULL), 400);
}

$target_user_id = (int) $this->input->post('user_id', TRUE);
if ($target_user_id <= 0) {
$target_user_id = (int) $admin['id'];
}

$target_user = $this->user_model->find_by_id($target_user_id);
if (!$target_user) {
return $this->json_response(array('ok' => FALSE, 'message' => 'Target user does not exist.', 'data' => NULL), 404);
}

$expires_at_raw = trim((string) $this->input->post('expires_at', TRUE));
$expires_at = NULL;
if ($expires_at_raw !== '') {
$dt = DateTime::createFromFormat('Y-m-d H:i:s', $expires_at_raw);
if (!$dt || $dt->format('Y-m-d H:i:s') !== $expires_at_raw) {
return $this->json_response(array('ok' => FALSE, 'message' => 'Invalid expires_at format. Use YYYY-MM-DD HH:MM:SS.', 'data' => NULL), 400);
}
$expires_at = $dt->format('Y-m-d H:i:s');
}

try {
$raw_token = bin2hex(random_bytes(32));
} catch (Exception $ex) {
return $this->json_response(array('ok' => FALSE, 'message' => 'Could not generate API token at this time.', 'data' => NULL), 500);
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
return $this->json_response(array('ok' => FALSE, 'message' => 'Failed to create API key.', 'data' => NULL), 500);
}

return $this->json_response(array(
'ok' => TRUE,
'message' => 'API key created successfully.',
'data' => array('id' => (int) $key_id, 'token' => (string) $raw_token, 'prefix' => (string) $key_prefix)
), 201);
}

public function revoke_api_key($id = NULL)
{
$admin = $this->require_admin_user();
if (strtoupper($this->input->method()) !== 'POST') {
return $this->json_response(array('ok' => FALSE, 'message' => 'Method not allowed. Use POST.', 'data' => NULL), 405);
}

$key_id = (int) $id;
if ($key_id <= 0) {
return $this->json_response(array('ok' => FALSE, 'message' => 'Invalid API key ID.', 'data' => NULL), 400);
}

$key = $this->api_key_model->get_by_id($key_id);
if (!$key) {
return $this->json_response(array('ok' => FALSE, 'message' => 'API key not found.', 'data' => NULL), 404);
}

if ((int) $key['is_revoked'] === 1) {
return $this->json_response(array('ok' => TRUE, 'message' => 'API key is already revoked.', 'data' => array('id' => $key_id)), 200);
}

$reason = trim((string) $this->input->post('reason', TRUE));
$ok = $this->api_key_model->revoke($key_id, $reason !== '' ? $reason : 'Revoked by admin');
if (!$ok) {
return $this->json_response(array('ok' => FALSE, 'message' => 'Failed to revoke API key.', 'data' => NULL), 500);
}

return $this->json_response(array('ok' => TRUE, 'message' => 'API key revoked.', 'data' => array('id' => $key_id)), 200);
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

return $this->json_response(array(
'ok' => TRUE,
'message' => 'Usage logs fetched successfully.',
'data' => array('filter_api_key_id' => $api_key_id, 'total_for_key' => $total_for_key, 'logs' => $logs)
), 200);
}

private function require_admin_user()
{
$user = $this->require_verified_user();
if (!isset($user['role']) || (string) $user['role'] !== 'admin') {
show_error('Forbidden', 403);
}

return $user;
}

private function json_response(array $payload, $status_code)
{
return $this->output
->set_content_type('application/json', 'utf-8')
->set_status_header((int) $status_code)
->set_output(json_encode($payload, JSON_UNESCAPED_SLASHES));
}
}

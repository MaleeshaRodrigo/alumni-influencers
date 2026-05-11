<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MY_Controller extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function require_verified_user()
    {
        $user_id = (int) $this->session->userdata('auth_user_id');
        $is_authenticated = (bool) $this->session->userdata('is_authenticated');

        if (!$is_authenticated || $user_id <= 0) {
            show_error('Unauthorized', 401);
        }

        $this->load->model('User_model', 'user_model');
        $user = $this->user_model->find_by_id($user_id);

        if (!$user || (string) $user['status'] !== 'active' || empty($user['email_verified_at'])) {
            $this->session->unset_userdata(array('auth_user_id', 'auth_role', 'is_authenticated', 'logged_in_at'));
            show_error('Forbidden', 403);
        }

        return $user;
    }
}

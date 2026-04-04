<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * One profile row per user (public-facing CV shell).
 */
class Profile_model extends CI_Model {

	protected $table = 'profiles';

	public function __construct()
	{
		parent::__construct();
		$this->load->database();
	}

	public function get_by_id($profile_id)
	{
	}

	public function get_by_user_id($user_id)
	{
	}

	public function create(array $data)
	{
	}

	public function update($profile_id, array $data)
	{
	}

	public function delete($profile_id)
	{
	}

	public function set_visibility($profile_id, $is_public)
	{
	}
}

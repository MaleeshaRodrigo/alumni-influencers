<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Short courses (short_courses table).
 */
class Course_model extends CI_Model {

	protected $table = 'short_courses';

	public function __construct()
	{
		parent::__construct();
		$this->load->database();
	}

	public function list_by_profile_id($profile_id)
	{
	}

	public function get_by_id($id)
	{
	}

	public function create(array $data)
	{
	}

	public function update($id, array $data)
	{
	}

	public function delete($id)
	{
	}

	public function reorder_for_profile($profile_id, array $ordered_ids)
	{
	}
}

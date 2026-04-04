<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Featured alumni placements (featured_alumni table).
 */
class Feature_model extends CI_Model {

	protected $table = 'featured_alumni';

	public function __construct()
	{
		parent::__construct();
		$this->load->database();
	}

	public function get_by_id($id)
	{
	}

	public function list_active(?DateTime $at = NULL)
	{
	}

	public function list_for_profile($profile_id)
	{
	}

	public function create(array $data)
	{
	}

	public function update($id, array $data)
	{
	}

	public function deactivate($id)
	{
	}

	public function link_winning_bid($feature_id, $bid_id)
	{
	}
}

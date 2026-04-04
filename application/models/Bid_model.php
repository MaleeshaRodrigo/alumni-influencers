<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Blind bidding: sealed amounts per user per cycle (see bids table).
 */
class Bid_model extends CI_Model {

	protected $table = 'bids';

	public function __construct()
	{
		parent::__construct();
		$this->load->database();
	}

	public function get_by_id($id)
	{
	}

	public function get_by_user_and_cycle($user_id, $cycle_id)
	{
	}

	public function create_draft($user_id, $cycle_id, array $data = array())
	{
	}

	public function submit($id, array $data = array())
	{
	}

	public function withdraw($id, $user_id)
	{
	}

	public function list_for_cycle($cycle_id, $include_amounts = FALSE)
	{
	}

	public function list_winners_for_cycle($cycle_id)
	{
	}

	public function update_status($id, $status, array $extra = array())
	{
	}

	public function mark_revealed($id, ?DateTime $at = NULL)
	{
	}

	public function attach_admin_note($id, $note)
	{
	}
}

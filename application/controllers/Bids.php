<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Blind bidding core flow (Step 8).
 *
 * @property CI_Input $input
 * @property CI_Session $session
 * @property CI_Form_validation $form_validation
 * @property Bid_model $bid_model
 */
class Bids extends MY_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Bid_model', 'bid_model');
		$this->load->helper(array('form', 'url'));
		$this->load->library('form_validation');
	}

	public function index()
	{
		$this->place();
	}

	public function place()
	{
		$user = $this->require_verified_user();
		$cycle_id = $this->bid_model->current_cycle_id();
		$current_bid = $this->bid_model->get_by_user_and_cycle((int) $user['id'], $cycle_id);
		$eligibility = $this->bid_model->monthly_eligibility_for_user((int) $user['id']);

		$data = array(
			'page_title' => 'Place Blind Bid',
			'cycle_id' => $cycle_id,
			'current_bid' => $current_bid,
			'eligibility' => $eligibility
		);

		$this->render('bids/place_bid', $data);
	}

	public function store()
	{
		$user = $this->require_verified_user();
		if (strtoupper($this->input->method()) !== 'POST') {
			redirect('bids/place');
			return;
		}

		$this->form_validation->set_rules('bid_amount', 'Bid Amount', 'trim|required|regex_match[/^[0-9]+(?:\\.[0-9]{1,2})?$/]');
		if ($this->form_validation->run() === FALSE) {
			$this->session->set_flashdata('bid_error', validation_errors('<p style="margin:4px 0;">', '</p>'));
			redirect('bids/place');
			return;
		}

		$amount = (float) $this->input->post('bid_amount', TRUE);
		if ($amount <= 0) {
			$this->session->set_flashdata('bid_error', 'Bid amount must be greater than zero.');
			redirect('bids/place');
			return;
		}

		$cycle_id = $this->bid_model->current_cycle_id();
		$result = $this->bid_model->place_or_increase_for_cycle((int) $user['id'], $cycle_id, $amount, 'GBP');

		if (!$result['ok']) {
			if (isset($result['eligibility'])) {
				log_message(
					'info',
					'Bid blocked by monthly eligibility: user_id='.(int) $user['id'].
					' wins='.(int) $result['eligibility']['wins_this_month'].
					' max='.(int) $result['eligibility']['max_slots'].
					' remaining='.(int) $result['eligibility']['remaining_slots'].
					' bonus='.(int) $result['eligibility']['has_event_bonus']
				);
			}
			log_message('error', 'Bid action failed: user_id='.(int) $user['id'].' cycle_id='.$cycle_id.' reason='.$result['error']);
			$this->session->set_flashdata('bid_error', $result['error']);
			redirect('bids/place');
			return;
		}

		log_message(
			'info',
			'Bid '.$result['action'].' by user_id='.(int) $user['id'].' cycle_id='.$cycle_id.' bid_id='.(int) $result['bid_id']
		);

		$this->session->set_flashdata('bid_success', 'Your bid has been recorded.');
		redirect('bids/status');
	}

	public function status()
	{
		$user = $this->require_verified_user();
		$cycle_id = $this->bid_model->current_cycle_id();
		$status = $this->bid_model->blind_status_for_user_cycle((int) $user['id'], $cycle_id);
		$eligibility = $this->bid_model->monthly_eligibility_for_user((int) $user['id']);

		$data = array(
			'page_title' => 'Bid Status',
			'cycle_id' => $cycle_id,
			'bid_status' => $status,
			'eligibility' => $eligibility
		);

		$this->render('bids/bid_status', $data);
	}

	public function history()
	{
		$user = $this->require_verified_user();
		$history = $this->bid_model->history_for_user((int) $user['id'], 50);
		$eligibility = $this->bid_model->monthly_eligibility_for_user((int) $user['id']);

		log_message(
			'info',
			'Bid history viewed: user_id='.(int) $user['id'].
			' wins='.(int) $eligibility['wins_this_month'].
			' max='.(int) $eligibility['max_slots'].
			' remaining='.(int) $eligibility['remaining_slots'].
			' bonus='.(int) $eligibility['has_event_bonus']
		);

		$data = array(
			'page_title' => 'Bid History',
			'history' => $history,
			'eligibility' => $eligibility
		);

		$this->render('bids/history', $data);
	}
}

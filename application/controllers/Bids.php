<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Blind bidding core flow + automated winner selection.
 *
 * @property CI_Input $input
 * @property CI_Output $output
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
		return $this->status();
	}

	public function place()
	{
		$user = $this->require_verified_user();
		$cycle_id = $this->bid_model->current_cycle_id();
		$current_bid = $this->bid_model->get_by_user_and_cycle((int) $user['id'], $cycle_id);
		$eligibility = $this->bid_model->monthly_eligibility_for_user((int) $user['id']);

		return $this->json_response(array(
			'ok' => TRUE,
			'message' => 'Bid placement context fetched successfully.',
			'data' => array(
				'cycle_id' => $cycle_id,
				'current_bid' => $current_bid,
				'eligibility' => $eligibility
			)
		), 200);
	}

	public function store()
	{
		$user = $this->require_verified_user();
		if (strtoupper($this->input->method()) !== 'POST') {
			return $this->json_response(array(
				'ok' => FALSE,
				'message' => 'Method not allowed. Use POST.',
				'data' => NULL
			), 405);
		}

		$this->form_validation->set_rules('bid_amount', 'Bid Amount', 'trim|required|regex_match[/^[0-9]+(?:\\.[0-9]{1,2})?$/]');
		if ($this->form_validation->run() === FALSE) {
			return $this->json_response(array(
				'ok' => FALSE,
				'message' => 'Validation failed.',
				'errors' => $this->form_validation->error_array(),
				'data' => NULL
			), 400);
		}

		$amount = (float) $this->input->post('bid_amount', TRUE);
		if ($amount <= 0) {
			return $this->json_response(array(
				'ok' => FALSE,
				'message' => 'Bid amount must be greater than zero.',
				'data' => NULL
			), 400);
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
			return $this->json_response(array(
				'ok' => FALSE,
				'message' => (string) $result['error'],
				'data' => NULL
			), 400);
		}

		log_message(
			'info',
			'Bid '.$result['action'].' by user_id='.(int) $user['id'].' cycle_id='.$cycle_id.' bid_id='.(int) $result['bid_id']
		);

		return $this->json_response(array(
			'ok' => TRUE,
			'message' => 'Your bid has been recorded.',
			'data' => array(
				'action' => (string) $result['action'],
				'bid_id' => (int) $result['bid_id'],
				'cycle_id' => $cycle_id
			)
		), 200);
	}

	public function status()
	{
		$user = $this->require_verified_user();
		$cycle_id = $this->bid_model->current_cycle_id();
		$status = $this->bid_model->blind_status_for_user_cycle((int) $user['id'], $cycle_id);
		$eligibility = $this->bid_model->monthly_eligibility_for_user((int) $user['id']);

		return $this->json_response(array(
			'ok' => TRUE,
			'message' => 'Bid status fetched successfully.',
			'data' => array(
				'cycle_id' => $cycle_id,
				'bid_status' => $status,
				'eligibility' => $eligibility
			)
		), 200);
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

		return $this->json_response(array(
			'ok' => TRUE,
			'message' => 'Bid history fetched successfully.',
			'data' => array(
				'history' => $history,
				'eligibility' => $eligibility
			)
		), 200);
	}

	public function run_daily_winner($cycle_id = NULL)
	{
		$auth = $this->authorize_winner_run();
		if ($cycle_id === NULL) {
			$cycle_id = (int) $this->input->get('cycle_id', TRUE);
		} else {
			$cycle_id = (int) $cycle_id;
		}
		if ($cycle_id <= 0) {
			$cycle_id = $this->bid_model->current_cycle_id();
		}

		$result = $this->bid_model->run_daily_winner($cycle_id, $auth);
		log_message(
			'info',
			'run_daily_winner executed: cycle_id='.$cycle_id.
			' status='.$result['status'].
			' trigger='.$auth['trigger']
		);

		if (is_cli()) {
			echo json_encode($result, JSON_PRETTY_PRINT).PHP_EOL;
			return;
		}

		$this->output
			->set_content_type('application/json')
			->set_output(json_encode($result));
	}

	private function authorize_winner_run()
	{
		if (is_cli()) {
			return array(
				'trigger' => 'cli',
				'actor_user_id' => 0
			);
		}

		$expected_key = getenv('BIDS_ADMIN_KEY');
		if ($expected_key === FALSE || $expected_key === '') {
			log_message('error', 'run_daily_winner forbidden: missing BIDS_ADMIN_KEY for web trigger');
			show_error('Forbidden', 403);
		}

		$provided = (string) $this->input->get('key', TRUE);
		if (!hash_equals((string) $expected_key, $provided)) {
			log_message('error', 'run_daily_winner forbidden: invalid admin key');
			show_error('Forbidden', 403);
		}

		return array(
			'trigger' => 'web_key',
			'actor_user_id' => 0
		);
	}

	private function json_response(array $payload, $status_code)
	{
		return $this->output
			->set_content_type('application/json', 'utf-8')
			->set_status_header((int) $status_code)
			->set_output(json_encode($payload, JSON_UNESCAPED_SLASHES));
	}
}

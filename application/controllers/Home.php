<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @property CI_Output $output
 */
class Home extends MY_Controller
{
	public function __construct()
	{
		parent::__construct();
	}

	public function index()
	{
		$data = array(
			'page_title' => 'Home',
			'app_name' => 'Alumni Influencers',
			'status_message' => 'Alumni Influencers is ready.'
		);

		$this->render('home/index', $data);
	}

	public function ping()
	{
		$this->output
			->set_content_type('application/json')
			->set_output(json_encode(array('status' => 'ok')));
	}
}

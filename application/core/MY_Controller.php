<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Controller extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
	}

	protected function render($view, $data = array())
	{
		$layout_data = array(
			'page_title' => isset($data['page_title']) ? $data['page_title'] : 'Alumni Influencers'
		);

		$this->load->view('layouts/header', $layout_data);
		$this->load->view($view, $data);
		$this->load->view('layouts/footer');
	}
}

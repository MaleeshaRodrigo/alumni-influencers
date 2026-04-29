<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->helper('url');
		$this->load->library('session');
	}

	public function index()
	{
		$this->load->view('dashboard/main');
	}

	public function graphs()
	{
		$this->load->view('dashboard/graphs');
	}

	public function alumni()
	{
		$this->load->view('dashboard/alumni');
	}

	public function security()
	{
		$this->load->view('dashboard/security');
	}

	public function login()
	{
		$this->load->view('dashboard/login');
	}

	public function register()
	{
		$this->load->view('dashboard/register');
	}
}

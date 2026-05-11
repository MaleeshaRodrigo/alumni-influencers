<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Serves Swagger UI and OpenAPI spec for coursework.
 */
class Apidocs extends CI_Controller
{
	public function index()
	{
		$this->load->view('docs/swagger_ui');
	}

	public function openapi()
	{
		$spec_path = APPPATH.'../docs/openapi.yaml';
		if (!is_file($spec_path)) {
			show_404();
			return;
		}

		$contents = @file_get_contents($spec_path);
		if ($contents === FALSE) {
			show_error('Could not read OpenAPI spec file.', 500);
			return;
		}

		$this->output
			->set_content_type('text/yaml', 'utf-8')
			->set_output($contents);
	}
}

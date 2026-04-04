<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$config['rate_limits'] = array(
	'auth_register' => array('limit' => 6, 'window_seconds' => 900),
	'auth_login_ip' => array('limit' => 20, 'window_seconds' => 900),
	'auth_login_identity' => array('limit' => 10, 'window_seconds' => 900),
	'auth_reset_request_ip' => array('limit' => 12, 'window_seconds' => 900),
	'auth_reset_request_identity' => array('limit' => 6, 'window_seconds' => 900),
	'public_api_featured_today' => array('limit' => 120, 'window_seconds' => 60)
);

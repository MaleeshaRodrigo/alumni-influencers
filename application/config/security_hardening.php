<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$config['rate_limits'] = array(
	'auth_register' => array('limit' => 6, 'window_seconds' => 900),
	'auth_login_ip' => array('limit' => 20, 'window_seconds' => 900),
	'auth_login_identity' => array('limit' => 10, 'window_seconds' => 900),
	'auth_reset_request_ip' => array('limit' => 12, 'window_seconds' => 900),
	'auth_reset_request_identity' => array('limit' => 6, 'window_seconds' => 900),
	'public_api_featured_today' => array('limit' => 120, 'window_seconds' => 60),
	'profile_api_read' => array('limit' => 180, 'window_seconds' => 60),
	'profile_api_write' => array('limit' => 90, 'window_seconds' => 60),
	'analytics_api' => array('limit' => 60, 'window_seconds' => 60)
);

$config['security_headers'] = array(
	'X-Frame-Options' => 'SAMEORIGIN',
	'X-Content-Type-Options' => 'nosniff',
	'X-XSS-Protection' => '1; mode=block',
	'Content-Security-Policy' => "default-src 'self' https://cdn.jsdelivr.net; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; font-src 'self' https://cdn.jsdelivr.net;",
	'Referrer-Policy' => 'strict-origin-when-cross-origin'
);

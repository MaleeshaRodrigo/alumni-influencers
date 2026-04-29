<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Registration & Verification Config
|--------------------------------------------------------------------------
*/
$config['allowed_email_domains'] = array(
	'my.westminster.ac.uk',
	'westminster.ac.uk',
	'iit.ac.lk'
);

$config['password_min_length'] = 12;
$config['verification_token_ttl_seconds'] = 86400; // 24 hours

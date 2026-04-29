<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class SecurityHeadersHook
{
	public function apply()
	{
		if (is_cli()) {
			return;
		}

		$ci =& get_instance();
		$ci->output->set_header('X-Content-Type-Options: nosniff');
		$ci->output->set_header('X-Frame-Options: SAMEORIGIN');
		$ci->output->set_header('Referrer-Policy: strict-origin-when-cross-origin');
		$ci->output->set_header('Permissions-Policy: geolocation=(), microphone=(), camera=()');

		if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
			$ci->output->set_header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
		}
	}
}

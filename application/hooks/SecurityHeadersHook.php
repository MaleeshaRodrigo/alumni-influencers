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
		$ci->config->load('security_hardening', TRUE);
		$headers = $ci->config->item('security_headers', 'security_hardening');

		if (is_array($headers)) {
			foreach ($headers as $name => $value) {
				$ci->output->set_header($name . ': ' . $value);
			}
		}

		if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
			$ci->output->set_header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
		}
	}
}

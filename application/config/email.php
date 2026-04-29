<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Email Configuration (stub-ready)
|--------------------------------------------------------------------------
| Keeps structure ready for SMTP while still allowing development fallback.
*/
$config['protocol'] = getenv('MAIL_PROTOCOL') ? getenv('MAIL_PROTOCOL') : 'mail';
$config['smtp_host'] = getenv('MAIL_HOST') ? getenv('MAIL_HOST') : '';
$config['smtp_user'] = getenv('MAIL_USERNAME') ? getenv('MAIL_USERNAME') : '';
$config['smtp_pass'] = getenv('MAIL_PASSWORD') ? getenv('MAIL_PASSWORD') : '';
$config['smtp_port'] = getenv('MAIL_PORT') ? (int) getenv('MAIL_PORT') : 587;
$config['smtp_crypto'] = getenv('MAIL_ENCRYPTION') ? getenv('MAIL_ENCRYPTION') : 'tls';
$config['mailtype'] = 'text';
$config['charset'] = 'utf-8';
$config['wordwrap'] = TRUE;
$config['newline'] = "\r\n";
$config['crlf'] = "\r\n";

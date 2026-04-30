<?php
define('ENVIRONMENT', 'testing');
define('BASEPATH', realpath(__DIR__ . '/../system') . DIRECTORY_SEPARATOR);
define('APPPATH', realpath(__DIR__ . '/../application') . DIRECTORY_SEPARATOR);
define('VIEWPATH', APPPATH . 'views' . DIRECTORY_SEPARATOR);
define('FCPATH', realpath(__DIR__ . '/../') . DIRECTORY_SEPARATOR);

require_once BASEPATH . 'core/Common.php';
require_once BASEPATH . 'core/Controller.php';
require_once BASEPATH . 'core/Model.php';
require_once BASEPATH . 'helpers/url_helper.php';

foreach (glob(APPPATH . 'core/*.php') as $coreFile) {
	require_once $coreFile;
}

foreach (glob(APPPATH . 'libraries/*.php') as $libraryFile) {
	require_once $libraryFile;
}

foreach (glob(APPPATH . 'models/*.php') as $modelFile) {
	require_once $modelFile;
}

foreach (glob(APPPATH . 'controllers/*.php') as $controllerFile) {
	require_once $controllerFile;
}

require_once __DIR__ . '/support/Fakes.php';
require_once __DIR__ . '/support/TestCase.php';
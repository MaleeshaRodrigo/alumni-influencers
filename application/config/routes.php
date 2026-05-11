<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/userguide3/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
$route['default_controller'] = 'home';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

$route['ping'] = 'home/ping';

$route['api-docs'] = 'apidocs/index';
$route['api-docs/openapi.yaml'] = 'apidocs/openapi';

$route['auth/do-register'] = 'auth/do_register';
$route['auth/do_login'] = 'auth/do_login';
$route['auth/logout'] = 'auth/logout';
$route['auth/send_reset'] = 'auth/send_reset';
$route['auth/do_reset_password'] = 'auth/do_reset_password';
$route['auth/verify-email/(:any)'] = 'auth/verify_email/$1';

$route['bids/store'] = 'bids/store';
$route['bids/status'] = 'bids/status';
$route['bids/history'] = 'bids/history';
$route['bids/run-daily-winner'] = 'bids/run_daily_winner';
$route['bids/run-daily-winner/(:num)'] = 'bids/run_daily_winner/$1';

$route['admin/api_keys'] = 'admin/api_keys';
$route['admin/create_api_key'] = 'admin/create_api_key';
$route['admin/revoke_api_key/(:num)'] = 'admin/revoke_api_key/$1';
$route['admin/usage_logs'] = 'admin/usage_logs';

$route['api/featured-today'] = 'publicapi/featured_today';
$route['publicapi/featured-today'] = 'publicapi/featured_today';

$route['api/profile'] = 'profileapi/profile';
$route['api/profile/basic'] = 'profileapi/basic';
$route['api/profile/save-basic'] = 'profileapi/save_basic';

$route['api/profile/degrees'] = 'profileapi/degrees';
$route['api/profile/degrees/add'] = 'profileapi/add_degree';
$route['api/profile/degrees/update/(:num)'] = 'profileapi/update_degree/$1';
$route['api/profile/degrees/delete/(:num)'] = 'profileapi/delete_degree/$1';

$route['api/profile/certifications'] = 'profileapi/certifications';
$route['api/profile/certifications/add'] = 'profileapi/add_certification';
$route['api/profile/certifications/update/(:num)'] = 'profileapi/update_certification/$1';
$route['api/profile/certifications/delete/(:num)'] = 'profileapi/delete_certification/$1';

$route['api/profile/licences'] = 'profileapi/licences';
$route['api/profile/licences/add'] = 'profileapi/add_licence';
$route['api/profile/licences/update/(:num)'] = 'profileapi/update_licence/$1';
$route['api/profile/licences/delete/(:num)'] = 'profileapi/delete_licence/$1';

$route['api/profile/courses'] = 'profileapi/courses';
$route['api/profile/courses/add'] = 'profileapi/add_course';
$route['api/profile/courses/update/(:num)'] = 'profileapi/update_course/$1';
$route['api/profile/courses/delete/(:num)'] = 'profileapi/delete_course/$1';

$route['api/profile/employment'] = 'profileapi/employment';
$route['api/profile/employment/add'] = 'profileapi/add_employment';
$route['api/profile/employment/update/(:num)'] = 'profileapi/update_employment/$1';
$route['api/profile/employment/delete/(:num)'] = 'profileapi/delete_employment/$1';

// Dashboard Routes
$route['dashboard'] = 'dashboard/index';
$route['dashboard/graphs'] = 'dashboard/graphs';
$route['dashboard/alumni'] = 'dashboard/alumni';
$route['dashboard/profile/(:num)'] = 'dashboard/profile/$1';
$route['dashboard/login'] = 'dashboard/login';
$route['dashboard/register'] = 'dashboard/register';

// Analytics API Routes
$route['api/analytics/alumni_distribution'] = 'analyticsapi/alumni_distribution';
$route['api/analytics/skills_gap'] = 'analyticsapi/skills_gap';
$route['api/analytics/career_pathways'] = 'analyticsapi/career_pathways';
$route['api/analytics/trends'] = 'analyticsapi/trends';
$route['api/analytics/alumni_list'] = 'analyticsapi/alumni_list';
$route['api/analytics/usage_stats'] = 'analyticsapi/usage_stats';

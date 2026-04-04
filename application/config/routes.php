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

$route['home'] = 'home/index';
$route['ping'] = 'home/ping';

$route['register'] = 'auth/register';
$route['auth/register'] = 'auth/register';
$route['auth/do-register'] = 'auth/do_register';
$route['auth/verify-notice'] = 'auth/verify_notice';
$route['auth/verify-email/(:any)'] = 'auth/verify_email/$1';

$route['profile'] = 'profile/dashboard';
$route['profile/dashboard'] = 'profile/dashboard';
$route['profile/basic'] = 'profile/basic';
$route['profile/save-basic'] = 'profile/save_basic';

$route['profile/degrees'] = 'profile/degrees';
$route['profile/degrees/add'] = 'profile/add_degree';
$route['profile/degrees/edit/(:num)'] = 'profile/edit_degree/$1';
$route['profile/degrees/update/(:num)'] = 'profile/update_degree/$1';
$route['profile/degrees/delete/(:num)'] = 'profile/delete_degree/$1';

$route['profile/certifications'] = 'profile/certifications';
$route['profile/certifications/add'] = 'profile/add_certification';
$route['profile/certifications/edit/(:num)'] = 'profile/edit_certification/$1';
$route['profile/certifications/update/(:num)'] = 'profile/update_certification/$1';
$route['profile/certifications/delete/(:num)'] = 'profile/delete_certification/$1';

$route['profile/licences'] = 'profile/licences';
$route['profile/licences/add'] = 'profile/add_licence';
$route['profile/licences/edit/(:num)'] = 'profile/edit_licence/$1';
$route['profile/licences/update/(:num)'] = 'profile/update_licence/$1';
$route['profile/licences/delete/(:num)'] = 'profile/delete_licence/$1';

$route['profile/courses'] = 'profile/courses';
$route['profile/courses/add'] = 'profile/add_course';
$route['profile/courses/edit/(:num)'] = 'profile/edit_course/$1';
$route['profile/courses/update/(:num)'] = 'profile/update_course/$1';
$route['profile/courses/delete/(:num)'] = 'profile/delete_course/$1';

$route['profile/employment'] = 'profile/employment';
$route['profile/employment/add'] = 'profile/add_employment';
$route['profile/employment/edit/(:num)'] = 'profile/edit_employment/$1';
$route['profile/employment/update/(:num)'] = 'profile/update_employment/$1';
$route['profile/employment/delete/(:num)'] = 'profile/delete_employment/$1';

$route['bids'] = 'bids/index';
$route['bids/place'] = 'bids/place';
$route['bids/store'] = 'bids/store';
$route['bids/status'] = 'bids/status';
$route['bids/history'] = 'bids/history';

$route['api/featured-today'] = 'publicapi/featured_today';
$route['publicapi/featured-today'] = 'publicapi/featured_today';

$route['api-docs'] = 'apidocs/index';
$route['api-docs/openapi.yaml'] = 'apidocs/openapi';

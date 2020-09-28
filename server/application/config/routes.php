<?php
defined('BASEPATH') or exit('No direct script access allowed');

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
| example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
| https://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
| $route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
| $route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
| $route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples: my-controller/index -> my_controller/index
|   my-controller/my-method -> my_controller/my_method
*/
$route['default_controller'] = 'welcome';
$route['404_override'] = 'welcome';
$route['403_override'] = 'welcome';
$route['translate_uri_dashes'] = TRUE;

/*
| -------------------------------------------------------------------------
| Sample REST API Routes
| -------------------------------------------------------------------------
*/
$route['api/example/users/(:num)'] = 'api/example/users/id/$1'; // Example 4
$route['api/example/users/(:num)(\.)([a-zA-Z0-9_-]+)(.*)'] = 'api/example/users/id/$1/format/$3$4'; // Example 8

// user routes
$route['api/users/roles'] = '403_override';
$route['user/roles']['GET'] = 'api/users/roles';

$route['api/users/register'] = '403_override';
$route['user/register']['POST'] = 'api/users/register';

$route['api/users/login'] = '403_override';
$route['user/login']['POST'] = 'api/users/login';

$route['api/users/detail/(:num)'] = '403_override';
$route['api/users/detail'] = '403_override';
$route['user/details/(:num)']['GET'] = 'api/users/detail/$1';
$route['user/details/(:num)']['PUT'] = 'api/users/detail/$1';

$route['api/users/password/(:num)'] = '403_override';
$route['api/users/password'] = '403_override';
$route['user/password/(:num)']['PUT'] = 'api/users/password/$1';

$route['api/cart'] = '403_override';
$route['user/cart']['GET'] = 'api/cart';
$route['user/cart']['POST'] = 'api/cart';
$route['user/cart']['DELETE'] = 'api/cart';
$route['user/cart']['PUT'] = 'api/cart';

// product routes
$route['api/categoryProduct'] = '403_override';
$route['product/category']['GET'] = 'api/categoryProduct';
$route['product/category']['POST'] = 'api/categoryProduct';
$route['product/category/(:num)']['DELETE'] = 'api/categoryProduct/$1';
$route['product/category/(:num)']['PUT'] = 'api/categoryProduct/$1';

$route['api/products'] = '403_override';
$route['product']['GET'] = 'api/products';
$route['product']['POST'] = 'api/products';
$route['product/(:num)']['DELETE'] = 'api/products/$1';
$route['product/(:num)']['PUT'] = 'api/products/$1';

$route['api/products/publish'] = '403_override';
$route['api/products/unpublish'] = '403_override';
$route['api/products/sold'] = '403_override';

$route['product/publish/(:num)']['PUT'] = 'api/products/publish/$1';
$route['product/unpublish/(:num)']['PUT'] = 'api/products/unpublish/$1';
$route['product/sold/(:num)']['PUT'] = 'api/products/sold/$1';

// province routes
$route['api/province'] = '403_override';
$route['province']['GET'] = 'api/province';

// city routes
$route['api/city'] = '403_override';
$route['province/city']['GET'] = 'api/city';

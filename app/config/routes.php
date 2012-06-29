<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
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
|     example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|    http://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are two reserved routes:
|
|    $route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|    $route['scaffolding_trigger'] = 'scaffolding';
|
| This route lets you set a "secret" word that will trigger the
| scaffolding feature for added security. Note: Scaffolding must be
| enabled in the controller in which you intend to use it.   The reserved
| routes must come before any wildcard or regular expression routes.
|
*/

$route['default_controller'] = "article_controller";
$route['scaffolding_trigger'] = "";

// for comment controller to pretend it is child to selection and article, must be before article controller routers
$route['article/(:any)/selection/(:any)/comment'] = array(
    "GET" => "comment/GET_index/$1/$2",
    "POST" => "comment/create/$1/$2"
);

// for JSONP
$route['article/(:any)/selection/(:any)/comment/create'] = "comment/create/$1/$2";

// for selection controller to pretend selection controller is child of article controller, must be before article controller routers
$route['article/(:any)/selection'] = array(
    "GET" => "selection/GET_index/$1",
    "POST" => "selection/create/$1"
);

// for JSONP
$route['article/(:any)/selection/create'] = "selection/create/$1";

// for JSONP
$route['article/create'] = "article/create";

// for article contoller
$route['article/(:any)'] = array(
    "GET" => "article/GET_index_id/$1",
    "PUT" => "article/PUT_index/$1",
    "DELETE" => "article/DELETE_index/$1",
);
$route['article'] = array(
    "GET" => "article/GET_index",
    "POST" => "article/create",
);

// simple routes for the facebook controller
$route['connect'] = "facebook/connect";

$route['newsroom/(:any)/article'] = "article/GET_index/$1";

// for article contoller
$route['newsroom/(:any)'] = array(
    "GET" => "newsroom/GET_index_id/$1",
    //"PUT" => "newsroom/PUT_index/$1",
    //"DELETE" => "newsroom/DELETE_index/$1",
);
$route['newsroom'] = array(
    "GET" => "newsroom/GET_index",
    //"POST" => "newsroom/create",
);

/* End of file routes.php */
/* Location: ./app/config/routes.php */

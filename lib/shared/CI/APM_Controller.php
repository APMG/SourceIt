<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once 'CI/APM_AuthUser.php';
require_once 'CI/APM_HTTPResponse.php';

/**
 * Secure Application Controller Base Class (overrides default CI controller)
 *
 * This class object is the parent class to every controller. It
 * implements security, as well as encapsulating any variables passed in
 * with the client request.
 *
 * @package default
 */
class APM_Controller extends Controller {
    public $is_production = false; // production env flag
    public $request_method;
    public $response_view;
    public $authuser;
    public $httpresponse;
    public $auth_user_class = 'APM_AuthUser';
    public $http_response_class = 'APM_HTTPResponse';

    /**
     * Constructor
     *
     * Connect to db, setup request params, init security.
     */
    public function __construct() {
        parent::Controller();

        $this->httpresponse = new $this->http_response_class;

        // set some SERVER vars
        $this->request_method = $this->input->server('REQUEST_METHOD');
        $this->response_view = $this->httpresponse->get_output_view();

        // allow subclasses to implement their own start-up hook
        $this->begin();
    }


    /**
     * begin() checks for security (authentication) using AirUser library.
     * If you override begin(), be sure to call parent::begin() in your subclass.
     *
     * @method begin()
     */
    public function begin() {
        $this->init_security();
    }


    /**
     * initialize security for this controller
     *
     * This function determines what (if any) security the user has and
     * encapsulates it in the $AIR2User class variable.  Will return a
     * login page and exit if the user cannot be authenticated.
     *
     * @access private
     * @return void
     */
    public function init_security() {

        // load required libraries
        $this->authuser = new $this->auth_user_class;

        // check if the credentials were good
        if ( !$this->authuser->has_valid_tkt() || !$this->authuser->get_user() ) {
            // user not authenticated
            $this_uri = current_url() . '?' . $this->input->server('QUERY_STRING');
            Carper::carp("Permission denied for $this_uri");
            $uri = $this->uri_for('login', array('back' => $this_uri));
            //echo "Location: $uri\n";
            redirect($uri);
            exit(0);
        }


        // authn ok
        return true;

    }



    /**
     * Write out a response
     *
     * @return void
     * @param array   $data (optional) The data to publish
     */
    protected function response($data=array(), $status_code=200) {
        if (empty($data)) {
            show_404();
        }
        else {
            $this->httpresponse->write($data, $status_code);
        }
    }


    /**
     * uri_for() will return the full URI for a part of the application.
     * Example:
     *   $this->uri_for('foo/bar', array('color'=>'red'));
     *   //  https://myhost/foo/bar?color=red
     *
     * @param string  $path
     * @param array   $query (optional)
     * @return unknown
     */
    public function uri_for($path, $query=array()) {
        $base = base_url();
        $base = preg_replace('/\/$/', '', $base);
        $path = preg_replace('/^\//', '', $path);
        if (strlen($path)) {
            $uri = $base . '/' . $path;
        }
        else {
            $uri = $base;
        }

        if (count($query)) {
            $uri .= '?' . http_build_query($query);
        }

        return $uri;
    }


    /**
     * Custom handling for showing 404 error
     */
    public function show_404() {
        show_404();
    }



    /**
     * Custom handling for showing 403 error
     */
    public function show_403() {
        show_error("Error: permission denied", 403);
    }


    /**
     * Custom handling for showing 403 error
     */
    public function show_401() {
        show_error("Error: permission denied", 401);
    }


    /**
     * Custom handling for showing 415 error
     */
    public function show_415() {
        show_error('', 415);
    }


}

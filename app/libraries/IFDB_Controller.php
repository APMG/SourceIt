<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once 'CI/APM_Controller.php';
require_once APPPATH.'/libraries/AuthUser.php';
require_once APPPATH.'/libraries/IFDB_HTTPResponse.php';

class IFDB_Controller extends APM_Controller {


    public $auth_user_class = 'AuthUser';
    public $http_response_class = 'IFDB_HTTPResponse';
    public $is_secure = true;

    /**
     * Constructor
     *
     * Connect to db, setup request params, init security.
     */
    public function begin() {
        // set the is_production flag based on setting in app/init.php
        if (IFDB_ENVIRONMENT == 'prod') $this->is_production = true;

        /* always open db connection per request */
        IFDB_DBManager::init();

        if ($this->is_secure) {
            parent::begin();
        }

        header("Access-Control-Allow-Origin: *");
    }


    /**
     * the $this->input->get_post() (and similar get() and post()) not
     * returning an array like it should, so this fixes that
     *
     * @return $array
     */
    public function get_post() {
        if ( isset($_GET) && count($_GET) > 0 ) {
            return $this->input->xss_clean($_GET);
        } else {
            return $this->input->xss_clean($_POST);
        }
    }


}

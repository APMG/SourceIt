<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once 'HTTP_Accept.php';
require_once 'CI/APM_HTTPResponse.php';

/**
 * HTTP Response library
 *
 * @package default
 */
class IFDB_HTTPResponse extends APM_HTTPResponse {


    protected $DEFAULT_FORMAT = "application/json";


}

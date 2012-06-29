<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once 'HTTP_Accept.php';

/**
 * HTTP Response library for Code Igniter.
 *
 * @package default
 */
class APM_HTTPResponse {
    private $_format;
    private $_view;
    private $ctrlr = null; //reference to controller
    private $loader = null; //reference to loader helper

    /* CONFIGURATION */
    private static $VALID_FORMAT_VIEWS = array(
        'application/json' => 'json',
        'text/html'        => 'html',
        'text/plain'       => 'text',
        'application/xml'  => 'xml',
        'application/csv'  => 'csv',
        'application/javascript' => 'jsonp',
    );
    protected $DEFAULT_FORMAT = 'text/html';
    private static $HTML_ERROR_TPL = '<div id="skel-exception"><div class="body skel-corners"><h1>%s</h1><p>%s</p></div></div>';

    // POST variable to indicate the Content-type to return, no matter the view
    private static $MISMATCH_VARNAME = 'skel_mismatch_content';


    /**
     * Constructor -- get references to a loader, enabling view loads.
     */
    public function APM_HTTPResponse() {
        if (class_exists('CI_Base')) {
            $this->ctrlr =& get_instance();
            $this->loader = $this->ctrlr->load;
        }
        else {
            $this->loader = new CI_Loader();
        }

        // detect formats
        $this->_detect_output_format();
    }


    /**
     * Attempt to find a valid output view based on the value of the
     * HTTP_ACCEPT header.  Setting the configured POST variable will
     * cause the RESPONSE_TYPE header to be set to whatever you specify, while
     * returning a body containing the type you actually requested.
     */
    private function _detect_output_format() {
        // default output format
        $this->_format = $this->DEFAULT_FORMAT;
        $this->_view = self::$VALID_FORMAT_VIEWS[$this->_format];

        if (isset($_SERVER['HTTP_ACCEPT'])) {
            $accept_str = $_SERVER['HTTP_ACCEPT'];
            $http_accept = new HTTP_Accept($accept_str);

            // look for a supported format acceptable to the client
            foreach (self::$VALID_FORMAT_VIEWS as $format => $view) {
                // check if this format is in the client's HTTP_ACCEPT
                if ($http_accept->isMatchExact($format)) {
                    $this->_format = $format;
                    $this->_view = $view;

                    if (isset($_POST[self::$MISMATCH_VARNAME])) {
                        $this->_format = $_POST[self::$MISMATCH_VARNAME];
                    }
                    return;
                }
            }

            // check for an image-proxying url
            if (preg_match('/^image/', $_SERVER['HTTP_ACCEPT']) && $this->ctrlr
                && isset($this->ctrlr->image_proxies)) {
                foreach ($this->ctrlr->image_proxies as $name) {
                    if (preg_match("/$name$/", $_SERVER['PHP_SELF'])) return;
                }
            }

            // many browsers, including iPhone and IE7, send a */* meaning they
            // are hopelessly promiscuous.  Just send default format.
            if (strpos($accept_str, '*/*') !== false) {
                return;
            }

            // not found!  Return a 415
            show_error("Unsupported request format. Valid: $accept_str", 415);
        }
    }


    /**
     * Getter for the output view
     *
     * @return string
     */
    public function get_output_view() {
        return $this->_view;
    }


    /**
     * Setter for the output view
     *
     * @param string  $view
     */
    public function set_output_view($view) {
        $this->_view = $view;
    }


    /**
     * Helper function to set headers for a certain status code and content
     * type.
     *
     * @param int     $status_code
     */
    private function _set_headers($status_code) {
        set_status_header($status_code);

        // NOTE: there's a bug in CI's set_header() so use the PHP one instead!
        header('Content-type: '.$this->_format, true);

        // Absolutely NO caching of views!
        header("Cache-Control: no-store");
    }


    /**
     * Write out a response to the view requested by the client.
     *
     * @param array   $data        (optional) data to output
     * @param int     $status_code (optional)
     */
    public function write($data=array(), $status_code=200) {
        // views expect top key named for the view
        $resp = array($this->_view => $data, 'c' => $this->ctrlr);

        // catch any exceptions the View might throw
        try {
            $out = $this->loader->view($this->_view, $resp, true);
            $this->_set_headers($status_code);
            echo $out;
            exit(0);
        }
        catch (Exception $err) {
            // re-throw the exception if we are not in production
            if (!$this->ctrlr->is_production)
                show_error($err);
            else
                show_error("There was a server error. Please try again later.");
        }
    }


    /**
     * Write out an error to the view requested by the client.
     *
     * @param int     $status_code
     * @param string  $heading
     * @param string  $message
     */
    public function write_error($status_code, $heading, $message) {
        if ($this->_view == 'html') {
            $this->write(
                array(
                    'head' => array('title' => $heading),
                    'body' => sprintf(self::$HTML_ERROR_TPL, $heading, $message),
                ),
                $status_code
            );
        }
        else {
            $this->write(
                array(
                    'status_code' => $status_code,
                    'heading' => "$heading",
                    'message' => "$message",
                    'success' => false,
                ),
                $status_code
            );
        }
    }


}

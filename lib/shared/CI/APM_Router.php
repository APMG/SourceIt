<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * MIME/REST-aware Router
 *
 * Custom subclass of Router to allow the use of GET, POST, PUT, and DELETE
 * in routing.  Routes can now look like:
 *   $route['batch'] = array(
 *       'GET' => 'batch/findAll',
 *       'POST' => 'batch/create'
 *   );
 *   $route['batch/(:num)/sources'] = array(
 *       'GET' => 'batch/findAllSources/$1'
 *   );
 *
 * @package default
 */
define('X_TUNNELED_METHOD_NAME', 'x-tunneled-method');

class APM_Router extends CI_Router {
    private $server_request;
    private static $VALID_REQUEST_METHODS = array(
        'DELETE', 'GET', 'POST', 'PUT',
    );
    private static $VALID_URL_HTTP_ACCEPT = array(
        'json' => 'application/json',
        'xml'  => 'application/xml',
        'html' => 'text/html',
        'htm'  => 'text/html',
        'text' => 'text/plain',
        'txt'  => 'text/plain',
        'csv'  => 'application/csv',
    );



    /**
     * Constructor
     */
    function APM_Router() {
        $this->server_request = $this->_detect_request_method();
        $this->_http_format_url();
        parent::CI_Router();
    }


    /**
     * Overrides base method to append _controller suffix to URI segment.
     *
     * @param array   $segments
     * @return array $segments
     */
    function _validate_request($segments) {
        // naming convention for all URI => controller classes
        if (isset($segments[0]) && !preg_match('/_controller$/', $segments[0])) {
            $segments[0] .= '_controller';
        }

        // if controller doesn't exist, redirect to "home" controller
        $name = APPPATH.'controllers/'.$segments[0];
        if (!file_exists($name.EXT) && !is_dir($name)) {
            $segments = array('home_controller', '404');
        }

        return parent::_validate_request($segments);
    }


    /**
     * Parse Routes
     *
     * Overridden function to allow distinguishing between GET, POST, PUT, and
     * DELETE requests.  Added to support REST api.
     *
     * @access private
     * @return void
     */
    function _parse_routes() {
        // detect the request method
        $server_request = $this->server_request;

        // Do we even have any custom routing to deal with?
        // There is a default scaffolding trigger, so we'll look just for 1
        if (count($this->routes) == 1) {
            $this->_set_request($this->uri->segments);
            return;
        }

        // Turn the segment array into a URI string
        $uri = implode('/', $this->uri->segments);

        // Is there a literal match?  If so we're done
        if (isset($this->routes[$uri])) {
            if (is_array($this->routes[$uri])) {
                //look for a match in the array
                foreach ($this->routes[$uri] as $req_method => $route) {
                    if (strtoupper($req_method) == $server_request) {
                        // match!
                        $this->_set_request(explode('/', $route));
                        return;
                    }
                }

                // if we got here, no route exists
                $this->_show_405($server_request);
            } else {
                $this->_set_request(explode('/', $this->routes[$uri]));
                return;
            }
        }

        // Loop through the route array looking for wild-cards
        foreach ($this->routes as $key => $val) {
            // Convert wild-cards to RegEx
            $key = str_replace(':any', '[^\/]+', str_replace(':num', '[0-9]+', $key));

            // Does the RegEx match?
            if (preg_match('#^'.$key.'$#', $uri)) {
                // check for the method array
                if (is_array($val)) {
                    // look for a match in the array
                    foreach ($val as $req_method => $route) {
                        if (strtoupper($req_method) == $server_request) {
                            $val = $route; // reset the value!
                            break;
                        }
                    }

                    // show 404 if we didn't find a matching route
                    if (is_array($val)) $this->_show_405($server_request);
                }
                // Do we have a back-reference?
                if (strpos($val, '$') !== FALSE and strpos($key, '(') !== FALSE) {
                    $val = preg_replace('#^'.$key.'$#', $val, $uri);
                }

                $this->_set_request(explode('/', $val));
                return;
            }
        }

        // If we got this far it means we didn't encounter a
        // matching route so we'll set the site default route
        $this->_set_request($this->uri->segments);
    }


    /**
     * Checks the URI segments for tokens indicating an explicit HTTP_ACCEPT
     * type (an acceptable type to return).  This token will be processed and
     * unset. (ex: http://localhost/api/source/.json)
     *
     * @return void
     */
    private function _http_format_url() {
        $path = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';
        //Carper::carp("path=$path");
        $segments = explode('/', $path);

        // Look for the special format token in the LAST segment
        // (it's invalid anywhere else)
        if (!is_array($segments) || count($segments) == 0) {
            return;
        }

        $idx = count($segments)-1;

        // allow for foo.html as well as foo/.html
        if (preg_match('/.+(\.\w+)$/', $segments[$idx], $matches)) {
            $segments[$idx] = preg_replace('/\.(\w+)$/', '', $segments[$idx]);
            array_push($segments, $matches[1]);
            $idx++;
        }
        //Carper::carp("segments=".var_export($segments, true));

        if (preg_match('/^\./', $segments[$idx])) {
            $format = substr($segments[$idx], 1);
            if (isset(self::$VALID_URL_HTTP_ACCEPT[$format])) {
                $_SERVER['HTTP_ACCEPT'] = self::$VALID_URL_HTTP_ACCEPT[$format];
            }
            else {
                $_SERVER['HTTP_ACCEPT'] = "unknown/$format";
            }
            array_splice($segments, $idx, 1);
        }

        $_SERVER['PATH_INFO'] = implode('/', $segments);
    }


    /**
     * Detect the HTTP request method
     *
     * @return unknown
     */
    private function _detect_request_method() {
        // IMPORTANT: check the POST var 'x-tunnel-method', which will
        // indicate if we're using POST tunneling or not!
        $server_request = strtoupper($_SERVER['REQUEST_METHOD']);
        if ($server_request == 'POST' && isset($_POST[X_TUNNELED_METHOD_NAME])) {
            $server_request = strtoupper($_POST[X_TUNNELED_METHOD_NAME]);
        }

        // a bit of extra error checking
        if (!in_array($server_request, self::$VALID_REQUEST_METHODS)) {
            $this->_show_405($server_request);
        }

        return $server_request;
    }


    /**
     * Helper function to set the "Allow" header before showing a 405 error.
     *
     * @param unknown $method
     */
    private function _show_405($method) {
        header('Allow: '.implode(', ', self::$VALID_REQUEST_METHODS));
        show_error("Error: Unsupported request method: $method", 405);
    }


}

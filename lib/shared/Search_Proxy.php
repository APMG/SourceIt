<?php

/*
    Search_Proxy is a cURL wrapper for fetching
    results from the AIR search server.

    Example of use:

    $search_proxy = new Search_Proxy(array('url' => 'http://localhost:3001'));
    $response     = $search_proxy->response();

    if ($response['response']['http_code'] != 200) {
        header('X-AIR: server error', false, $response['response']['http_code']);
    }

    // The web service returns JSON. Set the Content-Type appropriately
    // based on what the agent can Accept
    if (isset($_POST['gzip']) && $_POST['gzip']) {
        header("Content-Encoding: gzip");
    }
    if (preg_match('/application\/json/', $_SERVER['HTTP_ACCEPT'])) {
        header("Content-Type: application/json; charset=utf-8");
    }
    else {
        header("Content-Type: text/javascript; charset=utf-8");
    }

    echo $response['json'];

    // END example

*/

class Search_Proxy {
    public $url = 'http://pijnat02.mpr.org:3001';
    public $tkt = null;
    public $cookie_name = 'auth_tkt';
    private $session;

    /**
     * $opts is a hash of key/value pairs. Supported
     * keys are:
     *  url (the search server)
     *  cookie_name (the name of the auth tkt cookie)
     *  tkt (the auth tkt value to use)
     *  post_only (ignore GET params)
     *  query (explicit query)
     *  params (explicitly override GET and POST)
     *
     * @param array   $opts (optional)
     * @return unknown
     */


    public function Search_Proxy($opts=array()) {
        if (isset($opts['url']) && $opts['url']) {
            $this->url = $opts['url'];
        }

        if (isset($opts['cookie_name']) && $opts['cookie_name']) {
            $this->cookie_name = $opts['cookie_name'];
        }

        if (isset($opts['tkt']) && $opts['tkt']) {
            $this->tkt = $opts['tkt'];
        }
        else {
            $this->tkt = $_COOKIE[$this->cookie_name];
        }

        $post = $_POST; // make a copy

        // merge GET
        if (!isset($opts['post_only']) || !$opts['post_only']) {
            foreach ($_GET as $get_param=>$val) {
                $post[$get_param] = $val;
            }
        }

        // if $query is passed in, override $_POST
        if (isset($opts['query']) && $opts['query']) {
            $post['q'] = $opts['query'];
        }

        // any explicit params override the global POST and GET vars
        if (isset($opts['params'])) {
            foreach ($opts['params'] as $k=>$v) {
                $post[$k] = $v;
            }
        }

        // set up the curl session
        $this->session = curl_init($this->url);

        // If it's a POST, put the POST data in the body
        $postvars = array();
        foreach ($post as $n=>$v) {
            $postvars[]= urlencode($n).'='.urlencode($v);
        }
        $poststr = implode('&', $postvars);

        curl_setopt ($this->session, CURLOPT_POST, true);
        curl_setopt ($this->session, CURLOPT_POSTFIELDS, $poststr);

        // Don't return HTTP headers. Do return the contents of the call
        curl_setopt($this->session, CURLOPT_HEADER, false);
        curl_setopt($this->session, CURLOPT_RETURNTRANSFER, true);
        
        // ignore any ssl cert errors
        curl_setopt($this->session, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($this->session, CURLOPT_SSL_VERIFYHOST, false);

        // pass cookie in url for explicit-ness.
        curl_setopt($this->session, CURLOPT_COOKIE, $this->cookie_name.'='.$this->tkt);

        return $this;
    }


    /**
     *
     *
     * @return array with keys 'json' and 'response'
     */
    public function response() {
        // Make the call
        ob_start();         // capture any output
        $json = curl_exec($this->session);
        ob_end_clean();     // stop capturing output
        $ret = curl_getinfo($this->session);
        
        // get any error
        $error = curl_error($this->session);

        return array('json' => $json, 'response' => $ret, 'error' => $error );
    }


    /**
     * Destructor
     */
    function __destruct() {
        curl_close($this->session);
    }


}


?>

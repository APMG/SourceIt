<?php
require_once 'ApacheAuthTkt.php';
/**
 */


// ------------------------------------------------------------------------

/**
 * IFDB Secure User Tkt
 *
 * This class encapsulates user security/credentials. This
 * is stored in a 'tkt' (ticket), which is essentially an encrypted cookie.
 *
 */
class IFDB_User {
    /* Default time (in seconds) before a cookie expires */
    private static $COOKIE_EXPIRE_TIME = 86400; // 1 day

    /* Encoding to use for digest */
    private static $DIGEST_TYPE = 'md5';

    private $secret; // secret key
    private $cookie; // tkt cookie name
    private $auth_tkt = NULL; // ticket object
    private $tkt_info = NULL; // array of values in the ticket
    private $tkt_is_valid = false;

    /**
     * Constructor
     *
     * Sets up user credentials from the users tkt (or lack of)
     */
    public function __construct() {
        $cfgfile = realpath( dirname(__FILE__).'/../config/').'/authorization.conf';
        // read the config file for cookiename and secret tokens
        $this->_read_config($cfgfile);

        $this->auth_tkt = new ApacheAuthTkt(
            $this->secret,
            IFDB_User::$DIGEST_TYPE
        );

        $authcook = null;
        if (isset($_COOKIE[$this->cookie])) {
            $authcook = $_COOKIE[$this->cookie];
        }
        elseif(isset($_GET[$this->cookie])) {
            $authcook = $_GET[$this->cookie];
        }
        elseif(isset($_POST[$this->cookie])) {
            $authcook = $_POST[$this->cookie];
        }
        else {
            $this->tkt_is_valid = false;
            return;
        }

        if (isset($authcook)) {
            // check for a valid ticket
            $this->tkt_info = $this->auth_tkt->validate_ticket($authcook);
            if ($this->tkt_info) {
                // ticket decrypted ok --- check the internal timestamp to see if it's expired
                $sec_rem = ($this->tkt_info['ts'] + IFDB_User::$COOKIE_EXPIRE_TIME) - time();
                if ($sec_rem > 0) {
                    $this->tkt_is_valid = true;
                }
            }
        }
        else {
            $this->tkt_is_valid = false;
        }
    }


    /**
     * Check for a valid ticket
     *
     * @access public
     * @return bool whether the users tkt is valid or not
     */
    public function has_valid_tkt() {
        return $this->tkt_is_valid;
    }


    /**
     * Creates a new (valid) ticket for a user
     *
     * @access public
     * @return void
     * @param string  $usrname
     * @param array   $org_roles an array mapping organizations to roles
     * @param timestamp $ts (default 'time()') sets a time to issue this tkt for
     */
    public function create_tkt($usrname, $org_roles, $ts=false) {
        if (!$ts) {
            $ts = time();
        }
        $new_ticket = $this->auth_tkt->create_ticket(
            $usrname,
            json_encode($org_roles),
            $ts
        );
        setcookie($this->cookie, $new_ticket, time()+IFDB_User::$COOKIE_EXPIRE_TIME);
        $this->tkt_is_valid = true;
    }


    /**
     * Get a new (valid) ticket for a user, returning it without setting a
     * cookie.
     *
     * @access public
     * @return array associative array with the cookie name and the tkt
     * @param string  $usrname
     * @param array   $org_roles an array mapping organizations to roles
     * @param timestamp $ts (default 'time()') sets a time to issue this tkt for
     */
    public function get_tkt($usrname, $org_roles, $ts=false) {
        if (!$ts) {
            $ts = time();
        }
        $new_ticket = $this->auth_tkt->create_ticket(
            $usrname,
            json_encode($org_roles),
            $ts
        );
        $this->tkt_is_valid = true;
        return array($this->cookie => $new_ticket);
    }


    /**
     * Deletes this user's ticket
     *
     * @access public
     * @return void
     */
    public function delete_tkt() {
        setcookie($this->cookie, null, time()-3600);
        $this->tkt_is_valid = false;
    }


    /**
     * Gets the roles associative array from the ticket
     *
     * @access public
     * @return array the roles validated for this user
     */
    public function get_roles() {
        if (isset($this->tkt_info['data'])) {
            $json = json_decode($this->tkt_info['data']);
            return (array)$json;
        }
        return array();
    }

    /**
     * Reads the config file for authorization
     *
     * @access private
     * @param string $cfgfile the path/name of the auth config file
     * @return void
     */
    private function _read_config($cfgfile) {
        $buf = file_get_contents( $cfgfile );
        $secret_key = "AuthSecret";
        $secret_min_len = 10;
        $cookie_key = "AuthCookie";


        if ( $buf === FALSE ) {
            throw new Exception("cannot read authorization config file");
        } else {
            $matches = array();
            if ( preg_match( "/^\s*$secret_key\s+\"(.*?)\"/m",$buf, $matches ) ) {
                $this->secret = $matches[1];
                if ( strlen( $this->secret ) < $secret_min_len ) {
                    throw new Exception("secret key too short: $buf");
                }
            } else {
                throw new Exception("secret not set in authorization config file");
            }
            $matches = array();
            if ( preg_match( "/^\s*$cookie_key\s+\"(.*?)\"/m",$buf, $matches ) ) {
                $this->cookie = $matches[1];
            } else {
                throw new Exception("secret not set in authorization config file");
            }

        }
    }
}


/* End of file IFDB_User.php */

<?php

require_once 'Apache_AuthTkt.php';

/**
 * APM Authenticated User class.
 *
 * This class encapsulates user security/credentials.
 * Auth token stored in a 'tkt' (ticket), which is essentially an encrypted cookie.
 *
 * @package default
 */
class APM_AuthUser {

    /* Default time (in seconds) before a cookie expires */
    public $COOKIE_EXPIRE_TIME = 86400; // 1 day
    
    // your subclass must define
    public $AUTH_TKT_CONFIG_FILE = NULL;

    public $ENCRYPT_DATA         = true;

    /* Encoding to use for digest */
    private static $DIGEST_TYPE = 'md5';

    public $cookie = NULL;      // your subclass should define
    public $auth_tkt = NULL;    // ticket object
    private $tkt_info = NULL;   // array of values in the ticket
    private $tkt_is_valid = false;
    private $tkt_data = array(); // decoded tkt data

    /**
     * Constructor
     *
     * Sets up user credentials from the users tkt (or lack of)
     */
    public function __construct() {
        $this->auth_tkt = new Apache_AuthTkt(array(
                'conf'          => $this->AUTH_TKT_CONFIG_FILE,
                'encrypt_data'  => $this->ENCRYPT_DATA,
            )
        );

        $authcook = null;
        if (isset($_COOKIE[$this->cookie])) {
            $authcook = $_COOKIE[$this->cookie];
        }
        elseif (isset($_GET[$this->cookie])) {
            $authcook = $_GET[$this->cookie];
        }
        elseif (isset($_POST[$this->cookie])) {
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
                $sec_rem = ($this->tkt_info['ts'] + $this->COOKIE_EXPIRE_TIME) - time();
                if ($sec_rem > 0) {
                    $this->tkt_is_valid = true;
                    $this->tkt_data = json_decode($this->tkt_info['data'], true);
                }
                else {
                    $this->delete_tkt();
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
     * @param array   $user_obj
     * @param boolean $setck    (optional) false to skip setting the cookie
     * @return void
     */
    public function create_tkt($user_obj, $setck=true) {
        $this->tkt_data['user'] = array(
            'uuid'         => $user_obj['user_uuid'],
            'username'     => $user_obj['user_username'],
            'first_name'   => $user_obj['user_first_name'],
            'last_name'    => $user_obj['user_last_name'],
            'type'         => $user_obj['user_type'],
            'status'       => $user_obj['user_status'],
        );
        $tkt = $this->get_tkt($user_obj['user_username']);
        if ($setck) {
            setcookie(
                $this->cookie,
                $tkt[$this->cookie],
                time()+self::$COOKIE_EXPIRE_TIME,
                '/'
            );
        }
        return $tkt;
    }


    /**
     * Refresh the tkt with any changes made to $this->tkt_data
     *
     * @access public
     * @return void
     */
    public function refresh_tkt() {
        if ($this->has_valid_tkt()) {
            $tkt = $this->get_tkt($this->get_username());
            setcookie(
                $this->cookie,
                $tkt[$this->cookie],
                time()+self::$COOKIE_EXPIRE_TIME, //won't affect tktinfo ts
                '/'
            );
        }
    }


    /**
     * Get a new (valid) ticket for a user, returning it without setting a
     * cookie.
     *
     * @access public
     * @param string  $usrname
     * @param array   $data    (optional) data to add to the tkt
     * @return array associative array with the cookie name and the tkt
     */
    public function get_tkt($usrname, $data=array()) {
        foreach ($data as $key => $val) {
            $this->tkt_data[$key] = $val;
        }
        $new_ticket = $this->auth_tkt->create_ticket(array(
                'user' => $usrname,
                'data' => json_encode($this->tkt_data),
                'ts'   => $this->tkt_info['ts'] ? $this->tkt_info['ts'] : time()
            )
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
        setcookie($this->cookie, null, time()-3600, '/');
        $this->tkt_is_valid = false;
    }


    /**
     * Gets the username of an authenticated user
     *
     * @access public
     * @return string username of the user
     */
    public function get_username() {
        if ($this->has_valid_tkt() && isset($this->tkt_info['uid'])) {
            return $this->tkt_info['uid'];
        }
        return null;
    }


}

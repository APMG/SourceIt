<?php

require_once 'CI/APM_AuthUser.php';

/**
 * Secure User Tkt
 *
 * This class encapsulates user security/credentials.
 * Auth token stored in a 'tkt' (ticket), which is essentially an encrypted cookie.
 *
 * @package default
 */
class AuthUser extends APM_AuthUser {

    public $cookie = AUTH_TKT_NAME;
    public $AUTH_TKT_CONFIG_FILE = AUTH_TKT_CONFIG_FILE;

}

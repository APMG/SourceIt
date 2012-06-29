<?php
/**************************************************************************
 *
 *   Copyright 2010 American Public Media Group
 *
 *   This file is part of AIR2.
 *
 *   AIR2 is free software: you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation, either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   AIR2 is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with AIR2.  If not, see <http://www.gnu.org/licenses/>.
 *
 *************************************************************************/

require_once dirname(__FILE__).'/recaptcha-php-1.11/recaptchalib.php';


/***************************************************************************
 * Password_Reset_Controller:
 *
 * This CodeIgniter controller was developed as a base class for controllers
 * implementing a password-reset functionality. It requires a doctrine class
 * to store rows of (token, expire_dtim, user_id).
 *
 * Subclasses should override many (if not all) of the protected variables, as
 * well as implementing all abstract methods.
 *
 ***************************************************************************/
abstract class Password_Reset_Controller extends Controller {

    /* reCAPTCHA configuration (http://recaptcha.net/api/getkey?app=php) */
    protected $domain_name = 'pin'; //WARNING: THIS IS A GLOBAL KEY
    protected $public_key = '6Leh8LoSAAAAAHms1Cw4jDHpRFwMykprYlAuGC_a';
    protected $private_key = '6Leh8LoSAAAAABjuYmhKIxSCFNr-cbybD-fcewnf';

    /* doctrine configuration */
    protected $doctrine_model_name   = 'PasswordReset';
    protected $doctrine_token_col    = 'pr_token';
    protected $doctrine_expire_col   = 'pr_expiration';
    protected $doctrine_login_pk_col = 'pr_login_pk';

    /* other configuration */
    protected $url_expire_duration = 86400; //in seconds
    protected $url_dtim_format = 'Y-m-d H:i:s';

    /* error message configuration */
    protected $form_missing_field = 'Fill in all required fields.';
    protected $form_password_match = 'Passwords did not match!';
    protected $form_captcha_error = 'Incorrect captcha!';
    protected $form_cred_error = 'Unable to validate credentials.';
    protected $form_reset_url_error = 'There was a problem with your request. Contact an administrator.';
    protected $form_pw_change_error = 'There was a problem with setting your new password. Sorry.';
    protected $form_validation_error = "";

    /* PRIVATE variables */
    private $captcha_html;
    private $login_id_form_name = 'login_name';
    private $pass_1_form_name = 'login_pass_1';
    private $pass_2_form_name = 'login_pass_2';


    /**
     * Output the password reset request page.  MAKE SURE the login id input
     * field has the name "login_name".
     *
     * @param string  $action       the action of the form
     * @param string  $captcha_html html to insert for the reCaptcha field
     * @param string  $err_msg      optional error message to display
     * @package default
     */
    abstract protected function show_reset_request_form($action, $captcha_html, $err_msg=null);


    /**
     * Output the email sent page.  Output should distinguish between a
     * successful and unsuccessful email attempt.
     *
     * @param string  $email   the email address that was used
     * @param bool    $success whether the email succeeded or not
     */
    abstract protected function show_email_sent_page($email, $success);


    /**
     * Output the password change page.  MAKE SURE the login id input field has
     * the name "login_name", and the password fields (must be 2 of them) have
     * the names "login_pass_1" and "login_pass_2".
     *
     * @param string  $action       the action of the form
     * @param string  $captcha_html html to insert for the reCaptcha field
     * @param string  $err_msg      optional error message to display
     */
    abstract protected function show_change_form($action, $captcha_html, $err_msg=null);


    /**
     * Output a page indicating that the user's password was successfully
     * changed.
     */
    abstract protected function show_password_changed_page();


    /**
     * Search for the primary key identifier and email address for the login
     * name provided by a user.
     *
     * @param string  $login_name login provided by the requester
     * @return array(1234, 'email@email.com') | false if not found
     */
    abstract protected function get_login_credentials($login_name);


    /**
     * Get a uuid token of the correct length for the database column
     *
     * @return string url-friendly uuid
     */
    abstract protected function get_url_token();


    /**
     * Send an email, containing the password reset url in the body
     *
     * @param string  $email email address
     * @param string  $url   the url to reset this users password
     * @return bool whether or not the email was successfully sent
     */
    abstract protected function send_email($email, $url);


    /**
     * Changes the password for a user.
     *
     * @param int     $loginid the pk id of the user
     * @param string  $pw1     the password to change to
     * @return bool whether the password was successfully changed or not
     */
    abstract protected function change_password($login_id, $password);


    /**
     * Verify the validity of a password before it is changed. Called within
     * change_password_page() on the proposed new password.
     *
     * @param string  $login_name the username
     * @param string  $password   the password to be validated
     * @return bool whether the password is valid or not
     */
    abstract protected function validate_password($login_name, $password);


    /*************************************************************************
     * DO NOT OVERRIDE METHODS PAST THIS POINT! (unless you really want to)  *
     *************************************************************************/

    /**
     * Constructor to create captcha
     */
    public function __construct() {
        parent::Controller();
        $this->captcha_html = recaptcha_get_html($this->public_key, null, true);
    }


    /**
     * Landing page for any requests for password-change emails
     */
    public function index() {
        $req_method = $this->input->server('REQUEST_METHOD');
        $act = current_url();

        if ($req_method == 'GET') {
            $this->show_reset_request_form($act, $this->captcha_html);
        }
        elseif ($req_method == 'POST') {
            // check captcha
            $captcha_challenge = $this->input->post('recaptcha_challenge_field');
            $captcha_response = $this->input->post('recaptcha_response_field');
            $check = recaptcha_check_answer(
                $this->private_key,
                $this->input->server('REMOTE_ADDR'),
                $captcha_challenge,
                $captcha_response
            );
            if (!$check->is_valid) {
                $this->show_reset_request_form($act, $this->captcha_html, $this->form_captcha_error);
                return;
            }

            // check existence of login name
            $name = $this->input->post($this->login_id_form_name);
            if (!$name) {
                $this->show_reset_request_form($act, $this->captcha_html, $this->form_missing_field);
                return;
            }

            // search for id associated with login name
            $cred = $this->get_login_credentials($name);
            if (!$cred) {
                $this->show_reset_request_form($act, $this->captcha_html, $this->form_cred_error);
                return;
            }
            $loginid = $cred[0];
            $email = $cred[1];

            // create the reset url
            $url = $this->_create_reset_url($loginid);

            // send the email
            $success = $this->send_email($email, $url);

            // show the final page
            $this->show_email_sent_page($email, $success);
        }
    }


    /**
     * Helper function to create entry in password reset table.
     *
     * @param int     $login_id
     * @return string url to reset this users password
     */
    protected function _create_reset_url($login_id) {
        $tbl = Doctrine::getTable($this->doctrine_model_name);

        // check that there isn't already a URL for this login id
        $row = $tbl->findOneBy($this->doctrine_login_pk_col, $login_id);
        if ($row) {
            $row->delete();
        }

        $new = $tbl->create();
        $new[$this->doctrine_login_pk_col] = $login_id;
        $new[$this->doctrine_token_col] = $this->get_url_token();
        $new[$this->doctrine_expire_col] = date($this->url_dtim_format, time() + $this->url_expire_duration);

        try {
            $new->save();
            return current_url().'/'.$new[$this->doctrine_token_col];
        }
        catch (Exception $e) {
            $this->show_reset_request_form(current_url(), $this->captcha_html, $this->form_reset_url_error);
            exit(0);
        }
    }


    /**
     * Landing page for change-password form.
     *
     * @param string  $token authorized password-change token (from url)
     */
    public function change_password_page($token) {
        $tbl = Doctrine::getTable($this->doctrine_model_name);

        $row = $tbl->findOneBy($this->doctrine_token_col, $token);
        if (!$row) {
            $this->show_404();
        }
        elseif (time() > strtotime($row[$this->doctrine_expire_col])) {
            $row->delete();
            $this->show_404();
        }
        else {
            $req_method = $this->input->server('REQUEST_METHOD');
            $act = current_url();

            if ($req_method == 'GET') {
                $this->show_change_form($act, $this->captcha_html);
            }
            elseif ($req_method == 'POST') {
                // check captcha
                $captcha_challenge = $this->input->post('recaptcha_challenge_field');
                $captcha_response = $this->input->post('recaptcha_response_field');
                $check = recaptcha_check_answer(
                    $this->private_key,
                    $this->input->server('REMOTE_ADDR'),
                    $captcha_challenge,
                    $captcha_response
                );
                if (!$check->is_valid) {
                    $this->show_change_form($act, $this->captcha_html, $this->form_captcha_error);
                    return;
                }

                // check that all fields were filled out, and pw's match
                $name = $this->input->post($this->login_id_form_name);
                $pw1 = $this->input->post($this->pass_1_form_name);
                $pw2 = $this->input->post($this->pass_2_form_name);
                if (!$name || !$pw1 || !$pw2) {
                    $this->show_change_form($act, $this->captcha_html, $this->form_missing_field);
                    return;
                }
                if ($pw1 != $pw2) {
                    $this->show_change_form($act, $this->captcha_html, $this->form_password_match);
                    return;
                }
                if (!$this->validate_password($name, $pw1)) {
                    $this->show_change_form($act, $this->captcha_html, $this->form_validation_error);
                    return;
                }

                // search for id associated with login name
                $cred = $this->get_login_credentials($name);
                if (!$cred) {
                    $this->show_change_form($act, $this->captcha_html, $this->form_cred_error);
                    return;
                }
                $loginid = $cred[0];
                $email = $cred[1];

                // check the loginid against the one in the password reset table
                if ($row[$this->doctrine_login_pk_col] != $loginid) {
                    $this->show_change_form($act, $this->captcha_html, $this->form_cred_error);
                    return;
                }

                // everything seems to have worked out... change the password!
                $success = $this->change_password($loginid, $pw1);

                if ($success) {
                    $row->delete();
                    $this->show_password_changed_page();
                }
                else {
                    $this->show_change_form($act, $this->captcha_html, $this->form_pw_change_error);
                }
            }
        }
    }


    /**
     * Show a 404 error for a password-reset token that either doesn't exist,
     * or just expired.  Override for custom 404 views.
     *
     * @param boolean $page_existed
     */
    protected function show_404($page_existed=false) {
        show_404();
    }


}

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

class PINPassword {


    private $username;
    private $phrase;
    private $error;
    private $min_length = 8;

    static $gen_chars = array(
        'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'm', 'n', 'o', 'p',
        'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', 'A', 'B', 'C', 'D', 'E',
        'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'P', 'Q', 'R', 'S', 'T', 'U',
        'V', 'W', 'X', 'Y', 'Z', '2', '3', '4', '5', '6', '7', '8', '9', ',',
        '?',        '.',        '$',        '!',        '+',        '=',
    );

    /**
     *
     *
     * @param unknown $opts
     * @return unknown
     */
    public function PINPassword($opts) {
        if (!is_array($opts)) {
            throw new Exception("opts should be an associative array");
        }

        if (!isset($opts['username'])) {
            throw new Exception("username required");
        }
        if (!isset($opts['phrase'])) {
            throw new Exception("phrase required");
        }
        $this->username = $opts['username'];
        $this->phrase   = $opts['phrase'];
        return $this;
    }


    /**
     *
     *
     * @return string error message
     */
    public function get_error() {
        return $this->error;
    }


    /**
     *
     *
     * @param string  $username
     * @param int     $len      (optional) default 10
     * @return string random string of $len chars
     */
    public static function generate($username, $len=10) {

        $pinpass = new PINPassword(array('username' => $username, 'phrase' => 'ignored'));
        $str = '';
        $chars = array();
        $nchars = count(self::$gen_chars);
        while ( !$pinpass->validate( $username, $str ) ) {
            $chars = array();
            for ( $i = 0; $i <= ($len - 1); $i++) {
                $r = rand(0, $nchars - 1);
                $chars[$i] = self::$gen_chars[ $r ];
            }
            $str = implode('', $chars);
        }
        return $str;

    }



    /**
     *
     *
     * @param unknown $username (optional)
     * @param unknown $str      (optional)
     * @return unknown
     */
    public function validate($username=null, $str=null) {
        if (!isset($username)) {
            $username = $this->username;
        }
        if (!isset($str)) {
            $str = $this->phrase;
        }

        $methods = array(
            'length',
            'nousername',
            'punc',
            'lower',
            'upper',
            'number'
        );

        foreach ($methods as $method) {
            $m = 'valid_' . $method;
            if (!$this->$m( $username, $str )) {
                //diag("$str failed: " . $this->error);
                return false;
            }
        }
        return true;
    }



    /**
     * Source (MyPIN) accounts have different validation requirements.
     *
     * @param unknown $username (optional)
     * @param unknown $str      (optional)
     * @return unknown
     */
    public function validate_for_source($username=null, $str=null) {
        if (!isset($username)) {
            $username = $this->username;
        }
        if (!isset($str)) {
            $str = $this->phrase;
        }

        $methods = array(
            'length',
            'nousername',
            'alpha',
            'number'
        );

        foreach ($methods as $method) {
            $m = 'valid_' . $method;
            if (!$this->$m( $username, $str )) {
                //diag("$str failed: " . $this->error);
                return false;
            }
        }
        return true;

    }


    /**
     *
     *
     * @param unknown $username
     * @param unknown $str
     * @return unknown
     */
    public function valid_length($username, $str) {
        if (strlen($str) < $this->min_length) {
            $this->error = "must be at least " . $this->min_length . " characters long";
            return false;
        }
        return true;
    }



    /**
     *
     *
     * @param unknown $username
     * @param unknown $str
     * @return unknown
     */
    public function valid_nousername($username, $str) {

        // TODO
        return true;
    }


    /**
     *
     *
     * @param unknown $username
     * @param unknown $str
     * @return unknown
     */
    public function valid_alpha($username, $str) {
        if (!preg_match('/[a-zA-Z]/', $str)) {
            $this->error = "must contain a letter";
            return false;
        }
        return true;
    }


    /**
     *
     *
     * @param unknown $username
     * @param unknown $str
     * @return unknown
     */
    public function valid_punc($username, $str) {
        if ( !preg_match('/[\W]/', $str)) {
            $this->error = "must contain symbols";
            return false;
        }
        return true;
    }



    /**
     *
     *
     * @param unknown $username
     * @param unknown $str
     * @return unknown
     */
    public function valid_lower($username, $str) {
        if ( !preg_match('/[a-z]/', $str)) {
            $this->error = "must contain lowercase letter";
            return false;
        }
        return true;
    }



    /**
     *
     *
     * @param unknown $username
     * @param unknown $str
     * @return unknown
     */
    public function valid_upper( $username, $str) {
        if (!preg_match('/[A-Z]/', $str)) {
            $this->error = "must contain uppercase letter";
            return false;
        }
        return true;
    }



    /**
     *
     *
     * @param unknown $username
     * @param unknown $str
     * @return unknown
     */
    public function valid_number( $username, $str) {
        if (!preg_match('/[0-9]/', $str)) {
            $this->error = "must contain number";
            return false;
        }
        return true;
    }


}

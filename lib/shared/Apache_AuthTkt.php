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

class Apache_AuthTkt {

    private $secret;
    private $digest_type = 'md5';
    private $conf_file   = '/etc/auth_tkt.conf';
    private $err = array();
    private $tkt = '';
    private $default_opts = array(
        'ip'            => '0.0.0.0',
        'user'          => null,
        'ts'            => "",
        'tokens'        => "",
        'data'          => "",
        'base64'        => true,
    );
    private $encrypt_data = false;

    /**
     *
     *
     * @param unknown $config
     * @return unknown
     */


    public function Apache_AuthTkt($config=array()) {
        if (isset($config['secret'])) {
            $this->secret = $config['secret'];
        }
        else {
            $conf_file = $this->conf_file;
            if (isset($config['conf'])) {
                $conf_file = $config['conf'];
            }
            $buf = file_get_contents( $conf_file );
            $keyword = "TKTAuthSecret";
            $min_len = 10;
            $matches = array();

            if ( $buf === FALSE ) {
                throw new Exception("cannot read config file");
            }

            if ( preg_match( "/^\s*$keyword\s+\"(.*?)\"/m",
                    $buf, $matches ) ) {
                $this->secret = $matches[1];
            }

            if ( strlen( $this->secret ) < $min_len ) {
                throw new Exception("secret key too short: $buf");
            }

        }
        if (isset($config['digest_type'])) {
            $this->digest_type = $config['digest_type'];
        }
        if (isset($config['encrypt_data'])) {
            $this->encrypt_data = $config['encrypt_data'];
        }
        return $this;
    }



    /**
     *
     *
     * @return unknown
     */
    public function get_err() {
        return implode( "\n", $this->err);
    }



    /**
     *
     *
     * @param unknown $msg
     * @return unknown
     */
    private function set_err($msg) {
        array_push( $this->err, $msg );
        return $msg;
    }


    /*
 Returns a hashref representing the original ticket components
 Returns undef if there were any errors
*/

    /**
     *
     *
     * @param unknown $tkt
     * @param unknown $ip_address (optional)
     * @return unknown
     */
    public function validate_ticket($tkt=null, $ip_address='0.0.0.0') {

        // Parse ticket
        $info = $this->parse_ticket($tkt);

        // Validate digest
        $expected_digest = $this->get_digest(
            $info['ts'], $ip_address, $info['uid'],
            $info['tokens'], $info['data']);

        if ($expected_digest == $info['digest']) {
            return $info;
        }
        $this->set_err("digest mismatch: $expected_digest " . $info['digest']);
        return null;
    }


    /**
     *
     *
     * @param unknown $tkt
     * @return array $tkt_parts
     */
    public function parse_ticket($tkt=null) {

        if (!$tkt) {
            $tkt = $this->tkt;
        }
        else {
            $this->tkt = $tkt;  // cache
        }

        $parts = array();

        // Strip possible quotes
        preg_replace('/^"|"$/', '', $tkt);

        if (strlen($tkt) < 40) {
            $this->set_err("Ticket too short");
            return null;
        }

        // Assume ticket is not URL-escaped but may be base64-escaped
        $raw = $tkt;
        if (!preg_match('/!/', $tkt)) {
            $raw = base64_decode($tkt);
        }

        // If $raw still doesn't have ! then it is bogus
        if (!preg_match('/!/', $raw)) {
            $this->set_err("No ! in raw ticket");
            return null;
        }

        // Deconstruct
        $matches = array();
        if (!preg_match('/^(.{32})(.{8})(.+?)!(.*)$/', $raw, $matches)) {
            $this->set_err("No regex match for '$raw'");
            return null;
        }
        $parts['digest'] = $matches[1];
        $parts['ts']     = hexdec($matches[2]);
        $parts['uid']    = $matches[3];
        $parts['tokens'] = '';
        $parts['data']   = '';

        // Tokens and data if present
        if (isset($matches[4])) {
            if (preg_match('/!/', $matches[4])) {
                $tokens_data = explode('!', $matches[4]);
                $parts['tokens'] = $tokens_data[0];
                $parts['data']   = $this->encrypt_data
                    ? $this->_mdecrypt($tokens_data[1])
                    : $tokens_data[1];
            }
            else {
                $parts['data']   = $this->encrypt_data
                    ? $this->_mdecrypt($matches[4])
                    : $matches[4];
            }
        }
        return $parts;
    }


    /**
     *
     *
     * @param unknown $ts
     * @param unknown $ip_addr
     * @param unknown $uid
     * @param unknown $tokens
     * @param unknown $data
     * @return unknown
     */
    private function get_digest($ts, $ip_addr, $uid, $tokens, $data) {
        $ip = explode('.', $ip_addr);
        $ts_parts = array( (($ts & 0xff000000) >> 24),
            (($ts & 0xff0000) >> 16),
            (($ts & 0xff00) >> 8),
            (($ts & 0xff)) );
        $ipts = '';
        foreach ($ip as $octet) {
            $ipts .= pack("C1", $octet);
        }
        foreach ($ts_parts as $tsp) {
            $ipts .= pack("C1", $tsp);
        }
        if ($this->encrypt_data) {
            $data = $this->_mencrypt($data);
        }
        $raw = $ipts . $this->get_secret() . $uid . "\0" . $tokens . "\0" . $data;
        if ($this->digest_type == 'md5') {
            $digest0 = md5($raw);
            $digest  = md5($digest0 . $this->get_secret());
        }
        elseif ($this->digest_type == 'sha1') {
            $digest0 = sha1($raw);
            $digest  = sha1($digest0 . $this->get_secret());
        }
        return $digest;
    }


    /**
     *
     *
     * @param unknown $opts (optional)
     * @return unknown
     */
    public function create_ticket($opts=array()) {

        // flesh out $opts with defaults
        foreach ($this->default_opts as $key=>$value) {
            if (!isset($opts[$key])) {
                $opts[$key] = $value;
            }
        }

        // set the timestamp to now
        // unless a time is specified
        if ( empty($opts['ts']) ) {
            $opts['ts'] = time();
        }

        if ($opts['tokens']) {
            preg_replace('/\s+,/', ',', $opts['tokens']);
            preg_replace('/,\s+/', ',', $opts['tokens']);
        }

        if (!preg_match('/^([12]?[0-9]?[0-9]\.){3}[12]?[0-9]?[0-9]$/', $opts['ip'])) {
            $this->set_err("invalid IP address: " . $opts['ip']);
            return;
        }
        if (preg_match('/[!\s]/', $opts['tokens'])) {
            $this->set_err("invalid chars in tokens '" . $opts['tokens'] . "'");
            return;
        }

        $digest = $this->get_digest(
            $opts['ts'],
            $opts['ip'],
            $opts['user'],
            $opts['tokens'],
            $opts['data']
        );

        $ticket = sprintf( "%s%08x%s!", $digest, $opts['ts'], $opts['user'] );

        if (!empty($opts['tokens'])) {
            $ticket .= $opts['tokens'] . '!';
        }
        $ticket .= $this->encrypt_data
            ? $this->_mencrypt($opts['data'])
            : $opts['data'];

        if ( $opts['base64'] ) {
            return base64_encode( $ticket );
        } else {
            return $ticket;
        }
    }


    /**
     *
     *
     * @return type
     */
    public function get_digest_type() {
        if (!isset($this->digest_type)) {
            die("digest_type not set");
        }
        elseif ($this->digest_type == "md5" || $this->digest_type == "sha1") {
            return $this->digest_type;
        }
        else {
            die("Unsupported digest_type: " . $this->digest_type);
        }
    }


    /**
     *
     *
     * @return unknown
     */
    public function get_secret() {
        if (!isset($this->secret) || empty($this->secret)) {
            die("secret_key not set");
        }
        return $this->secret;
    }



    /**
     *
     *
     * @param unknown $s
     * @return unknown
     */
    private function _mencrypt($s) {
        $secret = $this->get_secret();
        while (strlen($secret) < 48) {
            $secret .= $secret;
        }
        $e = mcrypt_cbc(MCRYPT_RIJNDAEL_128, substr($secret, 0, 32) , $s, MCRYPT_ENCRYPT, substr($secret, 32, 16));
        return bin2hex($e);
    }



    /**
     *
     *
     * @param unknown $s
     * @return unknown
     */
    private function _mdecrypt($s) {
        $secret = $this->get_secret();
        while (strlen($secret) < 48) {
            $secret .= $secret;
        }
        $d = rtrim(mcrypt_cbc(MCRYPT_RIJNDAEL_128, substr($secret, 0, 32) , pack("H*", $s), MCRYPT_DECRYPT, substr($secret, 32, 16)), "\0");
        return $d;
    }


}


?>

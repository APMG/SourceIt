<?php

require_once 'IFDB_DBManager.php';

class IFDB_Exception extends Exception {

    private $message_args;


    /**
     *
     *
     * @return unknown
     */
    public function __toString() {
        return 'Error [' . $this->code . ']: ' . $this->message;
    }


    /**
     *
     *
     * @param unknown $message
     * @return unknown
     */
    public function set_message($message) {
        $this->message = $message;
        return $message;
    }


}

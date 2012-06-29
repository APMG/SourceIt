<?php

class Home_Controller extends IFDB_Controller {

    /**
     *
     */
    public function begin() {
        // no security here

    }


    /**
     *
     */
    public function index() {
        $this->response(array('body' => 'hello world'));
    }


}

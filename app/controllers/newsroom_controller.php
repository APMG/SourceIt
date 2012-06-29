<?php

require_once "IFDB_Newsroom.php";

/**
 *
 *
 * @author ktaborski
 * @package default
 */
class Newsroom_Controller extends IFDB_Controller {

    public $is_secure = false;


    /**
     * list all articles
     */
    public function GET_index() {
        // TODO get articles
        $newsrooms = IFDB_Newsroom::getAll();
        $this->response(array("records" => $newsrooms));
    }


    /**
     * get a specific newsroom by UUID
     *
     * @param string  $uuid
     */
    public function GET_index_id( $uuid ) {
        $newsroom = IFDB_Newsroom::find_by_url();
        $this->response(array("record" => $newsroom));
    }


}

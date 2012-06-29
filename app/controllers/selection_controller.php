<?php

require_once "IFDB_Selection.php";

/**
 *
 *
 * @author ktaborski
 * @package default
 */
class Selection_Controller extends IFDB_Controller {

    public $is_secure = false;

    /**
     *
     */
    /*public function begin() {
        // no security here
        // make sure to call the parent or init the DB
    }*/


    /**
     * list all selection
     *
     * @param unknown_type $article_uuid
     */
    public function GET_index($article_uuid) {
        $selection_service = new IFDB_Selection($article_uuid);
        $selections = $selection_service->getAll();
        $this->response(array("records" => $selections));
    }


    /**
     * create a new Selection
     *
     * @param unknown_type $article_uuid
     */
    public function create($article_uuid) {
        $selection_service = new IFDB_Selection($article_uuid);

        try {
            $selection = $selection_service->create($this->input->get_post('selection'));

            $this->response(
                array(
                    "message" => "Selection created.",
                    "record" => $selection,
                    "callback" => $this->input->get_post("callback")
                )
            );
        } catch (IFDB_Exception $e) {
            $this->response(
                array(
                    "message" => "Error creating selection: ".$e->getMessage().".",
                    "callback" => $this->input->get_post("callback")
                )
            );
        }
    }


    /**
     * get a specific selection
     *
     * @param unknown_type $id
     */
    public function GET_index_id( $id ) {
        // TODO get an Selection by id
        $selection = array();
        $this->response(array("record" => $selection));
    }


    /**
     * update a specific Selection
     *
     * @param unknown_type $id
     */
    public function PUT_index( $id ) {
        $this->response(
            array("message" => "Updating an Selection is not supported.")
        );
    }


    /**
     * delete a specific Selection
     *
     * @param unknown_type $id
     */
    public function DELETE_index($id) {
        $this->response(
            array("message" => "Deleting an Selection is not supported.")
        );
    }


    /* Not including methods/functions for the HTML for new or edit since this is a JSON API */
}

<?php

require_once "IFDB_Article.php";

/**
 *
 *
 * @author ktaborski
 * @package default
 */
class Article_Controller extends IFDB_Controller {

    public $is_secure = false;

    /**
     *
     */
    /*public function begin() {
        // no security here
        // make sure to call the parent or init the DB
    }*/


    /**
     * list all articles for a newsroom
     *
     * @param String  $newsroom_uuid (optional)
     */
    public function GET_index($newsroom_uuid=null) {
        $articles = array();
        if ($newsroom_uuid != null) {
            $articles = IFDB_Article::getArticlesByNewsroomUuid($newsroom_uuid);
        } else {
            // TODO return all articles in the system
        }
        $this->response(array("records" => $articles));
    }


    /**
     * create a new article
     */
    public function create() {
        try {
            $article = IFDB_Article::retrieveOrCreate($this->input->get_post("url"));
        }
        catch( IFDB_Exception $e ) {
            $this->response(
                array(
                    "message" => "Error creating article: ".$e->getMessage().".",
                    "callback" => $this->input->get_post("callback")
                )
            );
        }

        $this->response(
            array(
                "message" => "Article created.",
                "uuid" => $article->artcl_uuid,
                "callback" => $this->input->get_post("callback")
            )
        );
    }


    /**
     * get a specific articles
     *
     * @param unknown_type $id
     */
    public function GET_index_id( $id ) {
        // TODO get an article by id
        $article = array();
        $this->response(array("record" => $article));
    }


    /**
     * update a specific article
     *
     * @param unknown_type $id
     */
    public function PUT_index( $id ) {
        $this->response(
            array("message" => "Updating an article is not supported.")
        );
    }


    /**
     * delete a specific article
     *
     * @param unknown_type $id
     */
    public function DELETE_index($id) {
        $this->response(
            array("message" => "Deleting an article is not supported.")
        );
    }


    /* Not including methods/functions for the HTML for new or edit since this is a JSON API */
}

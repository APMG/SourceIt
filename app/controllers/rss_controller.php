<?php

require_once "IFDB_Article.php";

/**
 *
 *
 * @author ktaborski
 * @package default
 */
class Rss_Controller extends IFDB_Controller {

    public $is_secure = false;


    /**
     * intake an RSS and scrape all the articles
     */
    public function index() {

        if ($this->input->get_post("url", true) === false) {
            $this->response(
                array(
                    "message" => "Error: did not submit url parameter."
                )
            );

            return;
        }

        try {
            $rss_xml = simplexml_load_file($this->input->get_post("url", true));

            // get the root element
            $rss_channel = $rss_xml->children();

            // loop through the root looking for items, ie articles
            foreach ($rss_channel->children() as $item) {
                if ($item->getName() == "item") {
                    // get the link of an article and create it
                    foreach ($item->children() as $item_ele) {
                        if ($item_ele->getName() == "link") {
                            IFDB_Article::retrieveOrCreate((string)$item_ele);
                        }
                    }
                }
            }
        }
        catch( IFDB_Exception $e ) {
            $this->response(
                array(
                    "message" => "Error ingesting rss: ".$e->getMessage()."."
                )
            );
        }

        $this->response(
            array(
                "message" => "RSS ingested."
            )
        );
    }


}

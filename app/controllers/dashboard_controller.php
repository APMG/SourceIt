<?php

require_once "IFDB_Article.php";

/**
 *
 *
 * @author ktaborski
 * @package default
 */
class Dashboard_Controller extends IFDB_Controller {

    public $is_secure = false;


    /**
     * display the dashboard page for reports
     */
    public function index() {
        $newsrooms = array();
        $body = $this->load->view(
            "dashboard/index",
            array(
                "c" => $this,
                "newsrooms" => $newsrooms,
            ),
            true
        );

        $this->response(array(
                "body" => $body,
                "head" => array(
                    "css" => array(
                        "bootstrap",
                        "dashboard"
                    )
                ),
                "foot" => array(
                    "js" => array(
                        "jquery-1.7.1.min.js",
                        "underscore-min.1.2.1.js",
                        "backbone-min.js",
                        "mustache.0.4.0-dev.js",
                        "dashboard.js"
                    ),
                    /*"ready" => array(
                        '$.MyPIN2.ProfileCompleteness.ready',
                        'function(){ social_media_link_toggles() }',
                        '$.MyPIN2.Query.ready',
                    ),
                    "inline_js" => '<script type="text/javascript">var REMOVING_QUERY_AJAX = "'.$this->translate("remove ajax").'";</script>',*/
                )
            )
        );
    }


}

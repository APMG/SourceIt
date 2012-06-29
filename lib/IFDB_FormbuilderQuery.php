<?php

require_once "IFDB_Exception.php";

/**
 *
 *
 * @author ktaborski
 * @package default
 */
class IFDB_FormbuilderQuery {


    /**
     *
     *
     * @var Comment
     */
    private $comment;


    /**
     *
     *
     * @var string
     */
    public $response;


    /**
     *
     *
     * @var array
     */
    public $response_info;


    /**
     * Needs the form that will be sent to formbuilder.
     *
     * @param Comment $comment
     */
    public function __construct($comment) {
        $this->comment = $comment;
    }


    /**
     * Send the comment to Formbuilder.
     *
     * @throws IFDB_Exception
     */
    public function submit() {
        $args = $this->convertCommentToFB();

        /* Here we build the data we want to POST to Formbuilder */
        $data = "";
        foreach ($args as $key => $value) {
            $data .= ($data != "") ? "&" : "";
            $data .= urlencode($key)."=".urlencode($value);
        }

        /* Initialisation of curl */
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, FORMBUILDER_COMMENT_URL);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // get and store the response and the respons info
        $this->response = curl_exec($ch);
        $this->response_info = curl_getinfo($ch);
        curl_close($ch);
    }


    /**
     * 200 means there was a validation error
     * 302 means the form submitted and redirected to the thank you page
     *
     * @return String http status code
     */
    public function status() {
        return $this->response_info["http_code"];
    }


    /**
     *
     *
     * @return array
     */
    private function convertCommentToFB() {
        $fb_array = array();

        $fb_array["ctb_first_name"] = $this->comment->cmmnt_first_name;
        $fb_array["ctb_last_name"] = $this->comment->cmmnt_last_name;
        $fb_array["ctb_email"] = $this->comment->cmmnt_email;
        $fb_array["ctb_zipcode"] = $this->comment->cmmnt_zipcode;
        $fb_array["uptodate_children[ctb_ask_response_dtl][0][card_value]"] = $this->comment->cmmnt_comment;
        //$fb_array["uptodate_children[ctb_ask_response_dtl][1][card_value][]"] = ; //keywords
        $fb_array["uptodate_children[ctb_ask_response_dtl][2][card_value][]"] = $this->comment->Selection->Article->artcl_url;
        $fb_array["uptodate_children[ctb_ask_response_dtl][3][card_value][]"] = $this->comment->Selection->slctn_value;
        $fb_array["uptodate_children[ctb_ask_response_dtl][4][card_value][]"] = $this->comment->cmmnt_private;
        //$fb_array["uptodate_children[ctb_ask_response_dtl][5][card_value][]"] = $this->comment->cmmnt_type;

        return $fb_array;
    }


}

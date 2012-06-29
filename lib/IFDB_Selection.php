<?php

require_once "IFDB_Exception.php";
require_once "IFDB_Article.php";

/**
 *
 *
 * @author ktaborski
 * @package default
 */
class IFDB_Selection {


    /**
     *
     *
     * @var Article
     */
    public $article = null;


    /**
     *
     *
     * @param string  $article_uuid
     */
    public function __construct($article_uuid) {
        $this->article = IFDB_Article::find_by_uuid($article_uuid);
    }


    /**
     * return selections with their comments
     *
     * @return Array of selections
     */
    public function getAll() {
        $selections = IFDB_Query::create()
        ->from("Selection s")
        ->where("s.slctn_artcl_id = ?", $this->article->artcl_id);

        return $selections->execute()->toArray();
    }


    /**
     *
     *
     * @param String  $user_selection
     * @return Selection created
     */
    public function create( $user_selection) {
        // check to see if it exists or overlaps with another quote, already exists, is a valid length, etc.
        $this->isSelectionValid($user_selection);

        // check to see if this sentence exists in the page
        $this->isSelectionInArticle($this->article, $user_selection);

        // create new selection
        $selection = self::storeSelection(Selection::$USER_SELECTION, $this->article, $user_selection);

        return $selection->toArray();
    }


    /**
     *
     *
     * @param string  $selection
     * @return Selection
     */
    public static function findByValue($selection) {
        return Doctrine::getTable("Selection")
        ->findOneBy("slctn_value", $selection);
    }


    /**
     * Find an Selection by uuid.
     *
     * @param string  $uuid
     * @return $selection IFDB selection object
     */
    public static function find_by_uuid($uuid) {
        return Doctrine::getTable("Selection")
        ->findOneBy("slctn_uuid", $uuid);
    }


    /**
     *
     *
     * @param Article $article
     */
    public static function initialSelections($article) {
        // convert the article into a string of the paragraph tags, ie the article content
        $article_content = IFDB_Article::extractArticleBody($article, false);

        // quotes
        self::createAndStoreQuotes($article_content, $article);

        // entities
        // TODO

        // numbers
        self::createAndStoreNumberSentence($article_content, $article);
    }


    /**
     *
     *
     * @param Article $article
     * @param String  $user_selection
     * @return boolean true => string existed in the article
     */
    private function isSelectionInArticle($article, $user_selection) {
        $article_content = IFDB_Article::extractArticleBody($article, false);

        if (strstr($article_content, $user_selection) !== false) {
            return true;
        } else {
            throw new IFDB_Exception("Selection doesn't exist in article");
        }
    }


    /**
     *
     *
     * @param String  $user_selection
     * @return boolean true => string existed in the article
     */
    private function isSelectionValid($user_selection) {
        // does a selection with this text exist
        if (self::findByValue($user_selection) !== false) {
            throw new IFDB_Exception("Selection exists");
        }

        // is the selection valid, ie in a specific paragraph, contains three spaces, etc.
        if (count(split(" ", $user_selection)) < 4) {
            throw new IFDB_Exception("Selection isn't valid");
        }

        // TODO does a selection overlap another selection
        /*if (strstr($article_content, $user_selection) === false) {
            throw new IFDB_Exception("Selection overlaps another selection");
        }*/

        return true;
    }


    /**
     *
     *
     * @param unknown_type $content
     * @param unknown_type $article
     */
    private static function createAndStoreQuotes($content, $article) {
        $quote_matches = array();

        // define utf-8 directional quotes
        $smart_left_quote  = Encoding::uchr(0x201c);
        $smart_right_quote = Encoding::uchr(0x201d);

        // get all strings from the articles that have start and ends
        preg_match_all("/(&ldquo;|\"|".$smart_left_quote.").*(&rdquo;|\"|".$smart_right_quote.")/U", $content, $quote_matches);

        // define a quote array for the scrubbing
        $quotes = array($smart_left_quote, $smart_right_quote, '"', "&ldquo;", "&rdquo;");

        // scrub quote selections and store them
        foreach ($quote_matches[0] as $match) {
            // remove start and end quotes
            $match = str_replace($quotes, "", $match);

            // TODO make sure apostrphees in quotes are handeled

            self::storeSelection(Selection::$QUOTE, $article, $match);
        }
    }


    /**
     *
     *
     * @param unknown_type $content
     * @param unknown_type $article
     */
    private static function createAndStoreNumberSentence($content, $article) {
        // get sentences
        $re = '/(?<=[.!?]|[.!?][\'"])\s+/';
        $sentences = preg_split($re, $content, -1, PREG_SPLIT_NO_EMPTY);

        // check each sentence for dollar or percent
        foreach ($sentences as $to_check) {
            if (preg_match("/[$]{1}[0-9.,]+/", $to_check) || preg_match("/[0-9\.,]+(\%{1}|( percent){1})/", $to_check)) {
                $match = trim($to_check);
                self::storeSelection(Selection::$STATISTIC, $article, $match);
            }
        }
    }


    /**
     * Pass in a result from a selection with it's type and store it.
     *
     * @param unknown_type $type
     * @param string  $article
     * @param string  $data
     * @return Selection
     */
    private static function storeSelection($type, $article, $data) {
        $selection = new Selection();
        $selection->slctn_type = $type;
        $selection->slctn_value = $data;
        $selection->Article = $article;
        $selection->save();

        return $selection;
    }


}

<?php

require_once "IFDB_Exception.php";

/**
 *
 *
 * @author ktaborski
 * @package default
 */
class IFDB_Newsroom {


    /**
     * Find Newsroom by name.
     *
     * @param String  $name
     * @return Newsroom
     */
    public static function find_by_name($name) {
        return Doctrine::getTable("Newsroom")
        ->findOneBy("nwsrn_name", $name);
    }


    /**
     * Find Newsroom by url.
     *
     * @param String  $url
     * @return Newsroom
     */
    public static function find_by_url($url) {
        return Doctrine::getTable("Newsroom")
        ->findOneBy("nwsrn_url", $url);
    }


    /**
     * Find Newsroom by uuid.
     *
     * @param String  $uuid
     * @return Newsroom
     */
    public static function find_by_uuid($uuid) {
        return Doctrine::getTable("Newsroom")
        ->findOneBy("nwsrn_uuid", $uuid);
    }


    /**
     * return all newsrooms
     *
     * @return Array of selections
     */
    public function getAll() {
        $newsrooms = IFDB_Query::create()
        ->from("Newsroom n");

        return $newsrooms->execute()->toArray();
    }


    /**
     * get the newsroom from the article content and associate it to the article
     *
     * @param Article $article
     */
    public static function createArticleNewsroom($article) {
        // parse the HTML DOM to get the newsroom name
        $newsroom_name = self::getName($article->artcl_content);

        // parse the URL to get the newsroom
        $newsroom_url = self::getSiteUrl($article->artcl_url);

        // get the newsroom or create if it doesn't exist
        $newsroom = self::getOrCreate($newsroom_name, $newsroom_url);

        // assocaite article to the newsroom
        if ($newsroom) {
            self::assoiate($article, $newsroom);
        }
    }


    /**
     * parse the HTML DOM to get the newsroom name
     * check a few sites and saw og:site_name as the only why sites are easilly
     * accessibly including their name in the dom.
     *
     * @param String  $article_content
     * @return String author name
     */
    private static function getName($article_content) {
        // create DOM object from string and be less strict on parsing
        $DOM = new DOMDocument;
        libxml_use_internal_errors(true);
        $DOM->loadHTML($article_content);

        $newsroom_name = '';

        // look for it as open graph
        $metaChildren = $DOM->getElementsByTagName("meta");
        for ($i = 0; $i < $metaChildren->length; $i++) {
            $el = $metaChildren->item($i);
            if ($el->getAttribute("property") == "og:site_name") {
                $newsroom_name = $el->getAttribute("content");
            }
        }

        return $newsroom_name;
    }


    /**
     * Parse the article url to get the newsroom URL
     *
     * @param String  $article_url
     * @return String newsroom url
     */
    private static function getSiteUrl($article_url) {
        // look for the first slash after the http:// and grap until that position
        return substr($article_url, 0, strpos($article_url, "/", 8)+1);
    }


    /**
     * Get the newsroom record if it exists, otherwise create one.
     *
     * @param String  $name
     * @param String  $url
     * @return $newsroom
     */
    private static function getOrCreate($name, $url) {
        $newsroom = self::find_by_name($name);

        if (!$newsroom) {
            $newsroom = self::find_by_url($url);

            if (!$newsroom) {
                $newsroom = self::newNewsroom($name, $url);
            }
        }

        return $newsroom;
    }


    /**
     * Associate an article with its newsroom.
     *
     * @param Article $article
     * @param Newsroom $newsroom
     */
    private static function assoiate($article, $newsroom) {
        $article->Newsroom = $newsroom;
        $article->save();
    }


    /**
     * Create Newsroom, set name and url.
     *
     * @param string  $name
     * @param string  $url
     * @return $newsroom IFDB Newsroom object
     */
    private static function newNewsroom($name, $url) {
        $newsroom = new Newsroom();
        $newsroom->nwsrn_name = $name;
        $newsroom->nwsrn_url = $url;
        $newsroom->save();

        return $newsroom;
    }


}

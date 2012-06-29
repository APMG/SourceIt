<?php

require_once "IFDB_Exception.php";
require_once "IFDB_SemanticResults.php";
require_once "IFDB_Selection.php";
require_once "IFDB_Author.php";
require_once "IFDB_Newsroom.php";

/**
 *
 *
 * @author ktaborski
 * @package default
 */
class IFDB_Article {


    /**
     * Find an Article record by uuid.
     *
     * @param string  $uuid
     * @return $article IFDB article object
     */
    public static function find_by_uuid($uuid) {
        return Doctrine::getTable("Article")
        ->findOneBy("artcl_uuid", $uuid);
    }


    /**
     * Find an Article record by url.
     *
     * @param string  $url
     * @return $article IFDB article object
     */
    public static function find_by_url($url) {
        return Doctrine::getTable("Article")
        ->findOneBy("artcl_url", $url);
    }


    /**
     * Find an Article record by url_md5.
     *
     * @param string  $url_md5
     * @return $article IFDB article object
     */
    public static function find_by_url_md5($url_md5) {
        return Doctrine::getTable("Article")
        ->findOneBy("artcl_url_md5", $url_md5);
    }


    /**
     * return articles belonging to a newsroom by its uuid
     *
     * @param String  $uuid
     * @return Array of articles
     */
    public static function getArticlesByNewsroomUuid($uuid) {
        $articles = IFDB_Query::create()
        ->select("a.*")
        ->from("Article a")
        ->innerJoin("a.Newsroom n")
        ->where("n.nwsrn_uuid = ?", $uuid);

        return $articles->execute()->toArray();
    }


    /**
     * If the article exists return it.
     * If the article doesn't exist, create it and populate it.
     *
     * The population is scaping and storing the HTML to the model and sending
     * the artilce text to semantic engines and storing those results.
     *
     * @param string  $url
     * @return $article IFDB article object
     */
    public static function retrieveOrCreate($url) {
        $article = self::find_by_url($url);

        if ( !$article ) {
            $article = self::newArticle($url);
            $article = self::populateArticle($article);
        }

        return $article;
    }


    /**
     * Create the article and set the URL.
     * Doesn't scrape article or anything like that.
     *
     * @param string  $url
     * @return $article IFDB article object
     */
    private static function newArticle($url) {
        $article = new Article();
        $article->artcl_url = $url;
        $article->artcl_url_md5 = md5($url);
        $article->save();

        return $article;
    }


    /**
     * Scrapes and stores the article.
     * Runs the sematic stuff and stores that.
     *
     * @param Article $article
     * @return $article IFDB article object
     */
    private static function populateArticle($article) {
        //get article content
        $page_html = self::getArticleHTML($article->artcl_url);

        // TODO article title
        $article->artcl_title = self::getArticleTitle($page_html);

        // store and save
        $article->artcl_content = $page_html;
        $article->save();

        IFDB_SemanticResults::addZemanta($article);

        IFDB_Selection::initialSelections($article);

        // TODO populate author table
        IFDB_Author::createArticleAuthor($article);

        // TODO populate newsroom table
        IFDB_Newsroom::createArticleNewsroom($article);

        return $article;
    }


    /**
     *
     *
     * @throws IFDB_Exception
     * @param string  $url
     * @return $page string of article HTML
     */
    private static function getArticleHTML($url) {
        // hack for staging
        if (strpos($url, "stage.publicinsightnetwork.org") !== false) {
            if (strpos($url, "cir_wal_mart.html") !== false) {
                $url = "http://californiawatch.org/money-and-politics/wal-mart-ramps-ballot-threats-speed-new-stores-13678";
            }
            if (strpos($url, "cir_english.html") !== false) {
                $url = "http://californiawatch.org/k-12/english-learners-still-far-behind-using-immersion-methods-13161";
            }
            if (strpos($url, "cir_funding.html") !== false) {
                $url = "http://californiawatch.org/dailyreport/whitman-former-finance-chair-romney-gives-little-funding-time-14897";
            }
        }

        $page = file_get_contents( $url );

        if ( !$page ) {
            $page = file_get_contents( urldecode($url) );
        }
        if ( !$page ) {
            throw new IFDB_Exception("Could not fetch file by URL.");
        }

        return $page;
    }


    /**
     * Return the values of all the header(hX) and paragraph(p) elements of an
     * article as one string.
     *
     * @param Article $article
     * @param boolean $headers (optional)
     * @return string
     */
    public static function extractArticleBody($article, $headers=true) {
        $full_text = $article->artcl_content;

        // create DOM object from string and be less strict on parsing
        $DOM = new DOMDocument;
        libxml_use_internal_errors(true);
        $DOM->loadHTML($full_text);

        // only grab the h1 and p elements, trying to only get article content, simple version
        $body_content = '';
        if ($headers) {
            foreach ( $DOM->getElementsByTagName('h1') as $element ) {
                $body_content .= $element->nodeValue.' ';
            }
            foreach ( $DOM->getElementsByTagName('h2') as $element ) {
                $body_content .= $element->nodeValue.' ';
            }
            foreach ( $DOM->getElementsByTagName('h3') as $element ) {
                $body_content .= $element->nodeValue.' ';
            }
            foreach ( $DOM->getElementsByTagName('h4') as $element ) {
                $body_content .= $element->nodeValue.' ';
            }
            foreach ( $DOM->getElementsByTagName('h5') as $element ) {
                $body_content .= $element->nodeValue.' ';
            }
            foreach ( $DOM->getElementsByTagName('h6') as $element ) {
                $body_content .= $element->nodeValue.' ';
            }
        }
        foreach ( $DOM->getElementsByTagName('p') as $element ) {
            $body_content .= $element->nodeValue.' ';
        }

        return $body_content;
    }


    /**
     * get the article title from the html
     * uses the meta facebook open graph tag then the title tag
     *
     * @param string  $article_content
     * @return string article title
     */
    public static function getArticleTitle($article_content) {
        // create DOM object from string and be less strict on parsing
        $DOM = new DOMDocument;
        libxml_use_internal_errors(true);
        $DOM->loadHTML($article_content);

        $article_title = '';

        // Facebook's open graph doesn't support author
        $metaChildren = $DOM->getElementsByTagName("meta");
        for ($i = 0; $i < $metaChildren->length; $i++) {
            $el = $metaChildren->item($i);
            if ($el->getAttribute("property") == "og:title") {
                $article_title = $el->getAttribute("content");
            }
        }

        // not looking for it as a class of a header element, because that doesn't seem accurate and most have it in the open grach

        // look for it as meta data
        if ($article_title == '') {
            $metaChildren = $DOM->getElementsByTagName("title");
            for ($i = 0; $i < $metaChildren->length; $i++) {
                $el = $metaChildren->item($i);
                $article_title = $el->nodeValue;
            }
        }

        return $article_title;
    }


}

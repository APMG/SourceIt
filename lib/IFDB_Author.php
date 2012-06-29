<?php

require_once "IFDB_Exception.php";

/**
 *
 *
 * @author ktaborski
 * @package default
 */
class IFDB_Author {


    /**
     * Find Author by name.
     *
     * @param String  $name
     * @return Author
     */
    public static function find_by_name($name) {
        return Doctrine::getTable("Author")
        ->findOneBy("athr_name", $name);
    }


    /**
     * get the author from the article content and associate it to the article
     *
     * @param Article $article
     */
    public static function createArticleAuthor($article) {
        // parse the HTML DOM to get the author name
        $author_name = self::getName($article->artcl_content);

        // get the author or create if it doesn't exist
        $author = self::getOrCreate($author_name);

        // assocaite article to the author
        if ($author) {
            self::assoiate($article, $author);
        }
    }


    /**
     * parse the HTML DOM to get the author name
     *
     * @param String  $article_content
     * @return String author name
     */
    private static function getName($article_content) {
        // create DOM object from string and be less strict on parsing
        $DOM = new DOMDocument;
        libxml_use_internal_errors(true);
        $DOM->loadHTML($article_content);

        $author_name = '';

        // Facebook's open graph doesn't support author

        // look for it as meta data
        $metaChildren = $DOM->getElementsByTagName("meta");
        for ($i = 0; $i < $metaChildren->length; $i++) {
            $el = $metaChildren->item($i);
            if ($el->getAttribute("name") == "author") {
                $author_name = $el->getAttribute("content");
            }
        }

        // look for it as a class
        if ($author_name == '') {
            $metaChildren = $DOM->getElementsByTagName("span");
            for ($i = 0; $i < $metaChildren->length; $i++) {
                $el = $metaChildren->item($i);
                if (strpos($el->getAttribute("class"), "author") !== false) {
                    $author_name = $el->nodeValue;
                }
            }
        }

        return $author_name;
    }


    /**
     * Get the author record if it exists, otherwise create one.
     *
     * @param String  $author_name
     * @return $author
     */
    private static function getOrCreate($author_name) {
        $author = self::find_by_name($author_name);

        if (!$author) {
            $author = self::newAuthor($author_name);
        }

        return $author;
    }


    /**
     * Associate an article with its author.
     *
     * @param Article $article
     * @param Author  $author
     */
    private static function assoiate($article, $author) {
        $article->Author = $author;
        $article->save();
    }


    /**
     * Create Author, set name.
     *
     * @param string  $name
     * @return $author IFDB author object
     */
    private static function newAuthor($name) {
        $author = new Author();
        $author->athr_name = $name;
        $author->save();

        return $author;
    }


}

<?php

// require_once "simplepie/simplepie.inc";

// require_once dirname(__FILE__) . '/magpie/rss_fetch.inc';

// define('MAGPIE_CACHE_DIR', '/tmp/source/rsscache');
// define('MAGPIE_OUTPUT_ENCODING', 'UTF-8');  // IMPORTANT
// define('MAGPIE_CACHE_ON', true);

/**
 * RSS library.
 *
 * @author sgilbertson
 * @package default
 */
class Rss {

    /**
     * Filter a URL argument so it's safe and won't crash cURL.
     *
     * Note: This method is only meant as a callback, used internally
     * in this library.
     *
     * @deprecated I have no idea if this is used.
     * @param unknown_type $matches
     * @return string
     */
    public function filter_url_arg( $matches ) {
        return $matches[1] . $matches[2] . "=" . urlencode($matches[3]);
    }


    /**
     * Read an RSS feed, and return its items.
     *
     * @param unknown $uri
     * @return array
     * */
    public function read($uri) {
        $xml = new XMLReader();
        $xml->xml(file_get_contents($uri));

        $items = array();

        $cur_item = null;
        $in_item = false;
        $cur_node = null;
        while ($xml->read()) {
            if ($xml->nodeType === XMLReader::ELEMENT && $xml->name === "item") {
                $cur_item = new stdClass();
                $cur_item->headline = null;
                $cur_item->body = null;
                $cur_item->uri = null;
                $cur_item->pub_date = null;
                $cur_item->category = null;

                $in_item = true;
            }
            elseif ($xml->nodeType === XMLReader::ELEMENT) {
                $cur_node = $xml->name;
            }
            elseif (($xml->nodeType === XMLReader::TEXT || $xml->nodeType === XMLReader::CDATA)
                && $in_item === true) {
                switch ($cur_node) {
                case "title":
                    $cur_item->headline = $xml->value;
                    break;

                case "description":
                    $cur_item->body = $xml->value;
                    break;

                case "link":
                    $cur_item->uri = $xml->value;
                    break;

                case "pubDate":
                    $cur_item->pub_date = $xml->value;
                    break;

                case "category":
                    $cur_item->category = $xml->value;
                    break;

                default:
                    break;
                }
            }
            elseif ($xml->nodeType === XMLReader::END_ELEMENT
                && $xml->name === "item") {
                $items []= $cur_item;
                $in_item = false;
            }
        }

        return $items;
    }


}

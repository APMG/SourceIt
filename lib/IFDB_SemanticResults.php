<?php

require_once "IFDB_Exception.php";

/**
 *
 *
 * @author ktaborski
 * @package default
 */
class IFDB_SemanticResults {


    /**
     * Contact Zemanta with article content.
     * Store the Zemanta data.
     * Convert the Zemanta data into keywords for the article and store them.
     *
     * @param Article $article to add Zemanta Data to
     */
    public static function addZemanta($article) {
        // contact zemanta
        $zemanta_data = self::getZemantaAnalysis($article->artcl_content);

        // store semantic results
        self::storeSemtanticResult(SemanticResult::$ZEMANTA, $article, $zemanta_data);

        // store keyword results
        self::storeZemantaKeywords($article, $zemanta_data);
    }


    /**
     * Pass in a result from a semantic engine with it's type and store it.
     *
     * @param unknown_type $type
     * @param string  $article
     * @param string  $data
     */
    private static function storeSemtanticResult($type, $article, $data) {
        $sr = new SemanticResult();
        $sr->smrslt_type = $type;
        $sr->smrslt_content = $data;
        $sr->Article = $article;
        $sr->save();
    }


    /**
     * Send a string to Zemanta and return the analysis.
     *
     * @throws IFDB_Exception
     * @param string  $page_html
     * @return string Zemanta results as a JSON string
     */
    private static function getZemantaAnalysis($page_html) {
        $url = "http://api.zemanta.com/services/rest/0.0/";
        $args = array(
            "method"=> "zemanta.suggest",
            "api_key"=> "hvdtup4coeoqhuzqgeaza3me",
            "text"=> $page_html,
            "format"=> "json"
        );

        /* Here we build the data we want to POST to Zementa */
        $data = "";
        foreach ($args as $key => $value) {
            $data .= ($data != "") ? "&" : "";
            $data .= urlencode($key)."=".urlencode($value);
        }


        /* Initialisation of curl */
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }


    /**
     * Associate the entities from Zemanta analysis to an article.
     *
     * @param Article $article
     * @param String  $resposne JSON formated
     */
    private static function storeZemantaKeywords($article, $resposne) {
        $json = json_decode($resposne, true);

        foreach ($json["keywords"] as $keyword) {
            self::storeKeyword($article, $keyword["name"], $keyword["confidence"], Entity::$ZEMANTA);
        }
    }


    /**
     * Pass in the entity from a semantic engine with it's type and store it
     * as an entity.
     *
     * @param unknown_type $article
     * @param unknown_type $value
     * @param unknown_type $confidence
     * @param unknown_type $type
     */
    private static function storeKeyword($article, $value, $confidence, $type) {
        $entt = new Entity();
        $entt->entt_value = $value;
        $entt->entt_confidence = $confidence;
        $entt->entt_type = $type;
        $entt->Article = $article;
        $entt->save();
    }


}

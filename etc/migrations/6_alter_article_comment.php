<?php

/**
 *
 *
 * @author ktaborski
 * @package default
 */
class AlterArticleAndComment extends Doctrine_Migration_Base {


    /**
     * adding columns
     * and fixing cmmnt_private's blank status
     */
    public function up() {
        // Article columns
        $this->addColumn('article', 'artcl_title', 'string', 1000, array());

        // Comment Columns
        $this->addColumn('comment', 'cmmnt_sentiment', 'integer', 1, array('default' => -1, 'range' => array(-1, 100)));
        $this->addColumn('comment', 'cmmnt_accuracy', 'integer', 1, array('default' => -1, 'range' => array(-1, 100)));
    }


    /**
     *
     */
    public function down() {
        // Article columns
        $this->removeColumn('article', 'artcl_title');

        // Comment Columns
        $this->removeColumn('comment', 'cmmnt_sentiment');
        $this->removeColumn('comment', 'cmmnt_accuracy');
    }


}

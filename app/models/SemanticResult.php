<?php

require_once "IFDB_Exception.php";

class SemanticResult extends IFDB_Record {


    // Semantic Result Type => Zemanta
    public static $ZEMANTA = 'Z';


    /**
     * Custom AIR2 validation before update/save. Sets
     * timestamps and uuids among other things.
     *
     * @param Doctrine_Event $event
     */
    public function preValidate($event) {
        air2_model_prevalidate($this);
    }


    /**
     *
     */
    public function setTableDefinition() {
        $this->setTableName('semantic_result');
        $this->option('type', 'INNODB');

        $this->hasColumn(
            'smrslt_id',
            'integer',
            4,
            array(
                'primary' => true,
                'autoincrement' => true
            )
        );

        $this->hasColumn(
            'smrslt_uuid',
            'string',
            12,
            array(
                'notnull' => true,
                'notblank' => true,
                'unique' => true,
                'fixed' => true,  // char instead of varchar.
            )
        );

        $this->hasColumn(
            'smrslt_content',
            'clob'
        );

        $this->hasColumn(
            'smrslt_type',
            'string',
            1
        );

        // relations
        $this->hasColumn(
            'smrslt_artcl_id',
            'integer',
            4
        );

        // timestamps
        $this->hasColumn(
            'smrslt_cre_dtim',
            'timestamp',
            null,
            array(
                'notnull' => true
            )
        );

        $this->hasColumn(
            'smrslt_upd_dtim',
            'timestamp',
            null,
            array()
        );
    }


    /**
     * Doctrine method. Meant to set up model relationships, etc.
     */
    public function setUp() {
        parent::setUp();
        $this->hasOne('Article', array(
                'local' => 'smrslt_artcl_id',
                'foreign' => 'artcl_id'
            )
        );
    }


}

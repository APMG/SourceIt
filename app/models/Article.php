<?php

require_once "IFDB_Exception.php";

class Article extends IFDB_Record {

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
        $this->setTableName('article');
        $this->option('type', 'INNODB');

        $this->hasColumn(
            'artcl_id',
            'integer',
            4,
            array(
                'primary' => true,
                'autoincrement' => true
            )
        );

        $this->hasColumn(
            'artcl_uuid',
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
            'artcl_title',
            'string',
            1000
        );

        $this->hasColumn(
            'artcl_url',
            'string',
            2083,
            array('notnull' => true, 'notblank' => true)
        );

        $this->hasColumn(
            'artcl_url_md5',
            'string',
            2083,
            array('notnull' => true, 'notblank' => true)
        );

        $this->hasColumn(
            'artcl_content',
            'clob'
        );

        // relations
        $this->hasColumn(
            'artcl_athr_id',
            'integer',
            4
        );

        $this->hasColumn(
            'artcl_nwsrn_id',
            'integer',
            4
        );

        // time stamps
        $this->hasColumn(
            'artcl_cre_dtim',
            'timestamp',
            null,
            array(
                'notnull' => true
            )
        );

        $this->hasColumn(
            'artcl_upd_dtim',
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
        $this->hasOne('Author', array(
                'local' => 'artcl_athr_id',
                'foreign' => 'athr_id'
            )
        );
        $this->hasOne('Newsroom', array(
                'local' => 'artcl_nwsrn_id',
                'foreign' => 'nwsrn_id'
            )
        );
        $this->hasMany('SemanticResult as SemanticResults', array(
                'local' => 'artcl_id',
                'foreign' => 'smrslt_artcl_id'
            )
        );
        $this->hasMany('Selection as Selections', array(
                'local' => 'artcl_id',
                'foreign' => 'slctn_artcl_id'
            )
        );
        $this->hasMany('Entity as Entities', array(
                'local' => 'artcl_id',
                'foreign' => 'entt_artcl_id'
            )
        );
    }


}

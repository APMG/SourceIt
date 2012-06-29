<?php

require_once "IFDB_Exception.php";

class Author extends IFDB_Record {

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
        $this->setTableName('author');
        $this->option('type', 'INNODB');

        $this->hasColumn(
            'athr_id',
            'integer',
            4,
            array(
                'primary' => true,
                'autoincrement' => true
            )
        );

        $this->hasColumn(
            'athr_uuid',
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
            'athr_name',
            'string',
            255,
            array('notnull' => true, 'notblank' => true)
        );

        // relations

        // timestamps
        $this->hasColumn(
            'athr_cre_dtim',
            'timestamp',
            null,
            array(
                'notnull' => true
            )
        );

        $this->hasColumn(
            'athr_upd_dtim',
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
        $this->hasMany('Article as Articles', array(
                'local' => 'athr_id',
                'foreign' => 'artcl_athr_id'
            )
        );
    }


}

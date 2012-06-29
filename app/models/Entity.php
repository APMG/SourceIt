<?php

require_once "IFDB_Exception.php";

class Entity extends IFDB_Record {

    // Entity Type => Zemanta
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
        $this->setTableName('entity');
        $this->option('type', 'INNODB');

        $this->hasColumn(
            'entt_id',
            'integer',
            4,
            array(
                'primary' => true,
                'autoincrement' => true
            )
        );

        $this->hasColumn(
            'entt_uuid',
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
            'entt_value',
            'string',
            255,
            array('notnull' => true, 'notblank' => true)
        );

        $this->hasColumn(
            'entt_type',
            'string',
            1
        );

        $this->hasColumn(
            'entt_confidence',
            'string',
            32
        );

        // relations
        $this->hasColumn(
            'entt_artcl_id',
            'integer',
            4
        );

        // timestamps
        $this->hasColumn(
            'entt_cre_dtim',
            'timestamp',
            null,
            array(
                'notnull' => true
            )
        );

        $this->hasColumn(
            'entt_upd_dtim',
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
                'local' => 'entt_artcl_id',
                'foreign' => 'artcl_id'
            )
        );
    }


}

<?php

require_once "IFDB_Exception.php";

class Selection extends IFDB_Record {


    // Selection Type => quote
    public static $QUOTE = 'Q';


    // Selection Type => user selection
    public static $USER_SELECTION = 'U';


    // Selection Type => number or statistic
    public static $STATISTIC = 'S';


    // Selection Type => entity
    public static $ENTITY = 'E';


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
        $this->setTableName('selection');
        $this->option('type', 'INNODB');

        $this->hasColumn(
            'slctn_id',
            'integer',
            4,
            array(
                'primary' => true,
                'autoincrement' => true
            )
        );

        $this->hasColumn(
            'slctn_uuid',
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
            'slctn_value',
            'string',
            9999,
            array('notnull' => true, 'notblank' => true)
        );

        $this->hasColumn(
            'slctn_type',
            'string',
            1
        );

        // relations
        $this->hasColumn(
            'slctn_artcl_id',
            'integer',
            4
        );

        // timestamps
        $this->hasColumn(
            'slctn_cre_dtim',
            'timestamp',
            null,
            array(
                'notnull' => true
            )
        );

        $this->hasColumn(
            'slctn_upd_dtim',
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
                'local' => 'slctn_artcl_id',
                'foreign' => 'artcl_id'
            )
        );
        $this->hasMany('Comment as Comments', array(
                'local' => 'slctn_id',
                'foreign' => 'cmmnt_slctn_id'
            )
        );
        // TODO setup entity relations
    }


}

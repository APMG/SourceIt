<?php

require_once "IFDB_Exception.php";

class Comment extends IFDB_Record {


    // Comment Status => active
    public static $ACTIVE = "A";


    // Comment Status => hidden
    public static $HIDDEN = "H";


    // Comment Status => spam
    public static $SPAM = "S";


    // Comment Private => private
    public static $PRIVATE = "Private";


    // Comment Type => comment
    public static $COMMENT = "C";


    // Comment Type => comment
    public static $SLIDER = "S";


    // Comment Type => comment
    public static $FACT_ERROR = "F";


    // Comment Formbuilder Export Status => migrated
    public static $MIGRATED = "M";


    // Comment Formbuilder Export Status => migration error
    public static $MIGRATION_ERROR = "E";


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
        $this->setTableName('comment');
        $this->option('type', 'INNODB');

        $this->hasColumn(
            'cmmnt_id',
            'integer',
            4,
            array(
                'primary' => true,
                'autoincrement' => true
            )
        );

        $this->hasColumn(
            'cmmnt_uuid',
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
            'cmmnt_user_id',
            'integer',
            11,
            array(
                'notnull' => true,
                'fixed' => true
            )
        );

		$this->hasColumn(
            'cmmnt_full_name',
            'string',
            128,
            array(
                'notnull' => true,
                'notblank' => true
            )
        );

        $this->hasColumn(
            'cmmnt_comment',
            'string',
            9999
        );

        $this->hasColumn(
            'cmmnt_private',
            'string',
            16
        );

        $this->hasColumn(
            'cmmnt_status',
            'string',
            1
        );

        $this->hasColumn(
            'cmmnt_submission',
            'clob'
        );

        $this->hasColumn(
            'cmmnt_type',
            'string',
            1
        );

        $this->hasColumn(
            'cmmnt_fb_export_status',
            'string',
            1
        );

        $this->hasColumn(
            'cmmnt_accuracy',
            'integer',
            1,
            array('default' => -1, 'range' => array(-1, 100))
        );

        $this->hasColumn(
            'cmmnt_sentiment',
            'integer',
            1,
            array('default' => -1, 'range' => array(-1, 100))
        );

        // relations
        $this->hasColumn(
            'cmmnt_slctn_id',
            'integer',
            4
        );

        // timestamps
        $this->hasColumn(
            'cmmnt_cre_dtim',
            'timestamp',
            null,
            array(
                'notnull' => true
            )
        );

        $this->hasColumn(
            'cmmnt_upd_dtim',
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
        $this->hasOne('Selection', array(
                'local' => 'cmmnt_slctn_id',
                'foreign' => 'slctn_id'
            )
        );
        // TODO setup entity relations
    }


}
<?php

require_once "IFDB_Exception.php";

class User extends IFDB_Record {

    /**
     * Custom AIR2 validation before update/save. Sets
     * timestamps and uuids among other things.
     *
     * @param Doctrine_Event $event
     */
    public function preValidate($event) {
        //air2_model_prevalidate($this);			// Commented out for now as we do not need them...
    }


    /**
     *
     */
    public function setTableDefinition() {
        $this->setTableName('user');
        $this->option('type', 'INNODB');

		$this->hasColumn(
            'user_id',
            'integer',
            11,
            array(
                'primary' => true,
                'autoincrement' => true
            )
        );

		$this->hasColumn(
            'fb_user_id',
            'string',
            32,
            array(
                'notnull' => 1,
                'notblank' => 1
            )
        );
    }


}
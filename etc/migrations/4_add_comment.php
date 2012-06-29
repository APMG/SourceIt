<?php

/**
 *
 *
 * @author ktaborski
 * @package default
 */
class AddComment extends Doctrine_Migration_Base {


    /**
     *
     */
    public function up() {
        $this->createTable('comment', array(
                'cmmnt_id' => array(
                    'type' => 'integer',
                    'length' => 4,
                    'primary' => 1,
                    'autoincrement' => 1
                ),
				'cmmnt_user_id' => array(
                    'type' => 'integer',
                    'length' => 11,
                    'notnull' => true
                ),
                'cmmnt_uuid' => array(
                    'type' => 'string',
                    'length' => 12,
                    'notnull' => true,
                    'fixed' => true,
                    'unique' => true
                ),
                'cmmnt_full_name' => array(
                    'type' => 'string',
                    'length' => 128,
                    'notnull' => 1,
                    'notblank' => 1
                ),
                'cmmnt_comment' => array(
                    'type' => 'string',
                    'length' => 9999
                ),
                'cmmnt_private' => array(
                    'type' => 'string',
                    'length' => 16
                ),
                'cmmnt_status' => array(
                    'type' => 'string',
                    'length' => 1
                ),
                'cmmnt_submission' => array(
                    'type' => 'clob'
                ),
                'cmmnt_slctn_id' => array(
                    'type' => 'integer',
                    'length' => 4
                ),
                'cmmnt_cre_dtim' => array(
                    'type' => 'timestamp',
                    'length' => 4,
                    'notnull' => 1
                ),
                'cmmnt_upd_dtim' => array(
                    'type' => 'timestamp',
                    'length' => 4
                ),
            )
        );
    }


    /**
     *
     */
    public function down() {
        $this->dropTable('comment');
    }


}

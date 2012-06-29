<?php

/**
 *
 *
 * @author esundelof
 * @package default
 */
class AddUsersTable extends Doctrine_Migration_Base {


    /**
     *
     */
    public function up() {
        $this->createTable('user', array(
                'user_id' => array(
                    'type' => 'integer',
                    'length' => 11,
                    'primary' => 1,
                    'autoincrement' => 1
                ),
                'fb_user_id' => array(
                    'type' => 'integer',
                    'length' => 11,
                    'notnull' => 1,
                    'notblank' => 1,
                    'fixed' => 1
                )
            )
        );
    }


    /**
     *
     */
    public function down() {
        $this->dropTable('user');
    }


}

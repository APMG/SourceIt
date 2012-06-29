<?php

/**
 *
 *
 * @author esundelof
 * @package default
 */
class AlterUser extends Doctrine_Migration_Base {


    /**
     * changing the fb_user_id to string
     */
    public function up() {
        $this->changeColumn('user', 'fb_user_id', 'string', 32, array('notnull' => true, 'notblank' => false));
    }


    /**
     *
     */
    public function down() {
        $this->changeColumn('user', 'fb_user_id', 'integer', 11, array('notnull' => true, 'notblank' => false, 'fixed' => 1));
    }


}

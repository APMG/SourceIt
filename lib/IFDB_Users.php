<?php

require_once "IFDB_Exception.php";

/**
 *
 *
 * @author ktaborski
 * @package default
 */
class IFDB_Users {



    /**
     *  Nothing needed in the constructor for now
     *
     */
    public function __construct() {
		// do not need anything here now
    }

	/**
     * Find a User record by fb_user_id.
     *
     * @param string  $fb_user_id
     * @return $user IFDB user object
     */
    public static function find_by_fb_user_id($fb_user_id) {
        return Doctrine::getTable("User")
        ->findOneBy("fb_user_id", $fb_user_id);
    }


    /**
     * Pass in a result from a selection with it's type and store it.
     *
     * @param array   $form_data
     * @return User
     */
    public function createUser($fb_user_id) {
        $user = new User();
        $user->fb_user_id = $fb_user_id;
        $user->save();

        return $user;
    }


}

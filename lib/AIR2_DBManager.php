<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// can't use AIR2_DBManager class file from AIR2 app
// directly because it duplicates much of what we do in MyPIN2_DBManager
// but crib the important bits here.
require_once 'DBManager.php';
require_once AIR2_PATH.'/lib/AIR2_Record.php';
require_once AIR2_PATH.'/lib/AIR2_Query.php';
require_once AIR2_PATH.'/lib/AIR2_Exception.php';
require_once AIR2_PATH.'/lib/AIR2_Table.php';
require_once AIR2_PATH.'/lib/AIR2_Utils.php';

class AIR2_DBManager extends DBManager {

    public static function get_connection() {
        // Carper::croak("TODO why is AIR2_DBManager used?");
        return IFDB_DBManager::get_air2_connection();
    }

    public static function get_master_connection() {
        // Carper::croak("TODO why is AIR2_DBManager used?");
        return IFDB_DBManager::get_air2_master_connection();
    }

}

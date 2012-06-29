<?php
/**
 *
 *
 * @package default
 */
// required modules
require_once 'IFDB_Exception.php';
require_once 'DBManager.php';

/* initialize Doctrine setup */

// load Doctrine library
require_once DOCPATH.'/Doctrine.php';

// this will allow Doctrine to load Model classes automatically
spl_autoload_register(array('Doctrine', 'autoload'));
spl_autoload_register(array('Doctrine', 'modelsAutoload'));

// must include our base classes before any models can be loaded.
require_once 'IFDB_Table.php';
require_once 'IFDB_Record.php';
require_once 'IFDB_Query.php';

// our "proxy" manager
require_once 'DBManager.php';

// set our overloaded classes as the defaults
Doctrine_Manager::getInstance()->setAttribute(Doctrine_Core::ATTR_QUERY_CLASS, 'IFDB_Query');
Doctrine_Manager::getInstance()->setAttribute(Doctrine_Core::ATTR_TABLE_CLASS, 'IFDB_Table');

// load our base model so others can inherit easily
Doctrine_Manager::getInstance()->setAttribute('model_loading', 'conservative');

// telling Doctrine where our models are located
Doctrine::loadModels(array(APPPATH.'/models'));

// this will allow us to use "mutators"
Doctrine_Manager::getInstance()->setAttribute(
    Doctrine::ATTR_AUTO_ACCESSOR_OVERRIDE, true);

// throw errors when trying to run non-portable commands
//Doctrine_Manager::getInstance()->setAttribute(Doctrine::ATTR_PORTABILITY,
//    Doctrine::PORTABILITY_ALL | Doctrine::PORTABILITY_EXPR);

// turn on data validations in doctrine, to catch things before database ops
Doctrine_Manager::getInstance()->setAttribute(Doctrine::ATTR_VALIDATE,
    Doctrine::VALIDATE_ALL);


/***********************************************************
 * define the class
 */
class IFDB_DBManager extends DBManager {

    public static $air2_master_profile;
    public static $air2_slave_profile;
    public static $air2_master;
    public static $air2_slave;


    /**
     *
     *
     * @param string  $profile_name (optional)
     * @param boolean $load_air_2   (optional) True by default.
     * @return unknown
     */
    public static function init($profile_name=null, $load_air_2=true) {

        // load MyPIN2 tables first
        $ifdb_profile = parent::init($profile_name);

        // bind MyPIN2 tables to master MyPIN2 db (always write-able)
        $mgr = Doctrine_Manager::getInstance();
        $mgr->bindComponent('Article', $ifdb_profile);
        $mgr->bindComponent('Selection', $ifdb_profile);
        $mgr->bindComponent('Entity', $ifdb_profile);
        $mgr->bindComponent('Newsroom', $ifdb_profile);
        $mgr->bindComponent('Author', $ifdb_profile);
        $mgr->bindComponent('SemanticResult', $ifdb_profile);
        $mgr->bindComponent('Comment', $ifdb_profile);
        $mgr->bindComponent('User', $ifdb_profile);

        return $ifdb_profile;
    }


    /**
     * Get the cached database connection
     *
     * @return Doctrine_Connection
     */
    public static function get_air2_connection() {
        if (!isset(self::$db_handles[self::$air2_slave_profile])) {
            throw new Exception("No db connection for profile_name: " . self::$air2_slave_profile);
        }
        return self::$db_handles[self::$air2_slave_profile];
    }


    /**
     * Alias for get_connection().
     *
     * @return unknown
     */
    public static function get_air2_slave_connection() {
        return self::get_air2_connection();
    }


    /**
     * Get the cached master (write) database connection.
     *
     * @return unknown
     */
    public static function get_air2_master_connection() {
        if (!isset(self::$db_handles[self::$air2_master_profile])) {
            throw new Exception("No db connection for profile_name: " . self::$air2_master_profile);
        }
        return self::$db_handles[self::$air2_master_profile];
    }


}

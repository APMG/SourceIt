<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**************************************************************************
 *
 *   Copyright 2010 American Public Media Group
 *
 *   This file is part of AIR2.
 *
 *   AIR2 is free software: you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation, either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   AIR2 is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with AIR2.  If not, see <http://www.gnu.org/licenses/>.
 *
 *************************************************************************/

/**
 * Doctrine_Manager wrapper.
 *
 * Allows caching of database connections and master/slave replication.
 *
 * This class relies on the presence of 2 files:
 *  etc/db_registry.ini (or etc/profiles.ini)
 *  etc/my_profile
 *
 * The .ini file should have one or more profiles listed. The profile
 * is picked based on the following order:
 * -  passed explicitly to init()
 * -  env var named in DBMGR_DOMAIN constant
 * -  contents of etc/my_profile
 *
 * If there is a <profilename>_master profile defined in the .ini file,
 * it is opened as the "master" db handle and <profilename> as the slave.
 * Otherwise, <profilename> is used for both master and slave (same connection).
 *
 * This is an abstract class, intended to be subclassed per-application.
 * Example usage:
 *
 *   class My_DBManager extends DBManager {
 *
 *   }
 *   My_DBManager::init();  // profile picked from etc/my_profile
 *   $conn = My_DBManager::get_master_connection();  // a Doctrine_Connection object
 *   $conn->execute("update foo set bar=456 where bar=123");
 *
 * @author pkarman
 * @package default
 */
abstract class DBManager {
    /* set to true to force all connections to the MASTER profile */
    public static $FORCE_MASTER_ONLY = false;

    public static $master, $slave;
    public static $db_handles = array();
    public static $registry = null;
    public static $master_profile = null;
    public static $slave_profile = null;

    /**
     * Parse the database registry file
     *
     * @param string  $app_path (optional)
     * @return string $file
     */
    private static function load_registry_file($app_path=null) {
        if ($app_path == null and defined('APPPATH')) {
            $app_path = APPPATH;
        }
        $files = array('db_registry.ini', 'profiles.ini');
        foreach ($files as $f) {
            $file = $app_path.'../etc/'.$f;
            if (!file_exists($file)) {
                continue;
            }
            if (defined('DBMGR_REGISTRY') && getenv(DBMGR_REGISTRY)) {
                $file = getenv(DBMGR_REGISTRY);
            }
            self::$registry = parse_ini_file($file, true);
            if (self::$registry == false) {
                // throw normal Exception on error, since this is really fatal:
                // we can't go to the db to get our error message.
                throw new Exception("Failed to load DB registry file: $file");
            }
            else {
                return $file; // stop looking. we're done
            }
        }
        throw new Exception("Failed to find any DB registry file");
    }


    /**
     * Get the name of the current profile
     *
     * @param string  $app_path (optional)
     * @return $profile_name
     */
    public static function get_profile_name($app_path=null) {
        if (self::$slave_profile) {
            return self::$slave_profile;
        }
        $n = null;
        if (defined('DBMGR_DOMAIN')) {
            $n = getenv(DBMGR_DOMAIN);
        }
        if (!$n) {
            if ($app_path == null and defined('APPPATH')) {
                $app_path = APPPATH;
            }
            $my_profile = $app_path.'../etc/my_profile';
            if (file_exists($my_profile)) {
                $n = trim(file_get_contents($my_profile));
            }
        }
        if (!$n) {
            error_log(__CLASS__ . " defaulting to profile_name 'dev' -- create etc/my_profile");
            $n = 'dev';
        }
        return $n;
    }


    /**
     * Get names used for master/slave connections
     *
     * @param string  $profile_name
     * @return string
     */
    public static function get_master_slave_names($profile_name) {
        $names = array();
        if (!preg_match('/_master$/', $profile_name)) {
            $names['slave'] = $profile_name;
            $names['master'] = $profile_name . '_master';
        }
        else {
            $names['slave'] = preg_replace('/_master$/', '', $profile_name);
            $names['master'] = $profile_name;
        }
        return $names;
    }


    /**
     * Load a database registry file, and connect to the database.
     * All connections are cached, so repeated instances of this class
     * should re-use the same connections.
     *
     * @param string  $profile_name
     * @param string  $app_path     (optional)
     * @returns string $profile_name
     */
    public static function init($profile_name=null, $app_path=null) {
        // pick the db profile from the registry
        if (!$profile_name) {
            $profile_name = self::get_profile_name($app_path);
        }

        if (!isset(self::$slave_profile) || !isset(self::$master_profile)) {
            $names = self::get_master_slave_names($profile_name);
            self::$slave_profile = $names['slave'];
            self::$master_profile = $names['master'];
        }

        if (self::$registry == null) {
            self::load_registry_file($app_path);
        }
        if (!array_key_exists(self::$slave_profile, self::$registry)) {
            die("No such profile_name defined: " . self::$slave_profile . "\n");
        }
        self::$slave = self::$registry[self::$slave_profile];

        // if master is not defined explicitly, assume it is the same as slave (single host)
        if (!array_key_exists(self::$master_profile, self::$registry)) {
            self::$master = self::$slave;
        }
        else {
            self::$master = self::$registry[self::$master_profile];
        }
        $slave_dsn = self::make_dsn(self::$slave);
        $master_dsn = self::make_dsn(self::$master);
        self::open_connection($slave_dsn, self::$slave_profile);
        if ($master_dsn != $slave_dsn) {
            self::open_connection($master_dsn, self::$master_profile);
        }
        else {
            self::$db_handles[self::$master_profile] = self::$db_handles[self::$slave_profile];
        }

        return $profile_name;
    }


    /**
     * Opens, and caches under $name, a Doctrine_Connection for the $dsn.
     *
     * @param string  $dsn
     * @param string  $name
     */
    public static function open_connection($dsn, $name) {
        // error_log("open_connection for $dsn [$name]");
        // cache the connection
        if (!isset(self::$db_handles[$name])) {
            self::$db_handles[$name] = Doctrine_Manager::connection($dsn, $name);
            self::$db_handles[$name]->setCharset('utf8');
            self::$db_handles[$name]->setCollate('utf8_unicode_ci');
            self::$db_handles[$name]->execute("SET sql_mode='STRICT_ALL_TABLES'");
            //print("db connect for profile_name '$name'\n");
        }
    }


    /**
     * Reset connection
     *
     * @param string  $profile_name (optional) Passed to init(), if given here.
     * @param string  $app_path     (optional) Passed to init(), if given here.
     * @return string|null
     */
    public static function reset($profile_name=null, $app_path=null) {
        foreach (self::$db_handles as $name => $conn) {
            $conn->close();
        }

        self::$master = null;
        self::$slave = null;
        self::$db_handles = array();
        self::$registry = null;
        self::$master_profile = null;
        self::$slave_profile = null;

        Doctrine_Manager::resetInstance(); // not just reset()

        // If we were handed args, call init() on behalf of the user, and return the result.
        if (!empty($profile_name) || !empty($app_path)) {
            return self::init($profile_name, $app_path);
        }
    }


    /**
     * Returns DSN connection string for profile array.
     *
     * @param array   $profile
     * @return string $dsn
     */
    public static function make_dsn($profile) {
        $dsn = $profile['driver'] .
            '://' . $profile['username'] .
            ':'   . $profile['password'] .
            '@'   . $profile['hostname'] .
            '/'   . $profile['dbname'];
        return $dsn;
    }


    /**
     * Get the cached database connection
     *
     * @return Doctrine_Connection
     */
    public static function get_connection() {
        if (self::$FORCE_MASTER_ONLY) {
            return self::get_master_connection();
        }
        if (!isset(self::$db_handles[self::$slave_profile])) {
            throw new Exception("No db connection for profile_name: " . self::$slave_profile);
        }
        return self::$db_handles[self::$slave_profile];
    }


    /**
     * Alias for get_connection().
     *
     * @return Doctrine_Connection
     */
    public static function get_slave_connection() {
        return self::get_connection();
    }


    /**
     * Get the cached master (write) database connection.
     *
     * @return Doctrine_Connection
     */
    public static function get_master_connection() {
        if (!isset(self::$db_handles[self::$master_profile])) {
            throw new Exception("No db connection for profile_name: " . self::$master_profile);
        }
        return self::$db_handles[self::$master_profile];
    }


    /**
     * Gets the name of a database connection
     *
     * @param Doctrine_Connection $conn
     * @return string
     */
    public static function get_name($conn) {
        $mgr = Doctrine_Manager::getInstance();
        return $mgr->getConnectionName($conn);
    }


    /**
     * Determine if the profile is using DB replication
     *
     * @return boolean
     */
    public function uses_replication() {
        $mdsn = self::make_dsn(self::$master);
        $sdsn = self::make_dsn(self::$slave);
        return $mdsn !== $sdsn;
    }


}

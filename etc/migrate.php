<?php

/**
 * Loads the application to get a DB connection.
 * Then runs the migration based on the second arg, ie to that migration
 * version or if it isn't there to the current version.
 *
 * @author ktaborski
 * @package default
 */
require_once 'app/init.php';

// manually load the DB connection 
$profiles = array(
    "main" => @file_get_contents(dirname(__FILE__) . "/my_profile"),
    "test" => @file_get_contents(dirname(__FILE__) . "/my_profile_test"),
);

// Filter out profiles that weren't read.
$profiles = array_filter($profiles, "is_string");

foreach ($profiles as $name => $profile) {
    $profiles[$name] = trim($profile);
}

$profiles_worked_on = 0;
foreach ($profiles as $name => $profile) {
    echo "Using profile $profile\n";

    $ifdb_connection = IFDB_DBManager::init(
        $profile,
        // Do not load AIR 2 models -- we don't want to create those!
        false
    );

    // migration specific code
    $migration = new Doctrine_Migration(dirname(__FILE__) . "/migrations", $ifdb_connection);

    if ( !isset($argv[1]) ) {
        $migration->migrate();
        echo "Adjusted the DB to latest version.\n";
    } else {
        $migration->migrate($argv[1]);
        echo "Adjusted the DB to migration ".$argv[1]."\n";
    }

}

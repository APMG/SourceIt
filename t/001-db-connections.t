#!/usr/bin/env php
<?php

require_once 'UnitTest.inc';
require_once 'IFDB_DBManager.php';

plan(5);

ok( IFDB_DBManager::init(), "init ifdb connection" );
ok( $q = AIR2_Query::create(), "create AIR2_Query instance" );
ok( $users = $q->from('User u')->execute(), "select all Users" );

diag(count($users) . " users");

foreach ($users as $u) {
    if ($u->user_username == 'Administrator') {
       pass("Got Administrator");
    }
    elseif ($u->user_username == 'AIR2SYSTEM') {
       pass("Got AIR2SYSTEM");
    }
}

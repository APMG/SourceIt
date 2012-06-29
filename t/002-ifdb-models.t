#!/usr/bin/env php
<?php

require_once "UnitTest.inc";
require_once "IFDB_DBManager.php";

plan(2);

$profile_name = IFDB_DBManager::init();
ok($profile_name, "init IFDB connection");

$q = IFDB_Query::create();
ok($q, "created IFDB_Query instance");

// Make sure there is at least one Article record before testing against it.
/*$raw_db = IFDB_DBManager::get_connection();
$result = $raw_db->execute('select count(*) as count from article')->fetch();
if ($result['count'] == 0) {
    $timestamp = date('Y-m-d H:i:s');
    $raw_db->execute(
        'insert into source_auth(sauth_src_uuid, sauth_type, sauth_username, sauth_cre_dtim)' .
        "values('123abc', 'L', 'pijtest01@nosuchemail.org', '$timestamp')"
    );
}

$auths = $q->from('Article')->limit(1)->fetchOne();
ok($auths, "select all Article records");*/

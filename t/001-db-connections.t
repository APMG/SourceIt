#!/usr/bin/env php
<?php

require_once 'UnitTest.inc';
require_once 'IFDB_DBManager.php';

plan(1);

ok( IFDB_DBManager::init(), "init ifdb connection" );

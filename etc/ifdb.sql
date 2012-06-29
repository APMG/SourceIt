/* assumption - you know the root user password for mysql */
/* create the DB */
/* open it to run the following commands */
/* mysql -u root -p ifdb */

drop database IF EXISTS ifdb;

create database ifdb;

grant all on ifdb.* to 'tntuser'@'localhost' identified by 'tnt4ifdb';

alter database ifdb
default character set utf8
default collate utf8_unicode_ci;

use ifdb;

CREATE TABLE `migration_version` (
  `version` int(11) default NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO migration_version (version) VALUES (0);

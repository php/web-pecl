--
-- Table structure for table `bugdb`
--

DROP TABLE IF EXISTS `bugdb`;

CREATE TABLE `bugdb` (
  `id` int(8) NOT NULL auto_increment,
  `package_name` varchar(80) default NULL,
  `bug_type` varchar(32) NOT NULL default 'Bug',
  `email` varchar(40) NOT NULL default '',
  `sdesc` varchar(80) NOT NULL default '',
  `ldesc` text NOT NULL,
  `php_version` varchar(100) default NULL,
  `php_os` varchar(32) default NULL,
  `status` varchar(16) default NULL,
  `ts1` datetime default NULL,
  `ts2` datetime default NULL,
  `assign` varchar(20) default NULL,
  `passwd` varchar(20) default NULL,
  `duplicate_of` int(8) unsigned NOT NULL default '0',
  `package_version` varchar(100) default NULL,
  `reporter_name` varchar(80) default NULL,
  `handle` varchar(20) NOT NULL default '',
  `registered` tinyint(1) NOT NULL default '0',
  `visitor_ip` int(10) unsigned NOT NULL default '0',
  `new_id` int(8) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `php_version` (`php_version`(1)),
  KEY `duplicate_of` (`duplicate_of`),
  KEY `package_version` (`package_version`(1)),
  KEY `package_name` (`package_name`),
  FULLTEXT KEY `email` (`email`,`sdesc`,`ldesc`)
)

--
-- Table structure for table `bugdb_comments`
--

DROP TABLE IF EXISTS `bugdb_comments`;

CREATE TABLE `bugdb_comments` (
  `id` int(8) NOT NULL auto_increment,
  `bug` int(8) NOT NULL default '0',
  `email` varchar(40) NOT NULL default '',
  `ts` datetime NOT NULL default '0000-00-00 00:00:00',
  `comment` text NOT NULL,
  `reporter_name` varchar(80) default NULL,
  `handle` varchar(20) NOT NULL default '',
  `visitor_ip` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  INDEX (`bug`,`id`,`ts`),
  FULLTEXT KEY `comment` (`comment`)
);

--
-- Table structure for table `bugdb_votes`
--

DROP TABLE IF EXISTS `bugdb_votes`;

CREATE TABLE `bugdb_votes` (
  `bug` int(8) NOT NULL default '0',
  `ts` timestamp NOT NULL,
  `ip` int(10) unsigned NOT NULL default '0',
  `score` int(3) NOT NULL default '0', /* 1-5 */
  `reproduced` int(1) NOT NULL default '0',
  `tried` int(1) NOT NULL default '0',
  `sameos` int(1) default NULL,
  `samever` int(1) default NULL
);

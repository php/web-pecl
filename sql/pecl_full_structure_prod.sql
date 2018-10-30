-- MySQL dump 10.13  Distrib 5.5.41, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: peclweb
-- ------------------------------------------------------
-- Server version    5.5.41-0+wheezy1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `aggregated_package_stats`
--

DROP TABLE IF EXISTS `aggregated_package_stats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aggregated_package_stats` (
  `package_id` int(11) NOT NULL DEFAULT '0',
  `release_id` int(11) NOT NULL DEFAULT '0',
  `yearmonth` date NOT NULL DEFAULT '0000-00-00',
  `downloads` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`release_id`,`yearmonth`),
  KEY `package_id` (`package_id`),
  KEY `downloads` (`downloads`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categories` (
  `id` int(11) NOT NULL DEFAULT '0',
  `parent` int(11) DEFAULT NULL,
  `name` varchar(80) NOT NULL DEFAULT '',
  `summary` text,
  `description` text,
  `npackages` int(11) DEFAULT '0',
  `pkg_left` int(11) DEFAULT NULL,
  `pkg_right` int(11) DEFAULT NULL,
  `cat_left` int(11) DEFAULT NULL,
  `cat_right` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `categories_seq`
--

DROP TABLE IF EXISTS `categories_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categories_seq` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=61 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cvs_acl`
--

DROP TABLE IF EXISTS `cvs_acl`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cvs_acl` (
  `username` varchar(20) DEFAULT NULL,
  `usertype` enum('user','group') NOT NULL DEFAULT 'user',
  `path` varchar(250) NOT NULL DEFAULT '',
  `access` tinyint(1) DEFAULT NULL,
  UNIQUE KEY `username` (`username`,`path`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cvs_group_membership`
--

DROP TABLE IF EXISTS `cvs_group_membership`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cvs_group_membership` (
  `groupname` varchar(20) NOT NULL DEFAULT '',
  `username` varchar(20) NOT NULL DEFAULT '',
  `granted_when` datetime DEFAULT NULL,
  `granted_by` varchar(20) NOT NULL DEFAULT '',
  UNIQUE KEY `groupname` (`groupname`,`username`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cvs_groups`
--

DROP TABLE IF EXISTS `cvs_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cvs_groups` (
  `groupname` varchar(20) NOT NULL DEFAULT '',
  `description` varchar(250) NOT NULL DEFAULT '',
  UNIQUE KEY `groupname` (`groupname`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `deps`
--

DROP TABLE IF EXISTS `deps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `deps` (
  `package` varchar(80) NOT NULL DEFAULT '',
  `release` varchar(20) NOT NULL DEFAULT '',
  `type` varchar(6) NOT NULL DEFAULT '',
  `relation` varchar(6) NOT NULL DEFAULT '',
  `version` varchar(20) DEFAULT NULL,
  `name` varchar(100) NOT NULL DEFAULT '',
  `optional` tinyint(4) NOT NULL DEFAULT '0',
  KEY `release` (`release`),
  KEY `package` (`package`,`version`),
  KEY `package_2` (`package`,`optional`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `files`
--

DROP TABLE IF EXISTS `files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `files` (
  `id` int(11) NOT NULL DEFAULT '0',
  `package` int(11) NOT NULL DEFAULT '0',
  `release` int(11) NOT NULL DEFAULT '0',
  `platform` varchar(50) DEFAULT NULL,
  `format` varchar(50) DEFAULT NULL,
  `md5sum` varchar(32) DEFAULT NULL,
  `basename` varchar(100) DEFAULT NULL,
  `fullpath` varchar(250) DEFAULT NULL,
  `packagexml` longtext NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pkg_rel_plat` (`package`,`release`,`platform`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `files_seq`
--

DROP TABLE IF EXISTS `files_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `files_seq` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6752 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `karma`
--

DROP TABLE IF EXISTS `karma`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `karma` (
  `id` int(10) unsigned NOT NULL DEFAULT '0',
  `user` varchar(20) NOT NULL DEFAULT '',
  `level` varchar(20) NOT NULL DEFAULT '',
  `granted_by` varchar(20) NOT NULL DEFAULT '',
  `granted_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `user` (`user`),
  KEY `level` (`level`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `karma_seq`
--

DROP TABLE IF EXISTS `karma_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `karma_seq` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6702 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `maintains`
--

DROP TABLE IF EXISTS `maintains`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `maintains` (
  `handle` varchar(20) NOT NULL DEFAULT '',
  `package` int(11) NOT NULL DEFAULT '0',
  `role` enum('lead','developer','contributor','helper') NOT NULL DEFAULT 'lead',
  `active` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`handle`,`package`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `notes`
--

DROP TABLE IF EXISTS `notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notes` (
  `id` int(11) NOT NULL DEFAULT '0',
  `uid` varchar(20) DEFAULT NULL,
  `pid` int(11) DEFAULT NULL,
  `rid` int(11) DEFAULT NULL,
  `cid` int(11) DEFAULT NULL,
  `nby` varchar(20) DEFAULT NULL,
  `ntime` datetime DEFAULT NULL,
  `note` text,
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `pid` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `notes_seq`
--

DROP TABLE IF EXISTS `notes_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notes_seq` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6641 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `package_acl`
--

DROP TABLE IF EXISTS `package_acl`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `package_acl` (
  `handle` varchar(20) NOT NULL DEFAULT '',
  `package` varchar(80) NOT NULL DEFAULT '',
  `access` int(11) DEFAULT NULL,
  UNIQUE KEY `handle` (`handle`,`package`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `package_aliases`
--

DROP TABLE IF EXISTS `package_aliases`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `package_aliases` (
  `package_id` int(11) NOT NULL DEFAULT '0',
  `alias_name` varchar(80) NOT NULL DEFAULT '',
  PRIMARY KEY (`package_id`,`alias_name`),
  KEY `alias_name` (`alias_name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `package_stats`
--

DROP TABLE IF EXISTS `package_stats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `package_stats` (
  `dl_number` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `package` varchar(80) NOT NULL DEFAULT '',
  `release` varchar(20) NOT NULL DEFAULT '',
  `pid` int(11) NOT NULL DEFAULT '0',
  `rid` int(11) NOT NULL DEFAULT '0',
  `cid` int(11) NOT NULL DEFAULT '0',
  `last_dl` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`rid`,`pid`),
  KEY `package` (`package`),
  KEY `dl_number` (`dl_number`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `packages`
--

DROP TABLE IF EXISTS `packages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `packages` (
  `id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(80) NOT NULL DEFAULT '',
  `category` int(11) DEFAULT NULL,
  `stablerelease` varchar(20) DEFAULT NULL,
  `develrelease` varchar(20) DEFAULT NULL,
  `license` varchar(50) DEFAULT NULL,
  `summary` text,
  `description` text,
  `homepage` varchar(255) DEFAULT NULL,
  `package_type` enum('pear','pecl') NOT NULL DEFAULT 'pear',
  `doc_link` varchar(255) DEFAULT NULL,
  `cvs_link` varchar(255) DEFAULT NULL,
  `bug_link` varchar(255) DEFAULT NULL,
  `approved` tinyint(4) NOT NULL DEFAULT '0',
  `wiki_area` tinyint(1) NOT NULL DEFAULT '0',
  `unmaintained` tinyint(1) NOT NULL DEFAULT '0',
  `newpk_id` int(11) DEFAULT NULL,
  `blocktrackbacks` tinyint(4) NOT NULL DEFAULT '0',
  `newpackagename` varchar(100) DEFAULT NULL,
  `newchannel` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `category` (`category`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `packages_seq`
--

DROP TABLE IF EXISTS `packages_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `packages_seq` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=982 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `releases`
--

DROP TABLE IF EXISTS `releases`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `releases` (
  `id` int(11) NOT NULL DEFAULT '0',
  `package` int(11) NOT NULL DEFAULT '0',
  `version` varchar(20) NOT NULL DEFAULT '',
  `state` enum('stable','beta','alpha','snapshot','devel') DEFAULT 'stable',
  `doneby` varchar(20) NOT NULL DEFAULT '',
  `license` varchar(20) DEFAULT NULL,
  `summary` text,
  `description` text,
  `releasedate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `releasenotes` text,
  `packagefile` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `package` (`package`,`version`),
  KEY `state` (`state`),
  KEY `releasedate` (`releasedate`,`package`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `releases_seq`
--

DROP TABLE IF EXISTS `releases_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `releases_seq` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6763 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `state_order`
--

DROP TABLE IF EXISTS `state_order`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `state_order` (
  `state` varchar(10) NOT NULL DEFAULT '',
  `orderno` int(11) DEFAULT NULL,
  PRIMARY KEY (`state`),
  KEY `orderno` (`orderno`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `handle` varchar(20) NOT NULL DEFAULT '',
  `password` varchar(64) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `homepage` varchar(255) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `createdby` varchar(20) DEFAULT NULL,
  `lastlogin` datetime DEFAULT NULL,
  `showemail` tinyint(1) DEFAULT NULL,
  `registered` tinyint(1) DEFAULT NULL,
  `admin` tinyint(1) DEFAULT NULL,
  `userinfo` text,
  `pgpkeyid` varchar(20) DEFAULT NULL,
  `pgpkey` text,
  `wishlist` varchar(255) NOT NULL DEFAULT '',
  `longitude` varchar(25) DEFAULT NULL,
  `latitude` varchar(25) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `from_site` varchar(4) NOT NULL DEFAULT 'pear',
  PRIMARY KEY (`handle`),
  UNIQUE KEY `email_u` (`email`),
  KEY `handle` (`handle`,`registered`),
  KEY `pgpkeyid` (`pgpkeyid`),
  KEY `email` (`email`(25)),
  KEY `IDX_geoloc` (`latitude`,`longitude`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2015-03-30  6:53:29

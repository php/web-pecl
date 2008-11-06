--
-- Table structure for table `package_aliases`
--

DROP TABLE IF EXISTS `package_aliases`;

CREATE TABLE `package_aliases` (
  `package_id` int(11) NOT NULL default '0',
  `alias_name` varchar(80) NOT NULL default '',
  PRIMARY KEY  (`package_id`,`alias_name`),
  KEY `alias_name` (`alias_name`)
);

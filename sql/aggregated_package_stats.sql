--
-- Table structure for table `aggregated_package_stats`
--

DROP TABLE IF EXISTS `aggregated_package_stats`;

CREATE TABLE `aggregated_package_stats` (
  `package_id` int(11) NOT NULL default '0',
  `release_id` int(11) NOT NULL default '0',
  `yearmonth` date NOT NULL default '0000-00-00',
  `downloads` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`release_id`,`yearmonth`),
  KEY `package_id` (`package_id`),
  KEY `downloads` (`downloads`)
)

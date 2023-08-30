--
-- Table structure for table `package_stats`
--

DROP TABLE IF EXISTS `package_stats`;

CREATE TABLE `package_stats` (
  `dl_number` int(11) unsigned NOT NULL default '0',
  `package` varchar(80) NOT NULL default '',
  `release` varchar(20) NOT NULL default '',
  `pid` int(11) NOT NULL default '0',
  `rid` int(11) NOT NULL default '0',
  `cid` int(11) NOT NULL default '0',
  `last_dl` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`rid`,`pid`),
  INDEX (`package`),
  INDEX (`dl_number`)
);

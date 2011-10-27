--
-- Table structure for table `deps`
--

DROP TABLE IF EXISTS `deps`;

CREATE TABLE `deps` (
  `package` varchar(80) NOT NULL default '',
  `release` varchar(20) NOT NULL default '',
  `type` varchar(6) NOT NULL default '',
  `relation` varchar(6) NOT NULL default '',
  `version` varchar(20) default NULL,
  `name` varchar(100) NOT NULL default '',
  `optional` tinyint(4) NOT NULL default '0',
  INDEX (`release`),
  INDEX (`package`,`version`),
  INDEX (`package`,`optional`)
);

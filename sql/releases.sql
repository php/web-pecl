--
-- Table structure for table `releases`
--

DROP TABLE IF EXISTS `releases`;

CREATE TABLE `releases` (
  `id` int(11) NOT NULL default '0',
  `package` int(11) NOT NULL default '0',
  `version` varchar(20) NOT NULL default '',
  `state` enum('stable','beta','alpha','snapshot','devel') default 'stable',
  `doneby` varchar(20) NOT NULL default '',
  `license` varchar(20) default NULL,
  `summary` text,
  `description` text,
  `releasedate` datetime NOT NULL default '0000-00-00 00:00:00',
  `releasenotes` text,
  `packagefile` text,
  PRIMARY KEY  (`id`),
  UNIQUE INDEX (`package`,`version`),
  INDEX (`state`),
  INDEX (`releasedate`,`package`)
);

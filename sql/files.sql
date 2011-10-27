--
-- Table structure for table `files`
--

DROP TABLE IF EXISTS `files`;

CREATE TABLE `files` (
  `id` int(11) NOT NULL default '0',
  `package` int(11) NOT NULL default '0',
  `release` int(11) NOT NULL default '0',
  `platform` varchar(50) default NULL,
  `format` varchar(50) default NULL,
  `md5sum` varchar(32) default NULL,
  `basename` varchar(100) default NULL,
  `fullpath` varchar(250) default NULL,
  `packagexml` longtext NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE INDEX (`package`,`release`,`platform`)
);

CREATE TABLE `package_aliases` (
  `package_id` int(11) NOT NULL,
  `alias_name` varchar(80) NOT NULL,
  PRIMARY KEY  (`package_id`,`alias_name`),
  KEY `alias_name` (`alias_name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

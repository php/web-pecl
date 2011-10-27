--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `handle` varchar(20) NOT NULL default '',
  `password` varchar(64) default NULL,
  `name` varchar(100) default NULL,
  `email` varchar(100) default NULL,
  `homepage` varchar(255) default NULL,
  `created` datetime default NULL,
  `createdby` varchar(20) default NULL,
  `lastlogin` datetime default NULL,
  `showemail` tinyint(1) default NULL,
  `registered` tinyint(1) default NULL,
  `admin` tinyint(1) default NULL,
  `userinfo` text,
  `pgpkeyid` varchar(20) default NULL,
  `pgpkey` text,
  `wishlist` varchar(255) NOT NULL default '',
  `longitude` varchar(25) default NULL,
  `latitude` varchar(25) default NULL,
  `active` tinyint(1) NOT NULL default '1',
  `from_site` varchar(4) NOT NULL default 'pear',
  PRIMARY KEY  (`handle`),
  UNIQUE KEY `email_u` (`email`),
  KEY `handle` (`handle`,`registered`),
  KEY `pgpkeyid` (`pgpkeyid`),
  KEY `email` (`email`(25)),
  KEY `IDX_geoloc` (`latitude`,`longitude`)
);

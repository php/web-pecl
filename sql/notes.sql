--
-- Table structure for table `notes`
--

DROP TABLE IF EXISTS `notes`;

CREATE TABLE `notes` (
  `id` int(11) NOT NULL default '0',
  `uid` varchar(20) default NULL,  -- REFERENCES users(handle),
  `pid` int(11) default NULL,      -- REFERENCES packages(id),
  `rid` int(11) default NULL,      -- REFERENCES releases(id),
  `cid` int(11) default NULL,      -- REFERENCES categories(id),
  `nby` varchar(20) default NULL,  -- REFERENCES users(handle),
  `ntime` datetime default NULL,
  `note` text,
  PRIMARY KEY  (`id`),
  INDEX (`uid`),
  INDEX (`pid`)
);

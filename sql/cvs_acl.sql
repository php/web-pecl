--
-- Table structure for table `cvs_acl`
--

DROP TABLE IF EXISTS `cvs_acl`;

CREATE TABLE `cvs_acl` (
  `username` varchar(20) default NULL,
  `usertype` enum('user','group') NOT NULL default 'user',
  `path` varchar(250) NOT NULL default '',
  `access` tinyint(1) default NULL,
  UNIQUE INDEX (`username`,`path`)
);

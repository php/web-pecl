--
-- Table structure for table `package_acl`
--

DROP TABLE IF EXISTS `package_acl`;

CREATE TABLE `package_acl` (
  `handle` varchar(20) NOT NULL default '',
  `package` varchar(80) NOT NULL default '',
  `access` int(11) default NULL,
  UNIQUE INDEX (`handle`,`package`)
);

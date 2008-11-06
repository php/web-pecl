--
-- Table structure for table `cvs_group_membership`
--

DROP TABLE IF EXISTS `cvs_group_membership`;

CREATE TABLE `cvs_group_membership` (
  `groupname` varchar(20) NOT NULL default '',
  `username` varchar(20) NOT NULL default '',
  `granted_when` datetime default NULL,
  `granted_by` varchar(20) NOT NULL default '',
  UNIQUE INDEX (`groupname`,`username`)
);

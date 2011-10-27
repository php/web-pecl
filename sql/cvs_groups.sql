--
-- Table structure for table `cvs_groups`
--

DROP TABLE IF EXISTS `cvs_groups`;

CREATE TABLE `cvs_groups` (
  `groupname` varchar(20) NOT NULL default '',
  `description` varchar(250) NOT NULL default '',
  UNIQUE INDEX (`groupname`)
);

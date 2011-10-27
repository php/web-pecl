--
-- Table structure for table `package_proposals`
--

DROP TABLE IF EXISTS `package_proposals`;

CREATE TABLE `package_proposals` (
  `id` int(11) NOT NULL auto_increment,
  `pkg_category` varchar(80) NOT NULL default '',
  `pkg_name` varchar(80) NOT NULL default '',
  `pkg_license` varchar(100) NOT NULL default '',
  `pkg_describtion` text NOT NULL,
  `pkg_deps` text NOT NULL,
  `draft_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `proposal_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `vote_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `longened_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `status` enum('draft','proposal','vote','finished') NOT NULL default 'draft',
  `user_handle` varchar(255) NOT NULL default '',
  `markup` enum('bbcode','wiki') NOT NULL default 'bbcode',
  PRIMARY KEY  (`id`),
  KEY `cat_name` (`pkg_category`,`pkg_name`)
);

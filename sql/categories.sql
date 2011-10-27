--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;

CREATE TABLE `categories` (
  `id` int(11) NOT NULL default '0',
  `parent` int(11) default NULL,
  `name` varchar(80) NOT NULL default '',
  `summary` text,
  `description` text,
  `npackages` int(11) default '0',
  `pkg_left` int(11) default NULL,
  `pkg_right` int(11) default NULL,
  `cat_left` int(11) default NULL,
  `cat_right` int(11) default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE INDEX (`name`)
);

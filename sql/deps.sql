CREATE TABLE deps (
  package varchar(80) NOT NULL default '',
  release varchar(20) NOT NULL default '',
  type varchar(6) NOT NULL default '',
  relation varchar(6) NOT NULL default '',
  version varchar(10) NOT NULL default '',
  name varchar(100) NOT NULL default '',
  INDEX (release),
  INDEX (package,version)
);

CREATE TABLE deps (
  package    varchar(80) NOT NULL REFERENCES packages(id),
  release    varchar(20) NOT NULL REFERENCES releases(id),
  type       ENUM('pkg','ext','php','prog','ldlib','rtlib','os','websrv','nsapi') NOT NULL,
  relation   ENUM('has', 'eq', 'lt', 'le', 'gt', 'ge') DEFAULT 'has',
  version    varchar(20),
  name	     varchar(100),
  INDEX (release),
  INDEX (package,version)
);

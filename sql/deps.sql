CREATE TABLE deps (
  package    varchar(80) NOT NULL REFERENCES packages(id),
  release    varchar(20) NOT NULL REFERENCES releases(id),
  type       ENUM('pkg','ext','php','prog','ldlib','rtlib','os','websrv','nsapi') DEFAULT 'has',
  type	     varchar(6)	 NOT NULL ,
  relation   varchar(6)	 NOT NULL,
  version    varchar(20),
  name	     varchar(100),
  INDEX (release),
  INDEX (package,version)
);

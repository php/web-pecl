CREATE DATABASE pear;

INSERT INTO user VALUES('pb1','pear',password('pear'),'N','N','N','N','N','N','N','N','N','N','N','N','N','N');
INSERT INTO user VALUES('localhost','pear',password('pear'),'N','N','N','N','N','N','N','N','N','N','N','N','N','N');
INSERT INTO db VALUES('%','pear','pear','Y','Y','Y','Y','Y','Y','Y','Y','Y','Y');

FLUSH PRIVILEGES;


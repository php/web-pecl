CREATE DATABASE pear;

INSERT INTO user VALUES('%.trondheim.fast.no','pear',password('pear'),'N','N','N','N','N','N','N','N','N','N','N','N','N','N');
INSERT INTO user VALUES('localhost','pear',password('pear'),'N','N','N','N','N','N','N','N','N','N','N','N','N','N');
INSERT INTO db VALUES('%','pear','pear','Y','Y','Y','Y','Y','Y','Y','Y','Y','Y');

FLUSH PRIVILEGES;


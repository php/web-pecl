CREATE DATABASE pear;

INSERT INTO user (host,user,password,select_priv,insert_priv,update_priv,delete_priv,create_priv,drop_priv,reload_priv,shutdown_priv,process_priv,file_priv,grant_priv,references_priv,index_priv,alter_priv) VALUES('pb1','pear',password('pear'),'N','N','N','N','N','N','N','N','N','N','N','N','N','N');
INSERT INTO user (host,user,password,select_priv,insert_priv,update_priv,delete_priv,create_priv,drop_priv,reload_priv,shutdown_priv,process_priv,file_priv,grant_priv,references_priv,index_priv,alter_priv) VALUES('localhost','pear',password('pear'),'N','N','N','N','N','N','N','N','N','N','N','N','N','N');
INSERT INTO db (host,db,user,select_priv,insert_priv,update_priv,delete_priv,create_priv,drop_priv,grant_priv,references_priv,index_priv,alter_priv) VALUES('%','pear','pear','Y','Y','Y','Y','Y','Y','Y','Y','Y','Y');

FLUSH PRIVILEGES;


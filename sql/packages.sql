CREATE TABLE packages (
       name	      VARCHAR(80) NOT NULL,
       placeholder    INTEGER(1) DEFAULT '0',
       parent         VARCHAR(80),
       stablerelease  VARCHAR(20),
       develrelease   VARCHAR(20),
       copyright      VARCHAR(20) DEFAULT 'PEAR License',
       summary	      TEXT,
       description    TEXT,
       leftvisit      INTEGER,
       rightvisit     INTEGER,

       PRIMARY KEY(name)
);

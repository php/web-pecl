CREATE TABLE packages (
       id             INTEGER NOT NULL,
       name	      VARCHAR(80) NOT NULL,
       virtual        INTEGER(1) DEFAULT '0',
       parent         VARCHAR(80),
       stablerelease  VARCHAR(20),
       develrelease   VARCHAR(20),
       license        VARCHAR(20),
       summary	      TEXT,
       description    TEXT,
       leftvisit      INTEGER,
       rightvisit     INTEGER,

       PRIMARY KEY(id),
       UNIQUE INDEX(name)
);

CREATE TABLE packages (
       id             INTEGER NOT NULL,
       name	      VARCHAR(80) NOT NULL,
       virtual        INTEGER(1) DEFAULT '0',
       category       INTEGER, -- REFERENCES categories(id),
       stablerelease  VARCHAR(20),
       develrelease   VARCHAR(20),
       license        VARCHAR(20),
       summary	      TEXT,
       description    TEXT,

       PRIMARY KEY(id),
       UNIQUE INDEX(name)
);

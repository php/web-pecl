CREATE TABLE categories (
       id             INTEGER NOT NULL,
       parent         INTEGER,
       name	      VARCHAR(80) NOT NULL,
       summary	      TEXT,
       description    TEXT,
       npackages      INTEGER,
       leftvisit      INTEGER,
       rightvisit     INTEGER,

       PRIMARY KEY(id),
       UNIQUE INDEX(name)
);

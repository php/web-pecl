CREATE TABLE categories (
       id             INTEGER NOT NULL,
       parent         INTEGER,
       name	      VARCHAR(80) NOT NULL,
       summary	      TEXT,
       description    TEXT,
       npackages      INTEGER DEFAULT 0,
       pkg_left	      INTEGER,
       pkg_right      INTEGER,
       cat_left	      INTEGER,
       cat_right      INTEGER,

       PRIMARY KEY(id),
       UNIQUE INDEX(name)
);

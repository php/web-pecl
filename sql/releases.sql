CREATE TABLE releases (
       id	      INTEGER NOT NULL,
       package        INTEGER NOT NULL REFERENCES packages(id),
       version	      VARCHAR(20) NOT NULL,
       doneby	      VARCHAR(20) NOT NULL REFERENCES users(handle),
       license        VARCHAR(20),
       summary	      TEXT,
       description    TEXT,
       releasedate    DATETIME NOT NULL,
       releasenotes   TEXT DEFAULT '',
       maturity	      VARCHAR(20),
       md5sum	      VARCHAR(32),
       distfile	      VARCHAR(200),

       PRIMARY KEY(id),
       UNIQUE INDEX(package, version)
);

CREATE TABLE releases (
       id             INTEGER NOT NULL AUTO_INCREMENT,
       package        VARCHAR(80) NOT NULL REFERENCES packages(name),
       release	      VARCHAR(20) NOT NULL,
       releasedate    DATETIME,
       releasenotes   TEXT,

       PRIMARY KEY(id),
       UNIQUE(package, release)
);

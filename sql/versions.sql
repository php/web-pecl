CREATE TABLE versions (
       id             INTEGER NOT NULL AUTO_INCREMENT,
       package        VARCHAR(80) NOT NULL REFERENCES packages(name),
       version	      VARCHAR(20) NOT NULL,
       releasedate    DATETIME,
       releasenotes   TEXT,

       PRIMARY KEY(id),
       UNIQUE(package,version)
);

CREATE TABLE versions (
       package        VARCHAR(80) NOT NULL REFERENCES packages(name),
       version	      VARCHAR(20) NOT NULL,
       releasedate    DATETIME,
       releasenotes   TEXT,

       UNIQUE INDEX(package,version)
);

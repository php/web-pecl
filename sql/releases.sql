CREATE TABLE releases (
       id             INTEGER NOT NULL AUTO_INCREMENT,
       package        VARCHAR(80) NOT NULL REFERENCES packages(name),
       version	      VARCHAR(20) NOT NULL,
       doneby	      VARCHAR(20) NOT NULL REFERENCES users(handle),
       releasedate    DATETIME NOT NULL,
       releasenotes   TEXT DEFAULT '',

       PRIMARY KEY(id),
       UNIQUE INDEX(package, version)
);

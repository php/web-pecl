CREATE TABLE releases (
       package        VARCHAR(80) NOT NULL REFERENCES packages(name),
       version	      VARCHAR(20) NOT NULL,
       doneby	      VARCHAR(20) NOT NULL REFERENCES users(handle),
       releasedate    DATETIME NOT NULL,
       releasenotes   TEXT DEFAULT '',
       maturity	      VARCHAR(20),
       md5sum	      VARCHAR(32),
       distfile	      VARCHAR(200),

       PRIMARY KEY(package, version)
);

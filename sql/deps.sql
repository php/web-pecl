CREATE TABLE deps (
       package        VARCHAR(80) NOT NULL REFERENCES packages(name),
       version	      VARCHAR(20) NOT NULL,
       deps           VARCHAR(250),

       PRIMARY KEY(package, version)
);

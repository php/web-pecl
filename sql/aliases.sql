CREATE TABLE aliases (
       package	      VARCHAR(80) NOT NULL REFERENCES packages(name),
       alias	      VARCHAR(80) NOT NULL,
       PRIMARY KEY(package,alias)
);

CREATE TABLE files (
       id	      INTEGER NOT NULL,
       package        INTEGER NOT NULL REFERENCES packages(id),
       release	      INTEGER NOT NULL REFERENCES releases(id),
       platform	      VARCHAR(50),
       format	      VARCHAR(50),
       md5sum	      VARCHAR(32),
       basename	      VARCHAR(100),
       fullpath	      VARCHAR(250),

       PRIMARY KEY(id)
);

CREATE TABLE downloads (
       id             INTEGER NOT NULL,
       file	      INTEGER NOT NULL, -- REFERENCES files(id),
       package	      INTEGER NOT NULL, -- REFERENCES packages(id),
       release	      INTEGER NOT NULL, -- REFERENCES releases(id),
       dl_when	      DATETIME NOT NULL,
       dl_who	      VARCHAR(20),
       dl_host	      VARCHAR(100),

       PRIMARY KEY(id)
);

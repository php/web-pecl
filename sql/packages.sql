CREATE TABLE packages (
       name	      VARCHAR(80) NOT NULL,
       domain	      VARCHAR(80) NOT NULL REFERENCES domain(name),
       stablerelease  VARCHAR(20),
       develrelease   VARCHAR(20),
       copyright      VARCHAR(20),
       summary	      TEXT,
       description    TEXT,

       PRIMARY KEY(name)
);

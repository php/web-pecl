CREATE TABLE packages (
       name	      VARCHAR(80) NOT NULL,
       domain	      VARCHAR(80) NOT NULL,
       originator     VARCHAR(20) NOT NULL REFERENCES authors(handle),
       maintainer     VARCHAR(20) NOT NULL REFERENCES authors(handle),
       currentstable  VARCHAR(20),
       currentdevel   VARCHAR(20),
       copyright      VARCHAR(20) NOT NULL,
       summary	      TEXT,
       description    TEXT,

       PRIMARY KEY(name)
);

CREATE TABLE domains (
       domain	      VARCHAR(80) NOT NULL,
       administrator  VARCHAR(20) NOT NULL REFERENCES authors(handle),

       PRIMARY KEY(domain)
);

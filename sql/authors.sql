CREATE TABLE authors (
       handle	     VARCHAR(20) NOT NULL,
       password	     VARCHAR(64),
       name	     VARCHAR(100),
       email	     VARCHAR(100),
       homepage	     VARCHAR(255),
       created	     INTEGER,
       modified	     INTEGER,
       createdby     VARCHAR(20),
       showemail     BOOL,
       registered    BOOL,
       admin         BOOL,
       authorinfo    TEXT,

       PRIMARY KEY(handle)
);

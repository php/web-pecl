CREATE TABLE notes (
       id        INTEGER NOT NULL,
       uid       VARCHAR(20), -- REFERENCES users(handle),
       pid       INTEGER, -- REFERENCES packages(id),
       nby       VARCHAR(20) REFERENCES users(handle),
       ntime     DATETIME,
       note      TEXT,

       PRIMARY KEY(id),
       INDEX(uid),
       INDEX(pid)
);

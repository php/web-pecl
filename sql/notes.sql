CREATE TABLE notes (
       id        INTEGER NOT NULL,
       uid       VARCHAR(20), -- REFERENCES users(handle),
       pid       INTEGER, -- REFERENCES packages(id),
       rid       INTEGER, -- REFERENCES releases(id),
       cid       INTEGER, -- REFERENCES categories(id),
       nby       VARCHAR(20) REFERENCES users(handle),
       ntime     DATETIME,
       note      TEXT,

       PRIMARY KEY(id),
       INDEX(uid),
       INDEX(pid)
);

CREATE TABLE notes (
       id    INTEGER NOT NULL,
       uid   VARCHAR(20) REFERENCES users(handle),
       pid   INTEGER REFERENCES packages(id),

       PRIMARY KEY(id)
);

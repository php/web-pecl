CREATE TABLE cvs_acl (
    username        VARCHAR(20), -- NOT NULL REFERENCES users(handle),
    groupname       VARCHAR(20), -- NOT NULL REFERENCES users(handle),
    path            VARCHAR(250) NOT NULL,
    access          BOOL,

    UNIQUE INDEX(username,path)
);

CREATE TABLE cvs_acl (
    handle          VARCHAR(20) NOT NULL REFERENCES users(handle),
    path            VARCHAR(250) NOT NULL,
    access          BOOL,

    UNIQUE INDEX(handle,path)
);

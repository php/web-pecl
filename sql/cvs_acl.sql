CREATE TABLE cvs_acl (
    username        VARCHAR(20), -- NOT NULL REFERENCES users(handle),
    usertype	    ENUM('user','group') NOT NULL DEFAULT 'user',
    path            VARCHAR(250) NOT NULL,
    access          BOOL,

    UNIQUE INDEX(username,path)
);

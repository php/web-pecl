CREATE TABLE cvs_group_membership (
    groupname       VARCHAR(20) NOT NULL REFERENCES cvs_groups(name),
    username        VARCHAR(20) NOT NULL REFERENCES users(handle),
    granted_when    DATETIME,
    granted_by	    VARCHAR(20) NOT NULL REFERENCES users(handle),    

    UNIQUE INDEX(groupname, username)
);

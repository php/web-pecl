CREATE TABLE cvs_group_membership (
    groupname       VARCHAR(20) NOT NULL REFERENCES cvs_groups(name),
    username        VARCHAR(20) NOT NULL REFERENCES users(handle),
    member_since    DATETIME,
    approved_by	    VARCHAR(20) NOT NULL REFERENCES users(handle),    

    UNIQUE INDEX(groupname, username)
);

CREATE TABLE cvs_groups (
    groupname       VARCHAR(20) NOT NULL,
    description	    VARCHAR(250) NOT NULL,

    UNIQUE INDEX(groupname)
);

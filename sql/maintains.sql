CREATE TABLE maintains (
    handle      VARCHAR(20) NOT NULL REFERENCES users(handle),
    package     INTEGER NOT NULL REFERENCES packages(id),
    role        ENUM('lead', 'developer', 'contributor', 'helper') NOT NULL,

    PRIMARY KEY(handle,package)
);

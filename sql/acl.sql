CREATE TABLE package_acl (
    handle          VARCHAR(20) NOT NULL REFERENCES users(handle),
    package         VARCHAR(80) NOT NULL REFERENCES packages(name),
    access          INTEGER,

    UNIQUE INDEX(handle,package)
);

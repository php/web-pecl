CREATE TABLE maintains (
    handle      VARCHAR(20) NOT NULL REFERENCES users(handle),
    package     VARCHAR(20) NOT NULL REFERENCES packages(name),
    role        VARCHAR(40),

    UNIQUE INDEX(handle,package)
);

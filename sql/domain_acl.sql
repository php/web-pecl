CREATE TABLE domain_acl (
    handle          VARCHAR(20) NOT NULL REFERENCES users(handle),
    domain          VARCHAR(80) NOT NULL REFERENCES domains(id),
    access          INTEGER,

    UNIQUE INDEX(handle,domain)
);

CREATE TABLE domains (
    id               INTEGER NOT NULL AUTO_INCREMENT,
    name	     VARCHAR(80) NOT NULL,
    parent	     INTEGER,
    description      VARCHAR(120),

    PRIMARY KEY(id),
    INDEX(name)
);

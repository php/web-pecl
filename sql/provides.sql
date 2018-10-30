CREATE TABLE provides (
       release       INTEGER REFERENCES releases(id),
       type          ENUM('ext','prog','class','function','feature') NOT NULL,
       name          VARCHAR(200),
       INDEX(release)
);

CREATE TABLE state_order (
       state     VARCHAR(10) NOT NULL,
       order     INTEGER NOT NULL,
       PRIMARY KEY(state)
);

INSERT INTO state_order VALUES('stable', 0);
INSERT INTO state_order VALUES('beta', 1);
INSERT INTO state_order VALUES('alpha', 2);
INSERT INTO state_order VALUES('snapshot', 3);
INSERT INTO state_order VALUES('devel', 4);

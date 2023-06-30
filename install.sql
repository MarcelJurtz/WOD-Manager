START TRANSACTION;

CREATE TABLE accounts (
	id int NOT NULL AUTO_INCREMENT,
  	username varchar(50) NOT NULL,
  	password char(60) NOT NULL,
  	email varchar(100) NOT NULL,
    PRIMARY KEY (id)
);

CREATE TABLE log(
  id INT PRIMARY KEY AUTO_INCREMENT,
  created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  source VARCHAR(50),
  ip VARCHAR(45),
  params TEXT
);

CREATE TABLE login_log(
  id INT PRIMARY KEY AUTO_INCREMENT,
  created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  username VARCHAR(50),
  success TINYINT DEFAULT 0,
  ip VARCHAR(45)
);

CREATE TABLE wod(
  id INT PRIMARY KEY AUTO_INCREMENT,
  created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  designation VARCHAR(100),
  description VARCHAR(500),
  hashtags TEXT,
  permalink VARCHAR(36),
  notes TEXT,
  timecap_seconds INT
);

CREATE TABLE equipment(
  id INT PRIMARY KEY AUTO_INCREMENT,
  designation VARCHAR(25),
  displayname VARCHAR(75),
  supports_weight BIT NOT NULL DEFAULT 0,
  hashtags TEXT
);

CREATE TABLE wod_equipment(
  id INT PRIMARY KEY AUTO_INCREMENT,
  wod_id INT NOT NULL,
  equipment_id INT NOT NULL,
  FOREIGN KEY (wod_id) REFERENCES wod(id),
  FOREIGN KEY (equipment_id) REFERENCES equipment(id)
);

CREATE TABLE movement(
  id INT PRIMARY KEY AUTO_INCREMENT,
  designation VARCHAR(25),
  displayname VARCHAR(75),
  hashtags TEXT
);

CREATE TABLE wod_movement(
  id INT PRIMARY KEY AUTO_INCREMENT,
  wod_id INT NOT NULL,
  movement_id INT NOT NULL,
  FOREIGN KEY (wod_id) REFERENCES wod(id),
  FOREIGN KEY (movement_id) REFERENCES movement(id)
);


CREATE TABLE tag(
  id INT PRIMARY KEY AUTO_INCREMENT,
  designation VARCHAR(25),
  hashtags TEXT
);

CREATE TABLE wod_tag(
  id INT PRIMARY KEY AUTO_INCREMENT,
  wod_id INT NOT NULL,
  tag_id INT NOT NULL,
  FOREIGN KEY (wod_id) REFERENCES wod(id),
  FOREIGN KEY (tag_id) REFERENCES tag(id)
);

CREATE TABLE wod_weight(
  id INT PRIMARY KEY AUTO_INCREMENT,
  wod_id INT NOT NULL,
  equipment_id INT NOT NULL,
  display_order INT NOT NULL DEFAULT 0,
  weight_gender INT NOT NULL DEFAULT 0,
  weight_factor VARCHAR(50),
  weight_unit INT NOT NULL DEFAULT 0,
  weight FLOAT NOT NULL DEFAULT 0,
  notes TEXT
);


INSERT INTO accounts (id, username, password, email) VALUES (1, 'Marcel', '$2y$10$fmvOx4WQeqlDazluko8UPeqQ2b12PZJFmGNY18kN9WPpv2O3mF7km', 'jurtzmarcel@gmail.com');

-- Sample Entry
INSERT INTO tag(designation) VALUES ('Benchmark');

INSERT INTO equipment(designation, displayname, supports_weight) VALUES ('track', 'Track / Option for Running', 0);
INSERT INTO equipment(designation, displayname, supports_weight) VALUES ('kettlebell', 'Kettlebell', 1);
INSERT INTO equipment(designation, displayname, supports_weight) VALUES ('pullupbar', 'Pull-up-Bar', 0);
INSERT INTO equipment(designation, displayname, supports_weight) VALUES ('barbell', 'Barbell', 1);

INSERT INTO movement(designation, displayname, hashtags) VALUES ('running', 'Running', 'running, track, trackandfield, endurance, cardio');
INSERT INTO movement(designation, displayname, hashtags) VALUES ('kbswings', 'Kettlebell-Swings', 'kettlebell, kb, kbswings, kettlebellswings');
INSERT INTO movement(designation, displayname, hashtags) VALUES ('pullup', 'Pull-Up', 'pullup, chinup, kippings');
INSERT INTO movement(designation, displayname, hashtags) VALUES ('thruster', 'Thruster', 'thruster, lifting, squat, overhead');

INSERT INTO wod(designation, description, permalink) VALUES ('Fran', '21-15-9 Reps For Time', 'TEST-AS-1');
INSERT INTO wod(designation, description, permalink) VALUES ('Helen', '3 Rounds for Time', 'TEST-AS-2');

INSERT INTO wod_tag (wod_id, tag_id) VALUES(1,1);
INSERT INTO wod_tag (wod_id, tag_id) VALUES(2,1);

INSERT INTO wod_equipment (wod_id, equipment_id) VALUES(1,1);
INSERT INTO wod_equipment (wod_id, equipment_id) VALUES(1,2);
INSERT INTO wod_equipment (wod_id, equipment_id) VALUES(1,3);

INSERT INTO wod_equipment (wod_id, equipment_id) VALUES(2,3);
INSERT INTO wod_equipment (wod_id, equipment_id) VALUES(2,4);

INSERT INTO wod_movement (wod_id, movement_id) VALUES (1, 1);
INSERT INTO wod_movement (wod_id, movement_id) VALUES (1, 2);
INSERT INTO wod_movement (wod_id, movement_id) VALUES (1, 3);

INSERT INTO wod_movement (wod_id, movement_id) VALUES (2, 3);
INSERT INTO wod_movement (wod_id, movement_id) VALUES (2, 4);

INSERT INTO wod_weight (wod_id, equipment_id, display_order, weight_gender, weight_factor, weight_unit, weight, notes) VALUES (1, 2, 0, 1, 'RX', 3, 1.5, null); -- KB
INSERT INTO wod_weight (wod_id, equipment_id, display_order, weight_gender, weight_factor, weight_unit, weight, notes) VALUES (1, 2, 0, 2, 'RX', 3, 1, null); -- KB
INSERT INTO wod_weight (wod_id, equipment_id, display_order, weight_gender, weight_factor, weight_unit, weight, notes) VALUES (2, 4, 0, 1, 'RX', 1, 42.5, null); -- Thruster
INSERT INTO wod_weight (wod_id, equipment_id, display_order, weight_gender, weight_factor, weight_unit, weight, notes) VALUES (2, 4, 0, 2, 'RX', 1, 30, null); -- Thruster

COMMIT;
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
  notes VARCHAR(500),
  exercises TEXT,
  hashtags TEXT,
  permalink VARCHAR(36)
);

CREATE TABLE equipment(
  id INT PRIMARY KEY AUTO_INCREMENT,
  designation VARCHAR(25),
  displayname VARCHAR(75),
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

CREATE TABLE setting(
  id INT PRIMARY KEY AUTO_INCREMENT,
  systemname VARCHAR(50),
  displayname VARCHAR(50),
  value VARCHAR(500)
);

INSERT INTO accounts (id, username, password, email) VALUES (1, 'Marcel', '$2y$10$fmvOx4WQeqlDazluko8UPeqQ2b12PZJFmGNY18kN9WPpv2O3mF7km', 'jurtzmarcel@gmail.com');

-- Sample Entry
INSERT INTO tag(designation) VALUES ('Benchmark');
INSERT INTO equipment(designation, displayname) VALUES ('track', 'Track / Option for Running');
INSERT INTO equipment(designation, displayname) VALUES ('kettlebell', 'Kettlebell');
INSERT INTO equipment(designation, displayname) VALUES ('pullupbar', 'Pull-up-Bar');
INSERT INTO movement(designation, displayname) VALUES ('running', 'Run');
INSERT INTO movement(designation, displayname) VALUES ('kettlebell-swing', 'KB-Swing');
INSERT INTO movement(designation, displayname) VALUES ('pull-up', 'Pull-Up');
INSERT INTO wod(designation, description, notes, exercises, permalink) VALUES ('Helen', '3 Rounds for Time', 'RX: ♂ 50lb / ♀ 35lb', '400m Run, 21 Kettlebell-Swings, 12 Pull-Ups', '74BF4E7C');
INSERT INTO wod_tag (wod_id, tag_id) VALUES(1,1);
INSERT INTO wod_equipment (wod_id, equipment_id) VALUES(1,1);
INSERT INTO wod_equipment (wod_id, equipment_id) VALUES(1,2);
INSERT INTO wod_equipment (wod_id, equipment_id) VALUES(1,3);
INSERT INTO wod_movement (wod_id, movement_id) VALUES(1,1);
INSERT INTO wod_movement (wod_id, movement_id) VALUES(1,2);
INSERT INTO wod_movement (wod_id, movement_id) VALUES(1,3);

-- Config - See Keys in config.php
INSERT INTO setting(systemname, displayname) VALUES ('Config.Unsplash.AppId', 'Unsplash App-Id');
INSERT INTO setting(systemname, displayname) VALUES ('Config.Unsplash.AccessKey', 'Unsplash Access Key');
INSERT INTO setting(systemname, displayname) VALUES ('Config.Unsplash.SecretKey', 'Unsplash Secret Key');

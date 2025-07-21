-- Add date scheduling functionality to WOD Manager
-- This table handles the many-to-many relationship between workouts and dates
-- Includes gym linkage to avoid duplicates between gyms

CREATE TABLE wod_schedule(
  id INT PRIMARY KEY AUTO_INCREMENT,
  wod_id INT NOT NULL,
  gym_id INT NOT NULL,
  scheduled_date VARCHAR(8) NOT NULL, -- Format: YYYYMMDD (e.g., '20250721')
  created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  notes VARCHAR(200) DEFAULT NULL, -- Optional notes for this specific date
  FOREIGN KEY (wod_id) REFERENCES wod(id) ON DELETE CASCADE,
  FOREIGN KEY (gym_id) REFERENCES gym(id) ON DELETE CASCADE,
  INDEX idx_scheduled_date (scheduled_date),
  INDEX idx_gym_date (gym_id, scheduled_date),
  INDEX idx_wod_gym_date (wod_id, gym_id, scheduled_date),
  UNIQUE KEY unique_wod_gym_date (wod_id, gym_id, scheduled_date)
);

-- Add some sample data (ensuring gym exists and is enabled)
INSERT IGNORE INTO gym (designation, tag, enabled) VALUES ('Main Gym', 'main', 1);

-- Get the gym ID (this will work if gym already exists or was just created)
SET @gym_id = (SELECT id FROM gym WHERE designation = 'Main Gym' AND enabled = 1 LIMIT 1);

-- Only insert sample workouts if we have a valid gym
INSERT INTO wod_schedule (wod_id, gym_id, scheduled_date, notes) 
SELECT 1, @gym_id, '20250721', 'Monday benchmark workout' WHERE @gym_id IS NOT NULL
UNION ALL
SELECT 1, @gym_id, '20250728', 'Repeat Helen workout' WHERE @gym_id IS NOT NULL  
UNION ALL
SELECT 1, @gym_id, '20250804', 'Weekly Helen benchmark' WHERE @gym_id IS NOT NULL;

-- Add color customization fields to gym table
ALTER TABLE gym ADD COLUMN primary_color VARCHAR(7) DEFAULT '#667eea' AFTER enabled;
ALTER TABLE gym ADD COLUMN secondary_color VARCHAR(7) DEFAULT '#764ba2' AFTER primary_color;

-- Update existing gyms with default colors
UPDATE gym SET primary_color = '#667eea', secondary_color = '#764ba2' WHERE primary_color IS NULL;

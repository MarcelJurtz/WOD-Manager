# Daily Workout Scheduling Feature

## Overview
This feature adds date scheduling functionality to the WOD Manager, allowing workouts to be assigned to specific dates and accessed via a daily API endpoint.

## Database Changes

### New Table: `wod_schedule`
```sql
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
```

**Key Features:**
- **Gym Support**: Each scheduled workout is linked to a specific gym
- **No Duplicates**: Unique constraint prevents duplicate workout-gym-date combinations
- **Cascading Deletes**: Automatically cleans up schedules when workouts or gyms are deleted

## Features Added

### 1. Admin Interface Enhancements
- **Workout Edit Form**: Added date scheduling section with:
  - Dynamic date picker inputs
  - **Gym selection dropdown** for each scheduled date
  - Optional notes for each scheduled date
  - Add/remove date functionality
  - Automatic date sorting (by date, then gym)

- **Dashboard**: Added "Upcoming Scheduled Workouts" section showing:
  - Next 30 days of scheduled workouts
  - **Gym badges** for easy identification
  - Formatted dates (DD.MM.YYYY)
  - Quick edit and export links

### 2. Daily API Endpoint
**URL**: `/api/daily.php`

**Parameters**:
- `date` (optional): Date in YYYYMMDD format (defaults to today)
- `gym` (optional): Gym ID to filter by specific gym

**Example Requests**:
```
GET /api/daily.php                    // Today's workout (first gym alphabetically)
GET /api/daily.php?date=20250721      // Specific date workout (first gym)
GET /api/daily.php?gym=1              // Today's workout for gym ID 1
GET /api/daily.php?date=20250721&gym=1 // Specific date and gym
```

**Response Format**:
```json
{
  "id": 1,
  "designation": "Helen",
  "description": "3 Rounds for Time",
  "exercises": "400m Run, 21 Kettlebell-Swings, 12 Pull-Ups",
  "notes": "RX: ♂ 50lb / ♀ 35lb",
  "schedule_notes": "Monday benchmark workout",
  "hashtags": "wodaily, fitness, crossfit",
  "permalink": "74BF4E7C",
  "date": "20250721",
  "formatted_date": "21.07.2025",
  "gym": {
    "name": "Main Gym",
    "tag": "main"
  },
  "movements": [
    {"id": 1, "designation": "running", "displayname": "Run"},
    {"id": 2, "designation": "kettlebell-swing", "displayname": "KB-Swing"},
    {"id": 3, "designation": "pull-up", "displayname": "Pull-Up"}
  ],
  "equipment": [
    {"id": 1, "designation": "track", "displayname": "Track / Option for Running"},
    {"id": 2, "designation": "kettlebell", "displayname": "Kettlebell"},
    {"id": 3, "designation": "pullupbar", "displayname": "Pull-up-Bar"}
  ],
  "tags": [
    {"id": 1, "designation": "Benchmark"}
  ]
}
```

**Error Responses**:
- `400`: Invalid date format
- `404`: No workout scheduled for the specified date (and gym, if specified)

## Usage Scenarios

### 1. Recurring Workouts
The same workout can be scheduled multiple times across different gyms and dates:
- Weekly benchmark workouts (e.g., "Helen" every Monday at Main Gym)
- Different programming per gym location
- Seasonal programming variations

### 2. Multi-Gym Management
- **Gym-specific scheduling**: Each gym can have its own workout calendar
- **No conflicts**: The unique constraint prevents duplicate workouts on the same date at the same gym
- **Flexible programming**: Same workout can run at different gyms on the same day

### 2. API Integration
- Mobile apps can fetch daily workouts (with gym filtering)
- External websites can display scheduled workouts per gym
- Automated social media posting with gym-specific content

### 3. Planning & Management
- View upcoming workout schedule at a glance (with gym indicators)
- Easy scheduling from the admin interface with gym selection
- Notes for specific workout sessions
- **Gym-based filtering** for multi-location management

## Installation

1. Run the SQL script: `update_schedule.sql`
2. The admin interface and API endpoints are ready to use

## Benefits

- **Easy Readability**: Clean, intuitive UI for managing workout schedules with gym indicators
- **Flexibility**: Multiple dates per workout across different gym locations
- **No Duplicates**: Database constraints prevent scheduling conflicts within the same gym
- **Multi-Gym Support**: Perfect for gym chains or organizations with multiple locations
- **API Access**: External integration capabilities with gym filtering
- **Extensible**: Foundation for future features like gym-specific attendance tracking, location-based analytics, etc.

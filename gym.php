<?php
require_once('admin/config.php');

// Get the gym tag from the URL path
$request_uri = $_SERVER['REQUEST_URI'];
$base_path = '/' . ROOT_FOLDER . '/';
$gym_tag = str_replace($base_path, '', $request_uri);

// Remove any trailing slashes or query parameters
$gym_tag = trim($gym_tag, '/');
if (strpos($gym_tag, '?') !== false) {
    $gym_tag = substr($gym_tag, 0, strpos($gym_tag, '?'));
}

// If no gym tag provided, show error
if (empty($gym_tag)) {
    showNoWorkout("No gym specified");
    exit;
}

// Connect to database
$con = getConnection();

// Get gym by tag and check if it's enabled
$stmt = $con->prepare('SELECT id, designation, enabled, primary_color, secondary_color FROM gym WHERE tag = ? LIMIT 1');
$stmt->bind_param('s', $gym_tag);
$stmt->execute();
$stmt->bind_result($gym_id, $gym_name, $gym_enabled, $primary_color, $secondary_color);
$found = $stmt->fetch();
$stmt->close();

if (!$found) {
    showNoWorkout("Studio nicht gefunden", '#667eea', '#764ba2');
    exit;
}

if ($gym_enabled != 1) {
    showNoWorkout("Studio nicht gefunden", $primary_color ?: '#667eea', $secondary_color ?: '#764ba2');
    exit;
}

// Get today's workout for this gym with movements and equipment
$today = date('Ymd');
$stmt = $con->prepare('
    SELECT w.id, w.designation, w.description, w.exercises, ws.notes as schedule_notes
    FROM wod w 
    INNER JOIN wod_schedule ws ON w.id = ws.wod_id 
    WHERE ws.gym_id = ? AND ws.scheduled_date = ?
    LIMIT 1
');
$stmt->bind_param('is', $gym_id, $today);
$stmt->execute();
$stmt->bind_result($wod_id, $designation, $description, $exercises, $schedule_notes);
$found_workout = $stmt->fetch();
$stmt->close();

if (!$found_workout) {
    showNoWorkout("Für heute wurde bisher keine Einheit hinterlegt.", $primary_color ?: '#667eea', $secondary_color ?: '#764ba2');
    exit;
}

// Get movements for this workout
$stmt = $con->prepare('
    SELECT m.displayname, m.designation
    FROM movement m 
    INNER JOIN wod_movement wm ON m.id = wm.movement_id 
    WHERE wm.wod_id = ?
    ORDER BY m.displayname
');
$stmt->bind_param('i', $wod_id);
$stmt->execute();
$result = $stmt->get_result();
$movements = array();
while ($row = $result->fetch_assoc()) {
    $movements[] = $row;
}
$stmt->close();

// Get equipment for this workout
$stmt = $con->prepare('
    SELECT e.displayname, e.designation
    FROM equipment e 
    INNER JOIN wod_equipment we ON e.id = we.equipment_id 
    WHERE we.wod_id = ?
    ORDER BY e.displayname
');
$stmt->bind_param('i', $wod_id);
$stmt->execute();
$result = $stmt->get_result();
$equipment = array();
while ($row = $result->fetch_assoc()) {
    $equipment[] = $row;
}
$stmt->close();

// Show the workout
showWorkout($gym_name, $designation, $description, $exercises, $schedule_notes, $movements, $equipment, $primary_color ?: '#667eea', $secondary_color ?: '#764ba2');

function getConnection() {        
    $con = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $con->set_charset("utf8mb4");
    if (mysqli_connect_errno()) {
        showNoWorkout("Database connection error", '#667eea', '#764ba2');
        exit;
    }
    return $con;
}

function hexToRgb($hex) {
    $hex = ltrim($hex, '#');
    if (strlen($hex) == 6) {
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        return "$r, $g, $b";
    }
    return "102, 126, 234"; // fallback
}

function lightenColor($hex, $percent) {
    $hex = ltrim($hex, '#');
    if (strlen($hex) != 6) return $hex;
    
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));
    
    $r = min(255, $r + ($percent * 255 / 100));
    $g = min(255, $g + ($percent * 255 / 100));
    $b = min(255, $b + ($percent * 255 / 100));
    
    return sprintf("#%02x%02x%02x", $r, $g, $b);
}

function formatDateGerman($timestamp = null) {
    if ($timestamp === null) {
        $timestamp = time();
    }
    
    $dayNames = [
        'Sunday' => 'Sonntag',
        'Monday' => 'Montag',
        'Tuesday' => 'Dienstag',
        'Wednesday' => 'Mittwoch',
        'Thursday' => 'Donnerstag',
        'Friday' => 'Freitag',
        'Saturday' => 'Samstag'
    ];
    
    $monthNames = [
        'January' => 'Januar',
        'February' => 'Februar',
        'March' => 'März',
        'April' => 'April',
        'May' => 'Mai',
        'June' => 'Juni',
        'July' => 'Juli',
        'August' => 'August',
        'September' => 'September',
        'October' => 'Oktober',
        'November' => 'November',
        'December' => 'Dezember'
    ];
    
    $dayName = date('l', $timestamp);
    $monthName = date('F', $timestamp);
    $day = date('j', $timestamp);
    $year = date('Y', $timestamp);
    
    $germanDay = $dayNames[$dayName] ?? $dayName;
    $germanMonth = $monthNames[$monthName] ?? $monthName;
    
    return $germanDay . ', ' . $day . '. ' . $germanMonth . ' ' . $year;
}

function showNoWorkout($message, $primary_color = '#667eea', $secondary_color = '#764ba2') {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Keine Einheit geplant</title>
        <link rel="stylesheet" href="./assets/vendor/bootstrap-5.2.1-dist/css/bootstrap.min.css">
        <link rel="stylesheet" href="./assets/vendor/fontawesome-free-6.2.0-web/css/all.min.css">
        <style>
            body { 
                background: linear-gradient(135deg, <?= $primary_color ?> 0%, <?= $secondary_color ?> 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                font-family: 'Arial', sans-serif;
            }
            .workout-card {
                background: rgba(255, 255, 255, 0.95);
                border-radius: 20px;
                padding: 3rem;
                box-shadow: 0 20px 40px rgba(0,0,0,0.1);
                text-align: center;
                max-width: 500px;
                width: 90%;
            }
        </style>
    </head>
    <body>
        <div class="workout-card">
            <i class="fas fa-calendar-times fa-4x text-muted mb-4"></i>
            <h2 class="text-muted mb-3">Keine Einheit geplant</h2>
            <p class="text-muted"><?= htmlspecialchars($message) ?></p>
        </div>
    </body>
    </html>
    <?php
}

function showWorkout($gym_name, $designation, $description, $exercises, $schedule_notes, $movements, $equipment, $primary_color = '#667eea', $secondary_color = '#764ba2') {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= htmlspecialchars($designation) ?> - <?= htmlspecialchars($gym_name) ?></title>
        <link rel="stylesheet" href="./assets/vendor/bootstrap-5.2.1-dist/css/bootstrap.min.css">
        <link rel="stylesheet" href="./assets/vendor/fontawesome-free-6.2.0-web/css/all.min.css">
        <style>
            body { 
                background: linear-gradient(135deg, <?= $primary_color ?> 0%, <?= $secondary_color ?> 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                font-family: 'Arial', sans-serif;
                padding: 20px;
            }
            .workout-card {
                background: rgba(255, 255, 255, 0.95);
                border-radius: 20px;
                padding: 3rem;
                box-shadow: 0 20px 40px rgba(0,0,0,0.1);
                max-width: 600px;
                width: 100%;
            }
            .gym-badge {
                background: linear-gradient(45deg, <?= $primary_color ?>, <?= $secondary_color ?>);
                color: white;
                padding: 0.5rem 1rem;
                border-radius: 25px;
                font-weight: bold;
                margin-bottom: 1rem;
                display: inline-block;
            }
            .workout-title {
                color: #333;
                font-weight: bold;
                margin-bottom: 1rem;
            }
            .workout-description {
                color: #666;
                font-size: 1.1rem;
                margin-bottom: 1.5rem;
            }
            .exercises-box {
                background: rgba(<?= hexToRgb($primary_color) ?>, 0.1);
                border-left: 4px solid <?= $primary_color ?>;
                padding: 1rem;
                border-radius: 5px;
                margin: 1rem 0;
            }
            .tag-container {
                display: flex;
                flex-wrap: wrap;
                gap: 0.5rem;
                margin-top: 0.5rem;
            }
            .movement-tag {
                background: rgba(<?= hexToRgb($primary_color) ?>, 0.15);
                color: <?= $primary_color ?>;
                border: 1px solid rgba(<?= hexToRgb($primary_color) ?>, 0.3);
                padding: 0.25rem 0.5rem;
                border-radius: 12px;
                font-size: 0.75rem;
                font-weight: 500;
                display: inline-block;
            }
            .equipment-tag {
                background: rgba(<?= hexToRgb($primary_color) ?>, 0.15);
                color: <?= $primary_color ?>;
                border: 1px solid rgba(<?= hexToRgb($primary_color) ?>, 0.3);
                padding: 0.25rem 0.5rem;
                border-radius: 12px;
                font-size: 0.75rem;
                font-weight: 500;
                display: inline-block;
            }
            .section-title {
                color: #333;
                font-weight: 600;
                margin-bottom: 0.75rem;
                display: flex;
                align-items: center;
                gap: 0.5rem;
                font-size: 0.9rem;
            }
            .exercise-list {
                list-style: none;
                padding: 0;
                margin: 0;
            }
            .exercise-list li {
                position: relative;
                padding-left: 1rem;
                margin-bottom: 0.5rem;
                line-height: 1.4;
            }
            .exercise-list li:before {
                content: "•";
                color: <?= $primary_color ?>;
                font-weight: bold;
                position: absolute;
                left: 0;
            }
        </style>
    </head>
    <body>
        <div class="workout-card">
            <div class="text-center">
                <div class="gym-badge">
                    <i class="fas fa-dumbbell me-2"></i><?= htmlspecialchars($gym_name) ?>
                </div>
                <h1 class="workout-title"><?= htmlspecialchars($designation) ?></h1>
                <p class="workout-description"><?= htmlspecialchars($description) ?></p>
            </div>
            
            <?php if (!empty($exercises)): ?>
                <div class="exercises-box">
                    <div class="section-title">
                        <i class="fas fa-list-ul"></i>
                        <span>Exercises</span>
                    </div>
                    <ul class="exercise-list">
                        <?php 
                        $exercise_items = array_map('trim', explode(',', $exercises));
                        foreach ($exercise_items as $exercise): 
                            if (!empty($exercise)):
                        ?>
                            <li><?= htmlspecialchars($exercise) ?></li>
                        <?php 
                            endif;
                        endforeach; 
                        ?>
                    </ul>
                    
                    <?php if (!empty($movements)): ?>
                        <div class="section-title">
                            <i class="fas fa-running"></i>
                            <span>Movements</span>
                        </div>
                        <div class="tag-container">
                            <?php foreach ($movements as $movement): ?>
                                <span class="movement-tag">
                                    <?= htmlspecialchars($movement['displayname']) ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($equipment)): ?>
                        <div class="section-title" style="margin-top: 1rem;">
                            <i class="fas fa-tools"></i>
                            <span>Equipment</span>
                        </div>
                        <div class="tag-container">
                            <?php foreach ($equipment as $equip): ?>
                                <span class="equipment-tag">
                                    <?= htmlspecialchars($equip['displayname']) ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($schedule_notes)): ?>
                <div class="exercises-box" style="background: rgba(<?= hexToRgb($secondary_color) ?>, 0.1); border-left-color: <?= $secondary_color ?>;">
                    <div class="section-title">
                        <i class="fas fa-info-circle"></i>
                        <span>Hinweis</span>
                    </div>
                    <div style="color: #555; line-height: 1.5;"><?= htmlspecialchars($schedule_notes) ?></div>
                </div>
            <?php endif; ?>
            
            <div class="text-center mt-4">
                <small class="text-muted">
                    <i class="fas fa-calendar me-1"></i><?= formatDateGerman() ?>
                </small>
            </div>
        </div>
    </body>
    </html>
    <?php
}
?>

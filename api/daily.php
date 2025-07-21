<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/' . ROOT_FOLDER . '/admin/config.php');

$con = getConnection();
logApiAccess($con);

// Get parameters
$date = isset($_GET['date']) ? $_GET['date'] : date('Ymd');
$gym_id = isset($_GET['gym']) ? (int)$_GET['gym'] : null;

// Validate date format
if (!preg_match('/^\d{8}$/', $date)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid date format. Use YYYYMMDD.']);
$workout = getDailyWorkout($con, $date, $gym_id);

if (!$workout) {
    http_response_code(404);
    echo json_encode(['error' => 'No workout scheduled for this date' . ($gym_id ? ' and gym' : '')]);
    exit;
}

http_response_code(200);
echo json_encode($workout);
exit;

function getConnection() {        
    $con = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $con->set_charset("utf8mb4");
    if (mysqli_connect_errno()) {
        exit('Failed to connect to MySQL: ' . mysqli_connect_error());
    }
    return $con;
}

function getDailyWorkout($con, $date, $gym_id = null) {
    // Base query with gym support - includes schedule notes
    $sql = '
        SELECT w.id, w.designation, w.description, w.exercises, w.hashtags, w.permalink,
               ws.notes as schedule_notes, ws.scheduled_date,
               g.designation as gym_name, g.tag as gym_tag
        FROM wod w 
        INNER JOIN wod_schedule ws ON w.id = ws.wod_id 
        INNER JOIN gym g ON ws.gym_id = g.id
        WHERE ws.scheduled_date = ? AND g.enabled = 1
    ';
    
    if ($gym_id) {
        $sql .= ' AND g.id = ?';
        $stmt = $con->prepare($sql);
        $stmt->bind_param('si', $date, $gym_id);
    } else {
        $sql .= ' ORDER BY g.designation ASC LIMIT 1';
        $stmt = $con->prepare($sql);
        $stmt->bind_param('s', $date);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $stmt->close();
        return null;
    }
    
    $workout = $result->fetch_assoc();
    $wod_id = $workout['id'];
    
    // Get movements
    $stmt = $con->prepare('
        SELECT m.id, m.designation, m.displayname 
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
    
    // Get equipment
    $stmt = $con->prepare('
        SELECT e.id, e.designation, e.displayname 
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
    
    // Get tags
    $stmt = $con->prepare('
        SELECT t.id, t.designation 
        FROM tag t 
        INNER JOIN wod_tag wt ON t.id = wt.tag_id 
        WHERE wt.wod_id = ?
        ORDER BY t.designation
    ');
    $stmt->bind_param('i', $wod_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $tags = array();
    while ($row = $result->fetch_assoc()) {
        $tags[] = $row;
    }
    $stmt->close();
    
    // Format response - includes schedule notes
    return array(
        'id' => (int)$workout['id'],
        'designation' => $workout['designation'],
        'description' => $workout['description'],
        'exercises' => $workout['exercises'],
        'schedule_notes' => $workout['schedule_notes'],
        'hashtags' => $workout['hashtags'],
        'permalink' => $workout['permalink'],
        'date' => $workout['scheduled_date'],
        'formatted_date' => formatDateForDisplay($workout['scheduled_date']),
        'gym' => array(
            'name' => $workout['gym_name'],
            'tag' => $workout['gym_tag']
        ),
        'movements' => $movements,
        'equipment' => $equipment,
        'tags' => $tags
    );
}

function formatDateForDisplay($dateString) {
    if (strlen($dateString) === 8) {
        $year = substr($dateString, 0, 4);
        $month = substr($dateString, 4, 2);
        $day = substr($dateString, 6, 2);
        return $day . '.' . $month . '.' . $year;
    }
    return $dateString;
}

function logApiAccess($con) {
    $source = 'daily-api';
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $params = json_encode($_GET);
    
    $stmt = $con->prepare('INSERT INTO log (source, ip, params) VALUES (?, ?, ?)');
    $stmt->bind_param('sss', $source, $ip, $params);
    $stmt->execute();
    $stmt->close();
}

?>

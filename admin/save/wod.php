<?php
session_start();

if (!isset($_SESSION['loggedin'])) {
	header('Location: ./../index.html');
	exit;
}

if (!isset($_POST['id'])) {
	exit('No ID specified');
}

require_once('./../shared/db.php');
$con = getConnection();

$wod = $_POST['id'];

if($_POST['id'] > 0) {

	// No Update!
	//$permalink = strtoupper(substr(sha1($_POST["designation"] . $_POST["description"] . $_POST["exercises"]),0,8));

	$stmt = $con->prepare('UPDATE wod SET designation = ?, description = ?, notes = ?, exercises = ?, hashtags = ? WHERE id = ?');
	$stmt->bind_param('sssssi', $_POST["designation"], $_POST["description"], $_POST["notes"], $_POST["exercises"], $_POST["hashtags"], $_POST["id"]);
	$status = $stmt->execute();
	$stmt->close();
} else {

	// Generate permalink only once to prevent breaking from later changes
	$permalink = strtoupper(substr(sha1($_POST["designation"] . $_POST["description"] . $_POST["exercises"]),0,8));
	$stmt = $con->prepare('INSERT INTO wod (designation, description, notes, exercises, hashtags, permalink) VALUES (?,?,?,?,?,?)');
	$stmt->bind_param('ssssss', $_POST["designation"], $_POST["description"], $_POST["notes"], $_POST["exercises"], $_POST["hashtags"], $permalink);
	$status = $stmt->execute();
	$wod = $con->insert_id;
	$stmt->close();
}

// Update movements
$stmt = $con->prepare('DELETE FROM wod_movement WHERE wod_id = ?');
$stmt->bind_param('i', $_POST["id"]);
$status = $stmt->execute();
$stmt->close();

if (isset($_POST['movement'])) {
	$template = 'INSERT INTO wod_movement (wod_id, movement_id) VALUES ';

	foreach ($_POST['movement'] as &$id) {
		$template = $template . '(' . $wod . ', ' . $id . '),';
	}

	$stmt = $con->prepare(rtrim($template, ","));
	$status = $stmt->execute();
	$stmt->close();
}


// Update equipment
$stmt = $con->prepare('DELETE FROM wod_equipment WHERE wod_id = ?');
$stmt->bind_param('i', $_POST["id"]);
$status = $stmt->execute();
$stmt->close();

if (isset($_POST['equipment'])) {
	$template = 'INSERT INTO wod_equipment (wod_id, equipment_id) VALUES ';

	foreach ($_POST['equipment'] as &$id) {
		$template = $template . '(' . $wod . ', ' . $id . '),';
	}

	$stmt = $con->prepare(rtrim($template, ","));
	$status = $stmt->execute();
	$stmt->close();
}


// Update tags
$stmt = $con->prepare('DELETE FROM wod_tag WHERE wod_id = ?');
$stmt->bind_param('i', $_POST["id"]);
$status = $stmt->execute();
$stmt->close();

if (isset($_POST['tags'])) {
	$template = 'INSERT INTO wod_tag (wod_id, tag_id) VALUES ';

	foreach ($_POST['tags'] as &$id) {
		$template = $template . '(' . $wod . ', ' . $id . '),';
	}

	$stmt = $con->prepare(rtrim($template, ","));
	$status = $stmt->execute();
	$stmt->close();
}

// Update scheduled dates
$stmt = $con->prepare('DELETE FROM wod_schedule WHERE wod_id = ?');
$stmt->bind_param('i', $wod);
$status = $stmt->execute();
$stmt->close();

if (isset($_POST['scheduled_dates']) && is_array($_POST['scheduled_dates'])) {
	foreach ($_POST['scheduled_dates'] as $index => $date) {
		if (!empty($date) && isset($_POST['scheduled_gyms'][$index]) && !empty($_POST['scheduled_gyms'][$index])) {
			$formatted_date = formatDateForStorage($date);
			$gym_id = $_POST['scheduled_gyms'][$index];
			$notes = isset($_POST['scheduled_notes'][$index]) ? $_POST['scheduled_notes'][$index] : null;
			
			$stmt = $con->prepare('INSERT INTO wod_schedule (wod_id, gym_id, scheduled_date, notes) VALUES (?, ?, ?, ?)');
			$stmt->bind_param('iiss', $wod, $gym_id, $formatted_date, $notes);
			$stmt->execute();
			$stmt->close();
		}
	}
}

header('Location: ./../index.php');

function formatDateForStorage($dateString) {
    // Convert YYYY-MM-DD to YYYYMMDD format
    if (strpos($dateString, '-') !== false) {
        return str_replace('-', '', $dateString);
    }
    return $dateString;
}
?>
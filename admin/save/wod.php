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

	$stmt = $con->prepare('UPDATE wod SET designation = ?, description = ?, exercises = ?, hashtags = ? WHERE id = ?');
	$stmt->bind_param('ssssi', $_POST["designation"], $_POST["description"], $_POST["exercises"], $_POST["hashtags"], $_POST["id"]);
	$status = $stmt->execute();
	$stmt->close();
} else {

	// Generate permalink only once to prevent breaking from later changes
	$permalink = strtoupper(substr(sha1($_POST["designation"] . $_POST["description"] . $_POST["exercises"]),0,8));
	$wod = $con->insert_id;
	$stmt = $con->prepare('INSERT INTO wod (designation, description, exercises, hashtags, permalink) VALUES (?,?,?,?,?)');
	$stmt->bind_param('ssss', $_POST["designation"], $_POST["description"], $_POST["exercises"], $_POST["hashtags"], $permalink);
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
	$template = 'INSERT INTO wod_tag (wod_id, tag_id) VALUES '; // TODO SqlInjection

	foreach ($_POST['tags'] as &$id) {
		$template = $template . '(' . $wod . ', ' . $id . '),';
	}

	$stmt = $con->prepare(rtrim($template, ","));
	$status = $stmt->execute();
	$stmt->close();
}

header('Location: ./../index.php');
?>
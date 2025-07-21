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

// Handle checkbox value (0 if unchecked, 1 if checked)
$enabled = isset($_POST['enabled']) && $_POST['enabled'] == '1' ? 1 : 0;

// Validate color values (ensure they are valid hex colors)
$primary_color = isset($_POST['primary_color']) && preg_match('/^#[0-9A-Fa-f]{6}$/', $_POST['primary_color']) 
    ? $_POST['primary_color'] : '#667eea';
$secondary_color = isset($_POST['secondary_color']) && preg_match('/^#[0-9A-Fa-f]{6}$/', $_POST['secondary_color']) 
    ? $_POST['secondary_color'] : '#764ba2';

if($_POST['id'] > 0) {
	$stmt = $con->prepare('UPDATE gym SET designation = ?, tag = ?, enabled = ?, primary_color = ?, secondary_color = ? WHERE id = ?');
	$stmt->bind_param('ssissi', $_POST["designation"], $_POST["tag"], $enabled, $primary_color, $secondary_color, $_POST["id"]);
	$status = $stmt->execute();
	$stmt->close();
} else {
	$stmt = $con->prepare('INSERT INTO gym (designation, tag, enabled, primary_color, secondary_color) VALUES (?,?,?,?,?)');
	$stmt->bind_param('ssiss', $_POST["designation"], $_POST["tag"], $enabled, $primary_color, $secondary_color);
	$status = $stmt->execute();
	$stmt->close();
}

header('Location: ./../index.php');
?>
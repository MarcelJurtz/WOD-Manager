<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
	header('Location: index.html');
	exit;
}

if (!isset($_POST['id'])) {
	exit('No ID specified');
}

if (!isset($_POST['newpassword']) || !isset($_POST['newpassword2']) || $_POST['newpassword'] != $_POST['newpassword2']) {
	exit('Passwords don\'t match');
}

require_once('db.php');
$con = getConnection();

$stmt = $con->prepare('UPDATE accounts SET password = ? WHERE id = ?');
$pw = password_hash($_POST['newpassword'], PASSWORD_BCRYPT);
$stmt->bind_param('si', $pw, $_POST["id"]);
$status = $stmt->execute();
$stmt->close();

header('Location: profile.php');
?>
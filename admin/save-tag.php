<?php
session_start();

if (!isset($_SESSION['loggedin'])) {
	header('Location: index.html');
	exit;
}

if (!isset($_POST['id'])) {
	exit('No ID specified');
}

require_once('db.php');
$con = getConnection();

if($_POST['id'] > 0) {
	$stmt = $con->prepare('UPDATE tag SET designation = ? WHERE id = ?');
	$stmt->bind_param('si', $_POST["designation"], $_POST["id"]);
	$status = $stmt->execute();
	$stmt->close();
} else {
	$stmt = $con->prepare('INSERT INTO tag (designation) VALUES (?)');
	$stmt->bind_param('s', $_POST["designation"]);
	$status = $stmt->execute();
	$stmt->close();
}

header('Location: index.php');
?>
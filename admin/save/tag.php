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

if($_POST['id'] > 0) {
	$stmt = $con->prepare('UPDATE tag SET designation = ?, hashtags = ? WHERE id = ?');
	$stmt->bind_param('ssi', $_POST["designation"], $_POST["hashtags"], $_POST["id"]);
	$status = $stmt->execute();
	$stmt->close();
} else {
	$stmt = $con->prepare('INSERT INTO tag (designation, hashtags) VALUES (?,?)');
	$stmt->bind_param('ss', $_POST["designation"], $_POST["hashtags"]);
	$status = $stmt->execute();
	$stmt->close();
}

header('Location: ./../index.php');
?>
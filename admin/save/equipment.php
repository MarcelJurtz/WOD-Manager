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
	$stmt = $con->prepare('UPDATE equipment SET designation = ?, displayname = ? WHERE id = ?');
	$stmt->bind_param('ssi', $_POST["designation"], $_POST["displayname"], $_POST["id"]);
	$status = $stmt->execute();
	$stmt->close();
} else {
	$stmt = $con->prepare('INSERT INTO equipment (designation, displayname) VALUES (?,?)');
	$stmt->bind_param('ss', $_POST["designation"], $_POST["displayname"],);
	$status = $stmt->execute();
	$stmt->close();
}

header('Location: ./../index.php');
?>
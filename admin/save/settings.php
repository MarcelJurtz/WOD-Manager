<?php
session_start();

if (!isset($_SESSION['loggedin'])) {
	header('Location: ./../index.html');
	exit;
}

require_once('./../shared/db.php');
$con = getConnection();

foreach($_POST as $key => $value)
{
	// TODO Might be enough for reusability
	$stmt = $con->prepare('UPDATE setting SET value = ? WHERE systemname = ?');

	$systemname = str_replace("_", ".", $key);

	$stmt->bind_param('ss', $value, $systemname);
	$status = $stmt->execute();
	$stmt->close();
}

header('Location: ./../settings.php');
?>
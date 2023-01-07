<?php
session_start();

require_once('db.php');
$con = getConnection();

if ( !isset($_POST['username'], $_POST['password']) ) {
	exit('Please fill both the username and password fields!');
}

if ($stmt = $con->prepare('SELECT id, password FROM accounts WHERE username = ?')) {
	$stmt->bind_param('s', $_POST['username']);
	$stmt->execute();
	$stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $password);
        $stmt->fetch();
        if (password_verify($_POST['password'], $password)) {
            logLogin($con, 1);
            session_regenerate_id();
            $_SESSION['loggedin'] = TRUE;
            $_SESSION['name'] = $_POST['username'];
            $_SESSION['id'] = $id;
            header('Location: ./../index.php');
        } else {
            logLogin($con, 0);
            echo 'Incorrect username and/or password!';
        }
    } else {
        echo 'Incorrect username and/or password!';
    }

	$stmt->close();
}

function logLogin($con, $success) {
	$stmt = $con->prepare('INSERT INTO login_log (username, success, ip) VALUES (?,?,?)');
    $stmt->bind_param('sis', $_POST["username"], $success, $_SERVER['REMOTE_ADDR']);
	$stmt->execute();
	$stmt->close();
}
?>
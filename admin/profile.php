<?php
session_start();

if (!isset($_SESSION['loggedin'])) {
	header('Location: index.html');
	exit;
}
require_once('db.php');
$con = getConnection();

$stmt = $con->prepare('SELECT id, password, email FROM accounts WHERE id = ?');
$stmt->bind_param('i', $_SESSION['id']);
$stmt->execute();
$stmt->bind_result($id, $password, $email);
$stmt->fetch();
$stmt->close();
?>

<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>Profile Page</title>
		<link href="admin.css" rel="stylesheet" type="text/css">
		<link rel="stylesheet" href="./../vendor/fontawesome-free-5.15.4-web/css/all.min.css">
        <link rel="stylesheet" href="./../assets/css/bootstrap-5.0.0-beta3/bootstrap.min.css">
	</head>
	<body class="loggedin">
		<nav class="navtop">
			<?php
                include('./menu.php');
            ?>
		</nav>
		<div class="content">
            <div class="container">
                <div class="row">
                    <div class="col-12"><h2>Dein Profil</h2></div>
                </div>
            </div>
			<div>
				<table>
					<tr>
						<td>Benutzername:</td>
						<td><?=$_SESSION['name']?></td>
					</tr>
					<tr>
						<td>Passwort:</td>
						<td><?=$password?></td>
					</tr>
					<tr>
						<td>E-Mail:</td>
						<td><?=$email?></td>
					</tr>
				</table>
                <h3>Passwort Ändern</h3>
                <form action="set-password.php" method="post">
                <input type="hidden" name="id" value="<?=$id?>">
                <div class="form-group">
                    <label for="newpassword">Neues Passwort</label>
                    <input type="password" class="form-control" id="newpassword" name="newpassword" placeholder="Neues Password" required>
                </div>
                <div class="form-group">
                    <label for="newpassword2">Neues Passwort</label>
                    <input type="password" class="form-control" id="newpassword2" name="newpassword2" placeholder="Neues Password (bestätigen)" required>
                </div>
				
				<input type="submit" value="Speichern">
			</form>
			</div>
		</div>
	</body>
</html>
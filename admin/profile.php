<?php
session_start();

if (!isset($_SESSION['loggedin'])) {
	header('Location: ./../index.html');
	exit;
}
require_once('./shared/db.php');
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
<?php include('./shared/head.inc.php'); ?>

<body class="loggedin">
	<nav class="navtop">
		<?php include('./shared/menu.inc.php'); ?>
	</nav>
	<div class="content">
		<div class="p-2 container-fluid card">
			<div class="card-body">
				<h2 class="card-title mb-3">Profil</h2>
				<table>
					<tr>
						<td>Benutzername:</td>
						<td><?= $_SESSION['name'] ?></td>
					</tr>
					<tr>
						<td>Passwort:</td>
						<td><?= $password ?></td>
					</tr>
					<tr>
						<td>E-Mail:</td>
						<td><?= $email ?></td>
					</tr>
				</table>
				<h3 class="my-3">Passwort Ändern</h3>
				<form action="set-password.php" method="post">
					<input type="hidden" name="id" value="<?= $id ?>">
					<div class="form-group mb-3">
						<label for="newpassword">Neues Passwort</label>
						<input type="password" class="form-control" id="newpassword" name="newpassword" placeholder="Neues Password" required>
					</div>
					<div class="form-group mb-3">
						<label for="newpassword2">Neues Passwort</label>
						<input type="password" class="form-control" id="newpassword2" name="newpassword2" placeholder="Neues Password (bestätigen)" required>
					</div>

					<input type="submit" value="Speichern">
				</form>
			</div>
		</div>
	</div>
</body>

</html>
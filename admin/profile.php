<?php
session_start();

if (!isset($_SESSION['loggedin'])) {
	header('Location: ./index.html');
	exit;
}
require_once('./shared/db.php');
$con = getConnection();

$stmt = $con->prepare('SELECT id, email FROM accounts WHERE id = ?');
$stmt->bind_param('i', $_SESSION['id']);
$stmt->execute();
$stmt->bind_result($id, $email);
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
	<div class="container card my-3">
		<form action="./save/password.php" method="post">

			<input type="hidden" name="id" value="<?= $id ?>">

			<div class="row">
				<div class="col-12">
					<div class="d-flex justify-content-between border-bottom py-3 mb-3">
						<h2>Profile</h2>
						<div>
							<a class="btn btn-outline-danger" href="./index.php">Cancel</a>
							<input class="btn btn-outline-success" type="submit" value="Save">
						</div>
					</div>
				</div>
			</div>

			<div class="row">
				<div class="col-12">
					<table>
						<tr>
							<td>Username:</td>
							<td><?= $_SESSION['name'] ?></td>
						</tr>
						<tr>
							<td>E-Mail:</td>
							<td><?= $email ?></td>
						</tr>
					</table>
					<h3 class="my-3">Change Password</h3>
					<input type="hidden" name="id" value="<?= $id ?>">
					<div class="mb-3">
						<label for="newpassword">New Password</label>
						<input type="password" class="form-control" id="newpassword" name="newpassword" placeholder="New Password" required>
					</div>
					<div class="mb-3">
						<label for="newpassword2">Repeat New Password</label>
						<input type="password" class="form-control" id="newpassword2" name="newpassword2" placeholder="Repeat New Password" required>
					</div>
				</div>
			</div>
		</form>
	</div>
	<?php include('./shared/footer.inc.php'); ?>

</body>

</html>
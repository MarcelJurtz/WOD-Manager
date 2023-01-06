<?php
session_start();

if (!isset($_SESSION['loggedin'])) {
    header('Location: ./../index.html');
    exit;
}

require_once('./../shared/db.php');
$con = getConnection();

$stmt = $con->prepare('SELECT id, designation FROM tag WHERE id = ?');
$stmt->bind_param('i', $_GET["tag"]);
$stmt->execute();
$stmt->bind_result($id, $designation);
$stmt->fetch();
$stmt->close();

?>

<!DOCTYPE html>
<html>

<?php include('./../shared/head.inc.php'); ?>

<body class="loggedin">
    <?php
        include('./../shared/menu.inc.php');
    ?>
    <div class="content">
        <div class="container card">
            <form action="./../save/tag.php" method="post">
                <input type="hidden" name="id" value="<?= $id ?>">

                <div class="row">
                    <div class="col-12">
                        <div class="d-flex justify-content-between border-bottom mb-3">
                            <h2><?= isset($designation) ? $designation : "Neuer Tag" ?></h2>
                            <div>
                                <a class="btn btn-outline-danger" href="./../index.php">Cancel</a>
                                <input class="btn btn-outline-success" type="submit" value="Speichern">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 col-md-6">
                        <div class="form-group">
                            <label for="designation">Designation</label>
                            <input type="text" class="form-control" id="designation" name="designation" placeholder="Designation" value="<?= $designation ?>" required maxlength="25">
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <?php include('./../shared/footer.inc.php'); ?>
</body>

</html>
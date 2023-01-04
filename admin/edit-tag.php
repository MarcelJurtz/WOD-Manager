<?php
session_start();

if (!isset($_SESSION['loggedin'])) {
    header('Location: index.html');
    exit;
}

require_once('db.php');
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

<head>
    <meta charset="utf-8">
    <title>Tag verwalten</title>
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
    <div class="container content">
        <div class="row">
            <div class="col-12">
                <h2><?= isset($designation) ? $designation : "Neuer Tag" ?></h2>
            </div>
        </div>
        <form action="save-tag.php" method="post">
            <input type="hidden" name="id" value="<?= $id ?>">
            <div class="row">
                <div class="col-12 col-md-6">
                    <div class="form-group">
                        <label for="designation">Bezeichnung</label>
                        <input type="text" class="form-control" id="designation" name="designation" placeholder="Bezeichnung" value="<?= $designation ?>" required maxlength="25">
                    </div>
                </div>
            </div>

            <input class="mb-3" type="submit" value="Speichern">
        </form>
    </div>
</body>
<script src="./../vendor/jquery/jquery-3.5.1.min.js"></script>
<script src="./../assets/js/bootstrap-5.0.0-beta3/bootstrap.bundle.min.js"></script>

</html>
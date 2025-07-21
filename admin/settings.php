<?php
session_start();

if (!isset($_SESSION['loggedin'])) {
    header('Location: ./../index.html');
    exit;
}

require_once('./shared/db.php');
$con = getConnection();

// Logs
$stmt = $con->prepare('SELECT id, systemname, displayname, value FROM setting ORDER BY id LIMIT 100');
$stmt->execute();
$result = $stmt->get_result();
$num_of_rows = $result->num_rows;

$settings = array();
while ($row = $result->fetch_assoc()) {
    $settings[] = $row;
}

$stmt->free_result();
$stmt->close();

?>

<!DOCTYPE html>
<html>
<?php include('./shared/head.inc.php') ?>

<body class="loggedin">
    <?php include('./shared/menu.inc.php'); ?>

    <div class="container card my-3">
        <div class="card-body">
            <div class="row">
                <div class="col-12">
                    <div class="d-flex justify-content-between border-bottom py-3 mb-3">
                        <h2>Settings</h2>
                        <div>
                            <input class="btn btn-outline-success" type="submit" value="Save" form="form-settings">
                        </div>
                    </div>
                </div>
            </div>
            <ul class="nav nav-tabs mb-4" id="tabMember" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="main-tab" data-bs-toggle="tab" href="#main" role="tab" aria-controls="main" aria-selected="true">
                        Settings
                    </a>
                </li>
            </ul>
            <div class="tab-content" id="tabMemberContent">
                <!-- Regular Logs -->
                <div class="tab-pane fade active show" id="main" role="tabpanel" aria-labelledby="main-tab">

                    <form id="form-settings" action="./save/settings.php" method="POST">

                        <?php foreach ($settings as $setting) : ?>

                            <div class="mb-3">
                                <label for="<?= $setting['systemname'] ?>" class="form-label"><?= $setting['displayname'] ?></label>
                                <input type="text" class="form-control" id="<?= $setting['systemname'] ?>" name="<?= $setting['systemname'] ?>" value="<?= $setting['value'] ?>" maxlength="500">
                            </div>

                        <?php endforeach; ?>

                    </form>

                </div>
            </div>
        </div>
    </div>
    <?php include('./shared/footer.inc.php'); ?>
</body>

</html>
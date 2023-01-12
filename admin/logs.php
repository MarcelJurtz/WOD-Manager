<?php
session_start();

if (!isset($_SESSION['loggedin'])) {
    header('Location: ./../index.html');
    exit;
}

require_once('./shared/db.php');
$con = getConnection();

// Logs
$stmt = $con->prepare('SELECT id, created, source, ip, params FROM log ORDER BY id DESC LIMIT 100');
$stmt->execute();
$result = $stmt->get_result();
$num_of_rows = $result->num_rows;

$logs = array();
while ($row = $result->fetch_assoc()) {
    $logs[] = $row;
}

$stmt->free_result();
$stmt->close();

// Login Logs

$stmt = $con->prepare('SELECT created, ip, username, success FROM login_log ORDER BY ID DESC');
$stmt->execute();
$result = $stmt->get_result();
$num_of_rows = $result->num_rows;

$loginlogs = array();
while ($row = $result->fetch_assoc()) {
    $loginlogs[] = $row;
}

$stmt->free_result();
$stmt->close();

?>

<!DOCTYPE html>
<html>
<?php include('./shared/head.inc.php') ?>

<body class="loggedin">
    <?php include('./shared/menu.inc.php'); ?>

    <div class="container card mt-3">
        <div class="card-body">
            <h2 class="card-title mb-3">Logs</h2>
            <ul class="nav nav-tabs mb-4" id="tabMember" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="main-tab" data-bs-toggle="tab" href="#main" role="tab" aria-controls="main" aria-selected="true">
                        API Logs
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="contact-tab" data-bs-toggle="tab" href="#contact" role="tab" aria-controls="contact" aria-selected="false">
                        Logins
                    </a>
                </li>
            </ul>
            <div class="tab-content" id="tabMemberContent">
                <!-- Regular Logs -->
                <div class="tab-pane fade active show" id="main" role="tabpanel" aria-labelledby="main-tab">
                    <table class="table">
                        <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Time</th>
                                <th scope="col">Src</th>
                                <th scope="col">IP</th>
                                <th scope="col">Parameter</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logs as $log) : ?>
                                <tr>
                                    <td><?= $log['id']; ?></td>
                                    <td class="text-nowrap"><?= $log['created']; ?></td>
                                    <td><?= $log['source']; ?></td>
                                    <td><?= $log['ip']; ?></td>
                                    <td class="break"><?= $log['params']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <!-- Logins -->
                <div class="tab-pane fade" id="contact" role="tabpanel" aria-labelledby="membership-tab">
                    <table class="table">
                        <thead>
                            <tr>
                                <th scope="col">Timestamp</th>
                                <th scope="col">IP</th>
                                <th scope="col">Username</th>
                                <th scope="col" class="text-center">Success</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($loginlogs as $loginlog) : ?>
                                <tr>
                                    <td><?= $loginlog['created']; ?></td>
                                    <td><?= $loginlog['ip']; ?></td>
                                    <td><?= $loginlog['username']; ?></td>
                                    <td class="text-center">
                                        <?php
                                        if ($loginlog['success'] == 1) {
                                            echo '<i class="fa-solid fa-check text-success"></i>';
                                        } else {
                                            echo '<i class="fa-solid fa-xmark text-danger"></i>';
                                        }
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <?php include('./shared/footer.inc.php'); ?>
    </div>
</body>

</html>
<?php
session_start();

if (!isset($_SESSION['loggedin'])) {
	header('Location: index.html');
	exit;
}

require_once('db.php');
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

// IPlogs

$stmt = $con->prepare('SELECT count(*) as ct, ip FROM log GROUP BY ip '); // ORDER BY cd DESC LIMIT 100
$stmt->execute();
$result = $stmt->get_result();
$num_of_rows = $result->num_rows;

$iplogs = array();
while ($row = $result->fetch_assoc()) {
     $iplogs[] = $row;
}

$stmt->free_result();
$stmt->close();

?>

<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>Home Page</title>
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
                    <div class="col-8"><h2>WODs</h2></div>
                </div>
            </div>

            <!-- START -->

            <div class="p-2 container-fluid">
                <ul class="nav nav-tabs mb-4" id="tabMember" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="main-tab" data-bs-toggle="tab" href="#main" role="tab" aria-controls="main" aria-selected="true">
                            Logs
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="contact-tab" data-bs-toggle="tab" href="#contact" role="tab" aria-controls="contact" aria-selected="false">
                            Logs (IP)
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
                                <?php  foreach($logs as $log): ?>
                                    <tr>
                                        <td><?=$log['id'];?></td>
                                        <td class="text-nowrap"><?=$log['created'];?></td>
                                        <td><?=$log['source'];?></td>
                                        <td><?=$log['ip'];?></td>
                                        <td class="break"><?=$log['params'];?></td>
                                    </tr>
                                <?php endforeach;?>
                            </tbody>
                        </table>
                    </div>
                    <!-- IP grouped Logs -->
                    <div class="tab-pane fade" id="contact" role="tabpanel" aria-labelledby="membership-tab">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th scope="col">IP</th>
                                    <th scope="col"># Zugriffe</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($iplogs as $log): ?>
                                    <tr>
                                        <td><?=$log['ip'];?></td>
                                        <td><?=$log['ct'];?></td>
                                    </tr>
                                <?php endforeach;?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- END -->
            <script src="./../assets/js/bootstrap-5.0.0-beta3/bootstrap.bundle.min.js"></script>
		</div>
	</body>
</html>
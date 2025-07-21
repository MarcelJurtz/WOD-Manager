<?php
session_start();

// If not logged in, show login page
if (!isset($_SESSION['loggedin'])) {
    ?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="./../assets/vendor/fontawesome-free-6.2.0-web/css/all.min.css">
    <link rel="stylesheet" href="./../assets/css/stylesheet.css" type="text/css">
</head>

<body>
    <div class="login">
        <h1>Login</h1>
        
        <?php if (isset($_GET['error'])): ?>
            <div class="bg-danger m-1 p-1 rounded">
                <i class="fas fa-exclamation-triangle"></i> 
                <?php 
                switch($_GET['error']) {
                    case 'db_connection':
                        echo 'Database connection unavailable. Please contact the administrator.';
                        break;
                    case 'invalid_credentials':
                        echo 'Incorrect username and/or password!';
                        break;
                    case 'missing_fields':
                        echo 'Please fill both the username and password fields!';
                        break;
                    case 'server_error':
                        echo 'Server error occurred. Please try again later or contact the administrator.';
                        break;
                    default:
                        echo 'An error occurred. Please try again.';
                }
                ?>
            </div>
        <?php endif; ?>
        
        <form action="./shared/authenticate.php" method="post">
            <label for="username">
					<i class="fas fa-user"></i>
				</label>
            <input type="text" name="username" placeholder="Username" id="username" required>
            <label for="password">
					<i class="fas fa-lock"></i>
				</label>
            <input type="password" name="password" placeholder="Password" id="password" required>
            <input type="submit" value="Login">

            <div style="max-width:450px; margin-top: 1rem; margin-bottom: 1rem">
                Made with ❤️ by <a class="text-dark" href="https://mjurtz.com"><b> Marcel Jurtz</b></a>
            </div>

        </form>
    </div>

</body>

</html>
    <?php
    exit;
}

require_once('./shared/db.php');
require_once('./shared/icons.inc.php');

$con = getConnection();

// Workouts
$stmt = $con->prepare('SELECT id, created, designation, description, permalink FROM wod ORDER BY id DESC');
$stmt->execute();
$result = $stmt->get_result();
$num_of_rows = $result->num_rows;

$wods = array();
while ($row = $result->fetch_assoc()) {
    $wods[] = $row;
}

$stmt->free_result();
$stmt->close();

// Get upcoming scheduled workouts (next 30 days)
$today = date('Ymd');
$future_date = date('Ymd', strtotime('+30 days'));
$stmt = $con->prepare('
    SELECT w.id, w.designation, w.description, ws.scheduled_date, ws.notes, g.designation as gym_name 
    FROM wod w 
    INNER JOIN wod_schedule ws ON w.id = ws.wod_id 
    INNER JOIN gym g ON ws.gym_id = g.id
    WHERE ws.scheduled_date >= ? AND ws.scheduled_date <= ? AND g.enabled = 1
    ORDER BY ws.scheduled_date ASC, g.designation ASC, w.designation ASC
');
$stmt->bind_param('ss', $today, $future_date);
$stmt->execute();
$result = $stmt->get_result();

$upcoming_wods = array();
while ($row = $result->fetch_assoc()) {
    $row['formatted_date'] = formatDateForDisplay($row['scheduled_date']);
    $upcoming_wods[] = $row;
}

$stmt->free_result();
$stmt->close();

// Equipment
$stmt = $con->prepare('SELECT id, displayname FROM equipment ORDER BY id DESC');
$stmt->execute();
$result = $stmt->get_result();
$num_of_rows = $result->num_rows;

$equipment = array();
while ($row = $result->fetch_assoc()) {
    $equipment[] = $row;
}

$stmt->free_result();
$stmt->close();

// Movements
$stmt = $con->prepare('SELECT id, displayname FROM movement ORDER BY id DESC');
$stmt->execute();
$result = $stmt->get_result();
$num_of_rows = $result->num_rows;

$movements = array();
while ($row = $result->fetch_assoc()) {
    $movements[] = $row;
}

$stmt->free_result();
$stmt->close();

// Tags
$stmt = $con->prepare('SELECT id, designation FROM tag ORDER BY id DESC');
$stmt->execute();
$result = $stmt->get_result();
$num_of_rows = $result->num_rows;

$tags = array();
while ($row = $result->fetch_assoc()) {
    $tags[] = $row;
}

$stmt->free_result();
$stmt->close();

// Gyms
$stmt = $con->prepare('SELECT id, designation, enabled FROM gym ORDER BY id DESC');
$stmt->execute();
$result = $stmt->get_result();
$num_of_rows = $result->num_rows;

$gyms = array();
while ($row = $result->fetch_assoc()) {
    $gyms[] = $row;
}

$stmt->free_result();
$stmt->close();

function printMenuBar($editUrl)
{
    print '<div class="border-bottom mb-1">
        <a class="btn btn-outline-primary mb-3" href="' . $editUrl . '" role="button">' . ICON_PLUS . ' New</a>
    </div>';
}

?>

<!DOCTYPE html>
<html>

<?php include('./shared/head.inc.php') ?>

<body class="loggedin d-flex flex-column h-100 hover-menu">
    <nav class="navtop">
        <?php
        include('./shared/menu.inc.php');
        ?>
    </nav>
    <div class="container card my-3 flex-grow-1 overflow-auto ">
        <div class="card-body">
            <h2 class="card-title mb-3">Overview</h2>
            <ul class="nav nav-tabs mb-4" id="tabMember" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="main-tab" data-bs-toggle="tab" href="#main" role="tab" aria-controls="main" aria-selected="true">
                        WODs
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="equipment-tab" data-bs-toggle="tab" href="#equipment" role="tab" aria-controls="equipment" aria-selected="false">
                        Equipment
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="movement-tab" data-bs-toggle="tab" href="#movement" role="tab" aria-controls="movement" aria-selected="false">
                        Movements
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="tags-tab" data-bs-toggle="tab" href="#tags" role="tab" aria-controls="tags" aria-selected="false">
                        Tags
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="tags-tab" data-bs-toggle="tab" href="#gyms" role="tab" aria-controls="gyms" aria-selected="false">
                        Gyms
                    </a>
                </li>
            </ul>
            <div class="tab-content" id="tabMemberContent">
                <!-- WODs -->
                <div class="tab-pane fade active show" id="main" role="tabpanel" aria-labelledby="main-tab">
                    
                    <!-- Upcoming Scheduled Workouts -->
                    <?php if (!empty($upcoming_wods)): ?>
                    <div class="mb-4">
                        <h4 class="text-primary">
                            <i class="fas fa-calendar-alt"></i> Upcoming Scheduled Workouts
                        </h4>
                        <div class="table-responsive">
                            <table class="table table-sm table-striped">
                                <thead>
                                    <tr>
                                        <th scope="col">Date</th>
                                        <th scope="col">Gym</th>
                                        <th scope="col">Workout</th>
                                        <th scope="col">Description</th>
                                        <th scope="col">Notes</th>
                                        <th scope="col">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($upcoming_wods as $upcoming): ?>
                                        <tr>
                                            <td class="text-nowrap">
                                                <strong><?= $upcoming['formatted_date'] ?></strong>
                                            </td>
                                            <td class="text-nowrap">
                                                <span class="badge bg-primary"><?= htmlspecialchars($upcoming['gym_name']) ?></span>
                                            </td>
                                            <td>
                                                <strong><?= htmlspecialchars($upcoming['designation']) ?></strong>
                                            </td>
                                            <td class="text-muted"><?= htmlspecialchars($upcoming['description']) ?></td>
                                            <td class="text-muted"><?= htmlspecialchars($upcoming['notes']) ?></td>
                                            <td>
                                                <a href="edit/wod.php?wod=<?= $upcoming['id'] ?>" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="imgen.php?id=<?= $upcoming['id'] ?>" class="btn btn-sm btn-outline-info">
                                                    <i class="fas fa-camera"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <hr class="my-4">
                    </div>
                    <?php endif; ?>
                    
                    <!-- All Workouts -->
                    <h4>All Workouts</h4>
                    <?php printMenuBar("./edit/wod.php") ?>
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Designation</th>
                                <th scope="col" class="d-none d-lg-block">Description</th>
                                <th scope="col">Permalink</th>
                                <th scope="col" class="d-none d-lg-block">Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($wods as $wod) : ?>
                                <tr data-edit='edit/wod.php?wod=<?= $wod['id'] ?>' data-preview='imgen.php?id=<?= $wod['id'] ?>'>
                                    <td><?= $wod['id']; ?></td>
                                    <td class="break"><?= $wod['designation']; ?></td>
                                    <td class="break d-none d-lg-block"><?= $wod['description']; ?></td>
                                    <td><?= $wod['permalink']; ?></td>
                                    <td class="text-nowrap d-none d-lg-block"><?= $wod['created']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <!-- Equipment -->
                <div class="tab-pane fade" id="equipment" role="tabpanel" aria-labelledby="equipment-tab">
                    <?php printMenuBar("./edit/equipment.php") ?>
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Display Name</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($equipment as $eq) : ?>
                                <tr data-edit='edit/equipment.php?eq=<?= $eq['id'] ?>'>
                                    <td><?= $eq['id']; ?></td>
                                    <td><?= $eq['displayname']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <!-- Movement -->
                <div class="tab-pane fade" id="movement" role="tabpanel" aria-labelledby="movement-tab">
                    <?php printMenuBar("./edit/movement.php") ?>
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Display Name</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($movements as $move) : ?>
                                <tr data-edit='edit/movement.php?move=<?= $move['id'] ?>'>
                                    <td><?= $move['id']; ?></td>
                                    <td><?= $move['displayname']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <!-- Tags -->
                <div class="tab-pane fade" id="tags" role="tabpanel" aria-labelledby="tags-tab">
                    <?php printMenuBar("./edit/tag.php") ?>
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Designation</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tags as $tag) : ?>
                                <tr data-edit='edit/tag.php?tag=<?= $tag['id'] ?>'>
                                    <td><?= $tag['id']; ?></td>
                                    <td><?= $tag['designation']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <!-- Gyms -->
                <div class="tab-pane fade" id="gyms" role="tabpanel" aria-labelledby="gyms-tab">
                    <?php printMenuBar("./edit/gym.php") ?>
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Enabled</th>
                                <th scope="col">Designation</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($gyms as $gym) : ?>
                                <tr data-edit='edit/gym.php?gym=<?= $gym['id'] ?>'>
                                    <td><?= $gym['id']; ?></td>
                                    <td>
                                        <?php if ($gym['enabled'] == 1): ?>
                                            <i class="fas fa-check-circle text-success" title="Enabled"></i>
                                        <?php else: ?>
                                            <i class="fas fa-times-circle text-danger" title="Disabled"></i>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= $gym['designation']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <div class="hover-menu-container d-none">
        <button id="overlay-edit" class="btn btn-link text-dark">
            <i class="fa fa-fw fa-4x fa-pencil"></i>
        </button>
        <button id="overlay-imgen" class="btn btn-link text-dark">
            <i class="fa fa-fw fa-4x fa-camera"></i>
        </button>
        <button id="overlay-hide" class="btn btn-link text-dark">
            <i class="fa fa-fw fa-4x fa-times"></i>
        </button>
    </div>
    <?php include('./shared/footer.inc.php'); ?>
    <script src="<?php echo $root . '/js/index.js' ?>"></script>
</body>

</html>

<?php
function formatDateForDisplay($dateString) {
    // Convert YYYYMMDD to DD.MM.YYYY format
    if (strlen($dateString) === 8) {
        $year = substr($dateString, 0, 4);
        $month = substr($dateString, 4, 2);
        $day = substr($dateString, 6, 2);
        return $day . '.' . $month . '.' . $year;
    }
    return $dateString;
}
?>
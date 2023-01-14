<?php
session_start();

if (!isset($_SESSION['loggedin'])) {
    header('Location: index.html');
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
            </ul>
            <div class="tab-content" id="tabMemberContent">
                <!-- WODs -->
                <div class="tab-pane fade active show" id="main" role="tabpanel" aria-labelledby="main-tab">
                    <?php printMenuBar("./edit/wod.php") ?>
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Designation</th>
                                <th scope="col">Description</th>
                                <th scope="col">Permalink</th>
                                <th class="d-none d-lg-block" scope="col">Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($wods as $wod) : ?>
                                <tr data-edit='edit/wod.php?wod=<?= $wod['id'] ?>' data-preview='imgen.php?id=<?= $wod['id'] ?>'>
                                    <td><?= $wod['id']; ?></td>
                                    <td class="break"><?= $wod['designation']; ?></td>
                                    <td class="break"><?= $wod['description']; ?></td>
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
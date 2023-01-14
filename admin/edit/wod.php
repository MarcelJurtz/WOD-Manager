<?php
session_start();

if (!isset($_SESSION['loggedin'])) {
    header('Location: ./../index.html');
    exit;
}

require_once('./../shared/db.php');
$con = getConnection();

$stmt = $con->prepare('SELECT id, designation, description, exercises, hashtags FROM wod WHERE id = ?');
$stmt->bind_param('i', $_GET["wod"]);
$stmt->execute();
$stmt->bind_result($id, $designation, $description, $exercises, $hashtags);
$stmt->fetch();
$stmt->close();

// Get tags & referenced tags for current workout
$stmt = $con->prepare('SELECT tag.id, tag.designation, CASE WHEN EXISTS (SELECT 1 FROM wod_tag WHERE wod_id = ? AND tag.id = wod_tag.tag_id) THEN 1 ELSE 0 END AS selected FROM tag ORDER BY tag.designation ');
$stmt->bind_param('i', $_GET["wod"]);
$stmt->execute();
$result = $stmt->get_result();
$num_of_rows = $result->num_rows;

$tags = array();
while ($row = $result->fetch_assoc()) {
    $tags[] = $row;
}

$stmt->free_result();
$stmt->close();

// Get equipment & referenced equipment for current workout

// SELECT equipment.id, equipment.designation, equipment.displayname,
//   CASE
//     WHEN EXISTS (SELECT 1 FROM wod_equipment WHERE wod_id = 1 AND equipment.id = wod_equipment.equipment_id) THEN 1
//     ELSE 0
//   END AS included
// FROM equipment

$stmt = $con->prepare('SELECT equipment.id, equipment.designation, equipment.displayname, CASE WHEN EXISTS (SELECT 1 FROM wod_equipment WHERE wod_id = ? AND equipment.id = wod_equipment.equipment_id) THEN 1 ELSE 0 END AS selected FROM equipment ORDER BY equipment.displayname');
$stmt->bind_param('i', $_GET["wod"]);
$stmt->execute();
$result = $stmt->get_result();
$num_of_rows = $result->num_rows;

$equipment = array();
while ($row = $result->fetch_assoc()) {
    $equipment[] = $row;
}

$stmt->free_result();
$stmt->close();

// Get movements
$stmt = $con->prepare('SELECT movement.id, movement.designation, movement.displayname, CASE WHEN EXISTS (SELECT 1 FROM wod_movement WHERE wod_id = ? AND movement.id = wod_movement.movement_id) THEN 1 ELSE 0 END AS selected FROM movement ORDER BY movement.displayname');
$stmt->bind_param('i', $_GET["wod"]);
$stmt->execute();
$result = $stmt->get_result();
$num_of_rows = $result->num_rows;

$movements = array();
while ($row = $result->fetch_assoc()) {
    $movements[] = $row;
}

$stmt->free_result();
$stmt->close();

?>

<!DOCTYPE html>
<html>

<?php include('./../shared/head.inc.php') ?>

<body class="loggedin d-flex flex-column h-100">
    <?php include('./../shared/menu.inc.php'); ?>
    <div class="container card my-3 flex-grow-1 overflow-auto">
        <form action="./../save/wod.php" method="post">
            <input type="hidden" name="id" value="<?= $id ?>">

            <div class="row">
                <div class="col-12">
                    <div class="d-flex justify-content-between border-bottom py-3 mb-3">
                        <h2><?= isset($designation) ? $designation : "New Workout" ?></h2>
                        <div>
                            <a class="btn btn-outline-primary" href="./../imgen.php?id=<?= $id ?>"><i class="fa fa-fw fa-camera"></i> Export</a>
                            <a class="btn btn-outline-danger" href="./../index.php">Cancel</a>
                            <input class="btn btn-outline-success" type="submit" value="Save">
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12 col-md-6">
                    <div class="mb-3">
                        <label for="designation">Designation</label>
                        <input type="text" class="form-control" id="designation" name="designation" placeholder="Designation (Optional)" value="<?= $designation ?>" maxlength="100">
                    </div>
                    <div class="mb-3">
                        <label for="description">Description</label>
                        <input type="text" class="form-control" id="description" name="description" placeholder="Description" value="<?= $description ?>" required maxlength="500">
                    </div>
                    <div class="mb-3">
                        <label for="exercises">Exercises</label>
                        <input type="text" class="form-control" id="exercises" name="exercises" placeholder="Exercise 1, Exercise 2, ..." value="<?= $exercises ?>" required">
                    </div>
                    <div class="mb-3">
                        <label for="hashtags" class="form-label">Hashtags</label>
                        <textarea class="form-control" id="hashtags" name="hashtags" rows="3"><?= $hashtags ?></textarea>
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <h3>Movements</h3>
                    <div class="mb-3 checklist-container">
                        <?php foreach ($movements as $move) : ?>
                            <?php
                            $checked = $move['selected'] ? "checked" : "";
                            echo "<input type='checkbox' name='movement[]' value='" . $move['id'] . "' " . $checked . "> " . $move['displayname'] . "<br>";
                            ?>
                        <?php endforeach; ?>
                    </div>
                    <h3>Equipment</h3>
                    <div class="mb-3 checklist-container">
                        <?php foreach ($equipment as $eq) : ?>
                            <?php
                            $checked = $eq['selected'] ? "checked" : "";
                            echo "<input type='checkbox' name='equipment[]' value='" . $eq['id'] . "' " . $checked . "> " . $eq['displayname'] . "<br>";
                            ?>
                        <?php endforeach; ?>
                    </div>
                    <h3>Tags</h3>
                    <div class="mb-3 checklist-container">
                        <?php foreach ($tags as $tag) : ?>
                            <?php
                            $checked = $tag['selected'] ? "checked" : "";
                            echo "<input type='checkbox' name='tags[]' value='" . $tag['id'] . "' " . $checked . "> " . $tag['designation'] . "<br>";
                            ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <?php include('./../shared/footer.inc.php'); ?>
</body>

</html>
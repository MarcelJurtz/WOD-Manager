<?php
session_start();

if (!isset($_SESSION['loggedin'])) {
    header('Location: ./../index.html');
    exit;
}

require_once('./../shared/db.php');
$con = getConnection();

$stmt = $con->prepare('SELECT id, designation, description, notes, hashtags, timecap_seconds FROM wod WHERE id = ?');
$stmt->bind_param('i', $_GET["wod"]);
$stmt->execute();
$stmt->bind_result($id, $designation, $description, $notes, $hashtags, $timecap);
$stmt->fetch();
$stmt->close();

// Get weights
$stmt = $con->prepare('SELECT id, equipment_id, display_order, weight_gender, weight_factor, weight_unit, weight, notes FROM wod_weight WHERE wod_id = ? ORDER BY display_order ASC');
$stmt->bind_param('i', $_GET["wod"]);
$stmt->execute();
$result = $stmt->get_result();
$num_of_rows = $result->num_rows;

$weights = array();
while ($weight = $result->fetch_assoc()) {
    $weights[] = $weight;
}

$stmt->free_result();
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
$query = 'SELECT equipment.id, equipment.designation, equipment.displayname, equipment.supports_weight, wod.id AS wod_id,
    CASE WHEN wod_equipment.equipment_id IS NOT NULL THEN 1 ELSE 0 END AS selected
    FROM equipment
    CROSS JOIN wod
    LEFT JOIN wod_equipment ON equipment.id = wod_equipment.equipment_id AND wod.id = wod_equipment.wod_id
    WHERE wod.id = ?
    ORDER BY wod.id, equipment.displayname';

$stmt = $con->prepare($query);
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
$query = 'SELECT movement.id, movement.designation, movement.displayname, 
    wod.id AS wod_id,
    CASE WHEN wod_movement.movement_id IS NOT NULL THEN 1 ELSE 0 END AS selected
    FROM movement
    CROSS JOIN wod
    LEFT JOIN wod_movement ON movement.id = wod_movement.movement_id AND wod.id = wod_movement.wod_id
    WHERE wod.id = ?
    ORDER BY wod.id, movement.displayname';

$stmt = $con->prepare($query);
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
                    <!-- <div class="mb-3">
                        <label for="exercises">Exercises</label>
                        <input type="text" class="form-control" id="exercises" name="exercises" placeholder="Exercise 1, Exercise 2, ..." value="<?= $exercises ?>" required">
                    </div> -->
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"><?= $notes ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="hashtags" class="form-label">Hashtags</label>
                        <textarea class="form-control" id="hashtags" name="hashtags" rows="3"><?= $hashtags ?></textarea>
                    </div>

                    <h3>Tags</h3>
                    <div class="mb-3 checklist-container">
                        <?php
                        foreach ($tags as $tag) :
                            checkListBox('tags', $tag['id'], $tag['designation'], $tag['selected'] ? "checked" : "");
                        endforeach;
                        ?>
                    </div>
                </div>
                <div class="col-12 col-md-6">



                    <div class="mb-3 checklist-container">
                        <h3>Movements</h3>
                        <div id="movements">
                            <?php
                            foreach ($movements as $move) :
                                checkListBox('movement', $move['id'], $move['displayname'], $move['selected'] ? "checked" : "");
                            endforeach;
                            ?>
                        </div>
                    </div>

                    <div class="mb-3 checklist-container">
                        <h3>Equipment</h3>
                        <div id="equipment">
                            <?php
                            foreach ($equipment as $eq) :
                                checkListBox('equipment', $eq['id'], $eq['displayname'], $eq['selected'] ? "checked" : "");
                            endforeach;
                            ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <h3>Weights</h3>
                            <button type="button" role="button" onclick="add_step()" class="btn btn-outline-secondary">+</button>
                        </div>
                        <div id="weights">
                            <?php
                            foreach ($weights as $key => $weight) {
                                echo '<div class="step-container border p-3" data-weight="' . $key . '">
                                        <div class="d-flex justify-content-between">
                                            <button type="button" role="button" onclick="remove_weight(' . $key . ')" class="me-3 btn btn-outline-danger">
                                                <i class="fas fa-fw fa-xmark"></i>
                                            </button>
                                            <select name="weight-equipment" class="form-select" aria-label="Equipment">';

                                foreach ($equipment as $eq) :
                                    if ($eq['supports_weight'] == 1) {
                                        echo '<option value="' . $eq['id'] . '" ' . ($eq['id'] == $weight['equipment_id'] ? "selected" : "") . '>' . $eq['displayname'] . '</option>';
                                    }
                                endforeach;

                                echo '
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <div class="my-3 d-flex gap-1">
                                                <div>
                                                    <label for="weight" class="form-label">Gender</label>
                                                    <select name="weight-gender" class="form-select" aria-label="Weight Gender">
                                                        <option value="1" ' . ($weight['weight_gender'] == 1 ? "selected" : "") . '>Male</option>
                                                        <option value="2" ' . ($weight['weight_gender'] == 2 ? "selected" : "") . '>Female</option>
                                                    </select>
                                                </div>
                                                <div>
                                                    <label for="weight_factor" class="form-label">Factor</label>
                                                    <input type="text" class="form-control" id="weight_factor" name="weight_factor" value="' . $weight['weight_factor'] . '">
                                                </div>
                                                <div>
                                                    <label for="weight" class="form-label">Weight</label>
                                                    <input type="number" step="0.01" class="form-control" id="weight" name="weight" value="' . $weight['weight'] . '">
                                                </div>
                                                <div>
                                                    <label for="weight_unit" class="form-label">Unit</label>
                                                    <select name="weight_unit" class="form-select" aria-label="Weight Unit">
                                                        <option ' . ($weight['weight_unit'] == '' ? "selected" : "") . '>Nothing selected</option>
                                                        <option value="1" ' . ($weight['weight_unit'] == 1 ? "selected" : "") . '>Kilograms</option>
                                                        <option value="3" ' . ($weight['weight_unit'] == 2 ? "selected" : "") . '>% Bodyweight</option>
                                                        <option value="4" ' . ($weight['weight_unit'] == 3 ? "selected" : "") . '>Pood</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>';
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <?php include('./../shared/footer.inc.php'); ?>
    <script>
        let movements = <?php print_r(buildJsEntities($movements)); ?>;
        let equipment = <?php print_r(buildJsEntities($equipment)); ?>;
    </script>

    <script src="./../../assets/js/wod.js"></script>
</body>

</html>

<?php
function checkListBox($name, $value, $label, $checked)
{
    echo '<div class="checklist-item d-block">
                <input type="checkbox" name="' . $name . '[]" value="' . $value . '"' . $checked . '>
                <span class="ms-2 label">' . $label . '</span>
            </div>';
}

function buildJsEntities($data)
{
    $projectedData = array_map('cleanEntity', $data);

    $duplicate_keys = array();
    $tmp = array();

    foreach ($projectedData as $key => $val) {
        // convert objects to arrays, in_array() does not support objects
        if (is_object($val))
            $val = (array)$val;

        if (!in_array($val, $tmp))
            $tmp[] = $val;
        else
            $duplicate_keys[] = $key;
    }

    foreach ($duplicate_keys as $key)
        unset($projectedData[$key]);

    return json_encode($projectedData);
}

function cleanEntity($entity)
{
    return [
        'id' => $entity["id"],
        'designation' => $entity["designation"],
        'displayname' => $entity["displayname"]
    ];
}
?>
<?php
session_start();

if (!isset($_SESSION['loggedin'])) {
    header('Location: ./../index.html');
    exit;
}

require_once('./../shared/db.php');
$con = getConnection();

$stmt = $con->prepare('SELECT id, designation, description, notes, exercises, hashtags FROM wod WHERE id = ?');
$stmt->bind_param('i', $_GET["wod"]);
$stmt->execute();
$stmt->bind_result($id, $designation, $description, $notes, $exercises, $hashtags);
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

// Get available gyms
$stmt = $con->prepare('SELECT id, designation FROM gym WHERE enabled = 1 ORDER BY designation ASC');
$stmt->execute();
$result = $stmt->get_result();

$gyms = array();
while ($row = $result->fetch_assoc()) {
    $gyms[] = $row;
}

$stmt->free_result();
$stmt->close();

// Get scheduled dates for current workout (with gym information)
$stmt = $con->prepare('
    SELECT ws.id, ws.scheduled_date, ws.notes, ws.gym_id, g.designation as gym_name 
    FROM wod_schedule ws 
    INNER JOIN gym g ON ws.gym_id = g.id 
    WHERE ws.wod_id = ? 
    ORDER BY ws.scheduled_date ASC, g.designation ASC
');
$stmt->bind_param('i', $_GET["wod"]);
$stmt->execute();
$result = $stmt->get_result();

$scheduled_dates = array();
while ($row = $result->fetch_assoc()) {
    $row['formatted_date'] = formatDateForDisplay($row['scheduled_date']);
    $scheduled_dates[] = $row;
}

$stmt->free_result();
$stmt->close();

?>

<!DOCTYPE html>
<html>

<?php include('./../shared/head.inc.php') ?>

<body class="loggedin d-flex flex-column h-100">
    <?php include('./../shared/menu.inc.php'); ?>
    <div class="container">
        <div class="row card my-3 flex-grow-1 overflow-auto">
            <form action="./../save/wod.php" method="post">
                <input type="hidden" name="id" value="<?= $id ?>">
                <div class="row">
                    <div class="col-12">
                        <div class="d-flex justify-content-between border-bottom py-3 mb-3">
                            <h2>
                                <?= isset($designation) ? $designation : "New Workout" ?>
                            </h2>
                            <div>
                                <a class="btn btn-outline-primary" href="./../imgen.php?id=<?= $id ?>"><i
                                        class="fa fa-fw fa-camera"></i> Export</a>
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
                            <input type="text" class="form-control" id="designation" name="designation"
                                placeholder="Designation (Optional)" value="<?= $designation ?>" maxlength="100">
                        </div>
                        <div class="mb-3">
                            <label for="description">Description</label>
                            <input type="text" class="form-control" id="description" name="description"
                                placeholder="Description" value="<?= $description ?>" required maxlength="500">
                        </div>
                        <div class="mb-3">
                            <label for="exercises">Exercises</label>
                            <input type="text" class="form-control" id="exercises" name="exercises"
                                placeholder="Exercise 1, Exercise 2, ..." value="<?= $exercises ?>" required">
                        </div>
                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3"><?= $notes ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="hashtags" class="form-label">Hashtags</label>
                            <textarea class="form-control" id="hashtags" name="hashtags"
                                rows="3"><?= $hashtags ?></textarea>
                        </div>
                        
                        <!-- Date Scheduling Section -->
                        <div class="mb-3">
                            <label class="form-label">Scheduled Dates</label>
                            
                            <?php if (empty($gyms)): ?>
                                <div class="alert alert-warning" role="alert">
                                    <i class="fas fa-exclamation-triangle"></i> 
                                    <strong>No gyms available!</strong> Please add at least one gym before scheduling workouts.
                                </div>
                            <?php else: ?>
                                <div id="scheduled-dates-container">
                                    <?php if (!empty($scheduled_dates)): ?>
                                        <?php foreach ($scheduled_dates as $index => $scheduled): ?>
                                            <div class="d-flex mb-2 scheduled-date-row">
                                                <input type="hidden" name="scheduled_ids[]" value="<?= $scheduled['id'] ?>">
                                                <input type="date" class="form-control me-2" name="scheduled_dates[]" 
                                                       value="<?= date('Y-m-d', strtotime($scheduled['formatted_date'])) ?>" 
                                                       style="max-width: 160px;">
                                                <select class="form-control me-2" name="scheduled_gyms[]" style="max-width: 120px;">
                                                    <?php foreach ($gyms as $gym): ?>
                                                        <option value="<?= $gym['id'] ?>" <?= ($gym['id'] == $scheduled['gym_id']) ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars($gym['designation']) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <input type="text" class="form-control me-2" name="scheduled_notes[]" 
                                                       placeholder="Optional notes" value="<?= htmlspecialchars($scheduled['notes']) ?>">
                                                <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeScheduledDate(this)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                                <button type="button" class="btn btn-outline-primary btn-sm mt-2" onclick="addScheduledDate()">
                                    <i class="fas fa-plus"></i> Add Date
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <h3>Movements</h3>
                        <div class="mb-3 checklist-container">
                            <?php
                            foreach ($movements as $move):
                                checkListBox('movement', $move['id'], $move['displayname'], $move['selected'] ? "checked" : "");
                            endforeach;
                            ?>
                        </div>
                        <h3>Equipment</h3>
                        <div class="mb-3 checklist-container">
                            <?php
                            foreach ($equipment as $eq):
                                checkListBox('equipment', $eq['id'], $eq['displayname'], $eq['selected'] ? "checked" : "");
                            endforeach;
                            ?>
                        </div>
                        <h3>Tags</h3>
                        <div class="mb-3 checklist-container">
                            <?php
                            foreach ($tags as $tag):
                                checkListBox('tags', $tag['id'], $tag['designation'], $tag['selected'] ? "checked" : "");
                            endforeach;
                            ?>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <?php include('./../shared/footer.inc.php'); ?>
    <script src="./../../assets/js/wod.js"></script>
    <script>
        // Generate gym options from PHP data
        const gymOptions = [
            <?php if (!empty($gyms)): ?>
                <?php foreach ($gyms as $gym): ?>
                    {id: <?= $gym['id'] ?>, name: "<?= htmlspecialchars($gym['designation']) ?>"},
                <?php endforeach; ?>
            <?php endif; ?>
        ];

        console.log('Available gyms:', gymOptions); // Debug log

        function addScheduledDate() {
            const container = document.getElementById('scheduled-dates-container');
            const newRow = document.createElement('div');
            newRow.className = 'd-flex mb-2 scheduled-date-row';
            
            // Check if gyms are available
            if (gymOptions.length === 0) {
                alert('No gyms available. Please add and enable at least one gym first.');
                return;
            }
            
            // Build gym options HTML
            let gymOptionsHtml = '';
            gymOptions.forEach(gym => {
                gymOptionsHtml += `<option value="${gym.id}">${gym.name}</option>`;
            });
            
            newRow.innerHTML = `
                <input type="hidden" name="scheduled_ids[]" value="0">
                <input type="date" class="form-control me-2" name="scheduled_dates[]" style="max-width: 160px;" required>
                <select class="form-control me-2" name="scheduled_gyms[]" style="max-width: 120px;" required>
                    ${gymOptionsHtml}
                </select>
                <input type="text" class="form-control me-2" name="scheduled_notes[]" placeholder="Optional notes">
                <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeScheduledDate(this)">
                    <i class="fas fa-trash"></i>
                </button>
            `;
            container.appendChild(newRow);
        }

        function removeScheduledDate(button) {
            button.closest('.scheduled-date-row').remove();
        }

        // Sort dates when they change
        document.addEventListener('change', function(e) {
            if (e.target.type === 'date' && e.target.name === 'scheduled_dates[]') {
                sortScheduledDates();
            }
        });

        function sortScheduledDates() {
            const container = document.getElementById('scheduled-dates-container');
            const rows = Array.from(container.querySelectorAll('.scheduled-date-row'));
            
            rows.sort((a, b) => {
                const dateA = a.querySelector('input[type="date"]').value;
                const dateB = b.querySelector('input[type="date"]').value;
                // If dates are the same, sort by gym
                if (dateA === dateB) {
                    const gymA = a.querySelector('select').selectedOptions[0].text;
                    const gymB = b.querySelector('select').selectedOptions[0].text;
                    return gymA.localeCompare(gymB);
                }
                return dateA.localeCompare(dateB);
            });

            rows.forEach(row => container.appendChild(row));
        }
    </script>
</body>

</html>

<?php
function checkListBox($name, $value, $label, $checked)
{
    echo '<div class="checklist-item">
        <input type="checkbox" name="' . $name . '[]" value="' . $value . '"' . $checked . '>
        <span class="ms-2 label">' . $label . '</span>
    </div>';
}

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

function formatDateForStorage($dateString) {
    // Convert DD.MM.YYYY or YYYY-MM-DD to YYYYMMDD format
    if (strpos($dateString, '.') !== false) {
        // DD.MM.YYYY format
        $parts = explode('.', $dateString);
        if (count($parts) === 3) {
            return $parts[2] . str_pad($parts[1], 2, '0', STR_PAD_LEFT) . str_pad($parts[0], 2, '0', STR_PAD_LEFT);
        }
    } elseif (strpos($dateString, '-') !== false) {
        // YYYY-MM-DD format
        return str_replace('-', '', $dateString);
    }
    return $dateString;
}
?>
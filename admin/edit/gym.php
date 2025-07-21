<?php
session_start();

if (!isset($_SESSION['loggedin'])) {
    header('Location: ./../index.html');
    exit;
}

require_once('./../shared/db.php');
$con = getConnection();

$stmt = $con->prepare('SELECT id, designation, tag, enabled, primary_color, secondary_color FROM gym WHERE id = ?');
$stmt->bind_param('i', $_GET["gym"]);
$stmt->execute();
$stmt->bind_result($id, $designation, $tag, $enabled, $primary_color, $secondary_color);
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
    <div class="container card my-3">
        <form action="./../save/gym.php" method="post">
            <input type="hidden" name="id" value="<?= $id ?>">

            <div class="row">
                <div class="col-12">
                    <div class="d-flex justify-content-between border-bottom py-3  mb-3">
                        <h2><?= isset($designation) ? $designation : "New Gym" ?></h2>
                        <div>
                            <a class="btn btn-outline-danger" href="./../index.php">Cancel</a>
                            <input class="btn btn-outline-success" type="submit" value="Save">
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12 col-md-6">
                    <div class="mb-3">
                        <label for="designation" class="form-label">Designation</label>
                        <input type="text" class="form-control" id="designation" name="designation" placeholder="Designation" value="<?= $designation ?>" required maxlength="25">
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="mb-3">
                        <label for="tag" class="form-label">Tag</label>
                        <input type="text" class="form-control" id="tag" name="tag" placeholder="Tag" value="<?= $tag ?>" required maxlength="25">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12 col-md-6">
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="enabled" name="enabled" value="1" <?= ($enabled == 1) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="enabled">
                                Enabled
                            </label>
                        </div>
                        <small class="form-text text-muted">Only enabled gyms will appear in workout scheduling</small>
                    </div>
                </div>
            </div>
            
            <!-- Color Customization Section -->
            <div class="row">
                <div class="col-12">
                    <h4 class="mb-3">Color Scheme</h4>
                </div>
            </div>
            <div class="row">
                <div class="col-12 col-md-6">
                    <div class="mb-3">
                        <label for="primary_color" class="form-label">Primary Color</label>
                        <div class="input-group">
                            <input type="color" class="form-control form-control-color" id="primary_color" name="primary_color" 
                                   value="<?= $primary_color ?: '#667eea' ?>" title="Choose primary color">
                            <input type="text" class="form-control" id="primary_color_text" 
                                   value="<?= $primary_color ?: '#667eea' ?>" maxlength="7" pattern="^#[0-9A-Fa-f]{6}$">
                        </div>
                        <small class="form-text text-muted">Used for backgrounds and main elements</small>
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="mb-3">
                        <label for="secondary_color" class="form-label">Secondary Color</label>
                        <div class="input-group">
                            <input type="color" class="form-control form-control-color" id="secondary_color" name="secondary_color" 
                                   value="<?= $secondary_color ?: '#764ba2' ?>" title="Choose secondary color">
                            <input type="text" class="form-control" id="secondary_color_text" 
                                   value="<?= $secondary_color ?: '#764ba2' ?>" maxlength="7" pattern="^#[0-9A-Fa-f]{6}$">
                        </div>
                        <small class="form-text text-muted">Used for gradients and accents</small>
                    </div>
                </div>
            </div>
            
            <!-- Color Preview -->
            <div class="row">
                <div class="col-12">
                    <div class="mb-3">
                        <label class="form-label">Preview</label>
                        <div id="color-preview" class="d-flex align-items-center justify-content-center text-white rounded" 
                             style="height: 100px; background: linear-gradient(135deg, <?= $primary_color ?: '#667eea' ?> 0%, <?= $secondary_color ?: '#764ba2' ?> 100%);">
                            <div class="text-center">
                                <i class="fas fa-dumbbell fa-2x mb-2"></i>
                                <div class="fw-bold"><?= htmlspecialchars($designation ?: 'Gym Name') ?></div>
                                <div>Workout Preview</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
        </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const primaryColorPicker = document.getElementById('primary_color');
    const primaryColorText = document.getElementById('primary_color_text');
    const secondaryColorPicker = document.getElementById('secondary_color');
    const secondaryColorText = document.getElementById('secondary_color_text');
    const designationInput = document.getElementById('designation');
    const colorPreview = document.getElementById('color-preview');
    
    // Sync color picker with text input
    primaryColorPicker.addEventListener('input', function() {
        primaryColorText.value = this.value;
        updatePreview();
    });
    
    primaryColorText.addEventListener('input', function() {
        if (this.value.match(/^#[0-9A-Fa-f]{6}$/)) {
            primaryColorPicker.value = this.value;
            updatePreview();
        }
    });
    
    secondaryColorPicker.addEventListener('input', function() {
        secondaryColorText.value = this.value;
        updatePreview();
    });
    
    secondaryColorText.addEventListener('input', function() {
        if (this.value.match(/^#[0-9A-Fa-f]{6}$/)) {
            secondaryColorPicker.value = this.value;
            updatePreview();
        }
    });
    
    // Update gym name in preview
    designationInput.addEventListener('input', function() {
        updatePreview();
    });
    
    function updatePreview() {
        const primaryColor = primaryColorPicker.value;
        const secondaryColor = secondaryColorPicker.value;
        const gymName = designationInput.value || 'Gym Name';
        
        colorPreview.style.background = 'linear-gradient(135deg, ' + primaryColor + ' 0%, ' + secondaryColor + ' 100%)';
        colorPreview.querySelector('.fw-bold').textContent = gymName;
    }
});
</script>

<?php include("../shared/footer.inc.php"); ?>
</body>

</html>
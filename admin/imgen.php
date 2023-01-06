<!DOCTYPE html>
<html>

<?php

session_start();

if (!isset($_SESSION['loggedin'])) {
  header('Location: ./../index.html');
  exit;
}

require_once('./shared/db.php');
require_once('./shared/icons.inc.php');

// Get random wod from DB
$con = getConnection();

$stmt = $con->prepare('SELECT id, designation, description, exercises, permalink FROM wod ORDER BY RAND() LIMIT 1');
$stmt->execute();
$stmt->bind_result($id, $designation, $description, $exercises, $permalink);
$stmt->fetch();
$stmt->close();

//$hashtags = getHashtagString($wod["keywords"]) . ' ' . getRandomDefaultHashtags();
$prefix = "";
// TODO Move to config, add hashtags back to logic and base off of exercises
$suffix = "Follow @Wodai.ly on Instagram for more workouts!\n\n";
$src = "Background-images are from unsplash.com";

$params = '?wod=' . $permalink;
$params .= '&designation=' . $designation;
$params .= '&description=' . $description;
$params .= '&exercises=' . $exercises;
$img_url = 'image.php' . $params;
?>

<?php include('./shared/head.inc.php') ?>

<body class="loggedin">
  <?php
    include('./shared/menu.inc.php');
  ?>
  <div class="content">
    <div class="p-2 container-fluid card">
      <div class="card-body">

        <div class="row">
          <div class="col-12">
            <div class="d-flex justify-content-between border-bottom mb-3">
              <h2 class="card-title mb-3">Image-Generator</h2>
              <div>
                <a href="JavaScript:Void(0);" title="Copy Caption" id="copy" class="btn btn-outline-secondary text-decoration-none">
                  <span id="btn-copy-init">Copy Text <?php echo ICON_CLIPBOARD ?></span>
                  <span id="btn-copy-copied" class="d-none">Copied! <?php echo ICON_CHECK ?></span>
                </a>
                <button class="btn btn-outline-primary" id="get-random">Other WOD</button>
                <button class="btn btn-outline-secondary" id="replace-bg">Replace BG</button>
              </div>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="d-flex">
            <img id="preview" src="<?php echo $img_url; ?>" class="card-img-top bg-white rounded-0" style=" background:url(/workouts/assets/img/preview.gif) center/50% no-repeat; " width="450" height="450" alt="image with workout instructions">
            <textarea id="details" class="form-control" rows="8" data-permalink="<?php echo $permalink ?>">
              <?php echo $prefix . $description . ":\n\n" . $exercises . "\n\n" . $suffix . "\n\n" . $src ?>
            </textarea>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- END -->
  <?php include('./shared/footer.inc.php'); ?>
  <script src="/workouts/assets/js/imgen.js"></script>

</body>

</html>
</body>

</html>
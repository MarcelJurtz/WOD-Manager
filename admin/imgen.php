<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <title>Home Page</title>
  <link href="admin.css" rel="stylesheet" type="text/css">
  <link rel="stylesheet" href="./../vendor/fontawesome-free-5.15.4-web/css/all.min.css">
  <link rel="stylesheet" href="./../assets/css/bootstrap-5.0.0-beta3/bootstrap.min.css">
</head>

<?php

session_start();

if (!isset($_SESSION['loggedin'])) {
  header('Location: index.html');
  exit;
}

require_once('db.php');
require_once('icons.php');
include("./tools.inc.php");

// Get random wod from DB
$con = getConnection();

$stmt = $con->prepare('SELECT id, designation, description, exercises, permalink FROM wod ORDER BY RAND() LIMIT 1');
$stmt->execute();
$stmt->bind_result($id, $designation, $description, $exercises, $permalink);
$stmt->fetch();
$stmt->close();

// TODO Errorhandling
// if (isset($id)) {
// }


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

<body class="loggedin">
  <nav class="navtop">
    <?php
    include('./menu.php');
    ?>
  </nav>
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
            <img id="preview" src="<?php echo $img_url; ?>" class="card-img-top bg-white rounded-0" style=" background:url(assets/preview.gif) center/50% no-repeat; " width="450" height="450" alt="image with workout instructions">
            <textarea id="details" class="form-control" rows="8" data-permalink="<?php echo $permalink ?>">
              <?php echo $prefix . $description . ":\n\n" . $exercises . "\n\n" . $suffix . "\n\n" . $src ?>
            </textarea>
          </div>
        </div>
      </div>
    </div>
  </div>
  <?php
    include('./footer.php');
  ?>

  <!-- END -->
  <script src="./../assets/js/bootstrap-5.0.0-beta3/bootstrap.bundle.min.js"></script>
  <script src="./script.js"></script>

</body>

</html>
</body>

</html>
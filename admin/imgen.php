<?php

session_start();

if (!isset($_SESSION['loggedin'])) {
  header('Location: ./../index.html');
  exit;
}

require_once('./shared/db.php');
require_once('./shared/icons.inc.php');

$con = getConnection();

if (isset($_GET['id'])) {
  $stmt = $con->prepare('SELECT id, designation, description, exercises, permalink FROM wod WHERE id = ?');
  $stmt->bind_param('i', $_GET['id']);
} else {
  $query = 'SELECT id, designation, description, exercises, permalink FROM wod ORDER BY RAND() LIMIT 1';
  $stmt = $con->prepare($query);
}

$stmt->execute();
$stmt->bind_result($id, $designation, $description, $exercises, $permalink);
$stmt->fetch();
$stmt->close();

// Get hashtags for wod, tags and equipment with respecting configuration
$stmt = $con->prepare("SELECT GROUP_CONCAT(tags SEPARATOR ',') as tags
FROM (
    SELECT IFNULL(CASE WHEN ? THEN wod.hashtags ELSE NULL END, '') as tags FROM wod WHERE wod.id = ?
    UNION
    SELECT IFNULL(CASE WHEN ? THEN tag.hashtags ELSE NULL END, '') as tags FROM wod
    LEFT JOIN wod_tag ON wod.id = wod_tag.wod_id
    LEFT JOIN tag ON wod_tag.tag_id = tag.id
    WHERE wod.id = ?
    UNION
    SELECT IFNULL(CASE WHEN ? THEN equipment.hashtags ELSE NULL END, '') as tags FROM wod
    LEFT JOIN wod_equipment ON wod.id = wod_equipment.wod_id
    LEFT JOIN equipment ON wod_equipment.equipment_id = equipment.id
    WHERE wod.id = ?
    UNION
    SELECT IFNULL(CASE WHEN ? THEN movement.hashtags ELSE NULL END, '') as tags FROM wod
    LEFT JOIN wod_movement ON wod.id = wod_movement.wod_id
    LEFT JOIN movement ON wod_movement.movement_id = movement.id
    WHERE wod.id = ?
) as temp");

$useWods = HASHTAGS_USE_WODS;
$useTags = HASHTAGS_USE_TAGS;
$useEquipment = HASHTAGS_USE_EQUIPMENT;
$useMovements = HASHTAGS_USE_MOVEMENTS;

$stmt->bind_param('iiiiiiii', $useWods, $id, $useTags, $id, $useEquipment, $id, $useMovements, $id);
$stmt->execute();
$stmt->bind_result($hashtags);
$stmt->fetch();
$stmt->close();

// Filter hashtags
if (HASHTAGS_USE_DEFAULTS) {
  $hashtags .= ', ' . DEFAULT_HASHTAGS;
}

$uniqueHashtags = array_unique(explode(',', $hashtags));
array_walk($uniqueHashtags, 'hashtag');
shuffle($uniqueHashtags);
$uniqueHashtags = array_slice($uniqueHashtags, 0, HASHTAG_TOTAL_COUNT);

//$hashtags = getHashtagString($wod["keywords"]) . ' ' . getRandomDefaultHashtags();
$prefix = "";
$keyword =  $_GET["keyword"] ?: "crossfit";
// TODO Move to config, add hashtags back to logic and base off of exercises
$suffix = "Follow @Wodai.ly on Instagram for more workouts!";
$src = "Background-images are from unsplash.com";

$params = '?wod=' . $permalink;
$params .= '&designation=' . $designation;
$params .= '&description=' . $description;
$params .= '&exercises=' . $exercises;
$params .= '&keyword=' . $keyword;
$img_url = 'image.php' . $params;


function hashtag(&$value)
{
  $value = '#' . trim($value);
}

function linebreak($text)
{
  $text = str_replace(', ', ',', $text); // trim
  return str_replace(',', ',&#013;&#010;', $text);
}

?>

<!DOCTYPE html>
<html>

<?php include('./shared/head.inc.php') ?>

<body class="loggedin">
  <?php
  include('./shared/menu.inc.php');
  ?>
  <div class="container card my-3">
    <div class="card-body">
      <div class="row">
        <div class="col-12">
          <div class="d-md-flex justify-content-between border-bottom mb-3 pb-3">
            <h2 class="card-title">Image-Generator</h2>
            <div>
              <button class="btn btn-outline-primary" id="get-random">Other WOD</button>
            </div>
          </div>
        </div>
      </div>

      <!-- Menu -->
      <div class="row">
        <div class="card mb-3">
          <div class="card-body">
            <a href="JavaScript:Void(0);" title="Copy Caption" id="copy" class="btn btn-outline-secondary text-decoration-none">
              <span id="btn-copy-init">Copy Text <?php echo ICON_CLIPBOARD ?></span>
              <span id="btn-copy-copied" class="d-none">Copied! <?php echo ICON_CHECK ?></span>
            </a>

            <div class="input-group mb-3">
              <input id="txt-keyword" type="text" class="form-control" placeholder="Search Keyword" aria-label="Search Keyword" aria-describedby="replace-bg" value="<?php echo $keyword ?>">
              <button class="btn btn-outline-secondary" type="button" id="replace-bg">Replace BG</button>
            </div>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="d-flex">
          <img id="preview" src="<?php echo $img_url; ?>" class="bg-white rounded-0" style=" background:url(/workouts/assets/img/preview.gif) center/50% no-repeat; " width="450" height="450" alt="image with workout instructions">
          <textarea id="details" class="form-control" rows="8" data-permalink="<?php echo $permalink ?>">
              <?php echo $prefix . $description . ":\n\n" . linebreak($exercises) . "\n\n" . $suffix . "\n\n" . implode(' ', $uniqueHashtags) . "\n\n" . $src ?>
            </textarea>
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
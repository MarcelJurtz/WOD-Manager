<!doctype html>
<html lang="en">
<head>
  <title>Wodaily Image Generator</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="Image generator to accompany wodai.ly functional fitness workout generator" />

  <link href="./assets/vendor/fontawesome-free-6.2.0-web/css/fontawesome.min.css" rel="stylesheet">
  <link href="./assets/vendor/fontawesome-free-6.2.0-web/css/solid.css" rel="stylesheet">
  <link href="./assets/vendor/bootstrap-5.2.1-dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

  <?php
    $string = file_get_contents("wods.json");
    if ($string === false) {
      // TODO
    }

    $data = json_decode($string, true);
    if ($data === null) {
      // TODO
    }

    $wod_index = rand(0, count($data));
    // $permalink  = $data[$wod_index]['permalink'];
    // $wod = strtoupper($data[$wod_index]['name']);
    $wod = $data[$wod_index];

    $permalink = $wod["permalink"];
    $excercises = implode("\n",$wod["excercises"]);
    $instructions = $wod["description"];

    $hashtags = "#test #wasd #abcdefu";
    $prefix = "";
    $suffix = "Follow @Wodai.ly on Instagram for more workouts!\n\n";

    $params = '?wod=' . $permalink;
    $img_url = 'image.php' . $params;
  ?>

  <div class="card mx-auto my-1 border-0 shadow-lg" style="width: 450px; background:#000;">
    <img src="<?php echo $img_url; ?>" class="card-img-top bg-white rounded-0" style=" background:url(assets/preview.gif) center/50% no-repeat; " width="450" height="450" alt="image with workout instructions">
    <div class="card-body text-white">
      <div class="container w-100"></div>
      <label> 
          <a href="JavaScript:Void(0);" 
            title="Copy Caption" 
            id="copy" 
            style="background-color: rgba(255, 255, 255, 0.3) !important; color:white !important;" 
            class="badge badge-light text-decoration-none">Copy <i class="fa-regular fa-copy"></i>
          </a>
        </label>
      <div class="form-group my-3">
        <textarea id="details" class="form-control" rows="12">
          <?php echo $prefix . $instructions . ":\n\n" . $excercises . "\n\n" . $suffix . $hashtags ?>
        </textarea>
      </div>
      <a href="image.php<?php echo $params; ?>" class="btn btn-primary">Download <i class="fa-regular fa-download"></i></a> 
      <button class="btn btn-outline-light" onClick="window.location.reload();">Another, please <i class="fa-regular fa-rotate-right"></i></button>
    </div>
  </div>
  <div class="container my-2 small text-center" style="max-width:450px;">
    Made with <i class="text-danger fa-solid fa-heart"></i> by <a class="text-dark" href="https://github.com/MarcelJurtz"><b> Marcel Jurtz</b></a> | Photos from <a class="text-dark" href="https://source.unsplash.com/"><b> unsplash.com </b></a>
  </div>

  <script src="./assets/vendor/bootstrap-5.2.1-dist/js/bootstrap.bundle.min.js"></script> 
  <script src="./script.js"></script> 
</body>
</html>
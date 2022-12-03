<?php

include("./tools.inc.php");

$img_src = "https://source.unsplash.com/random/1080x1080/?crossfit";
$font = "permanent-marker-v16-latin-regular";
$filename = "wods.json";

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Load data from json
$content = file_get_contents($filename);

if ($content === false) {
    echo 'Error reading data';
    return;
}

$data = json_decode($content, true);
if ($data === null) {
    echo 'Error parsing data';
    return;
}

$wod = findObjectById($data, $_GET["wod"]);
if ($wod === false) {
    echo 'Error searching wod in data';
    return;
}

// Setup GD
putenv('GDFONTPATH=' . realpath('.') . '/assets/fonts');

// Configure image content
$description =  wordwrap($wod["description"], 38, "\n");
$title = $wod["name"];
$excercises = implode("\n", $wod["excercises"]);
$user = "@Wodai.ly";

// $img = imagecreatefromjpeg($img_src);
// $img = greyscale($img);

// Fallback: Single color instead of unsplash
$img = imagecreate(1080, 1080) or die("Can't create image!");
// We Allocate a color to be used as the canvas background
$colorIndigo = imagecolorallocate($img, 0x3F, 0x51, 0xB5);
// We Apply color as canvas background
imagefill($img, 0, 0, $colorIndigo);

$white = imagecolorallocate($img, 255, 255, 255);

$myCanvasWidth  = imagesx($img);
$myCanvasHeight = imagesy($img);

$fontSize = 16;

// Configs for variable font sizes
tryWrite($img, 150, 0.1, 0.3, array(48, 42, 36, 32, 28, 24, 20, 16, 12, 8), $font, $description, $white);
tryWrite($img, 300, 0.1, 0.3, array(42, 36, 28, 24, 20, 16, 12, 8), $font, $excercises, $white);
tryWrite($img, 930, 0.1, 0.3, array(20), $font, $user, $white);

header('Content-Type: image/png');
header('Content-Disposition: Attachment;filename="Wodaily - ' . $title . '.png"');

imagepng($img);
imagedestroy($img);


function tryWrite($img, $offset, $padX, $padY, $fontsizes, $font, $text, $color)
{
    $angle = 0;
    $maxAttempts = sizeof($fontsizes);

    $width = imagesx($img);
    $height = imagesy($img);

    for ($i = 0; $i < $maxAttempts; $i++) {
        $fontsize = $fontsizes[$i];

        // Try to fit with current fontsize
        $textBounds = imageftbbox($fontsize, $angle, $font, $text);

        // Get bounds that text would require to verify fitting
        $lower_left_x  = $textBounds[0];
        $lower_left_y  = $textBounds[1];
        $lower_right_x = $textBounds[2];
        $lower_right_y = $textBounds[3];
        $upper_right_x = $textBounds[4];
        $upper_right_y = $textBounds[5];
        $upper_left_x  = $textBounds[6];
        $upper_left_y  = $textBounds[7];

        // Get Text Width and Height
        $textWidth  =  $lower_right_x - $lower_left_x;
        $textHeight = $lower_right_y - $upper_right_y;

        if ($textWidth <= $width - $padX * 2 && $textHeight <= $height - $padY * 2) {

            // Center text horizontally and vertically (+ vertical offset)
            $start_x_offset = ($width - $textWidth) / 2;
            $start_y_offset = $offset;
            // $start_y_offset = (($height - $textHeight) + $fontsize * 2) / 2 + $offset;

            imagettftext($img, $fontsize, $angle, (int)$start_x_offset, (int)$start_y_offset, $color, $font, $text);

            return;
        }
    }

    // Add error when trying to add too much text
    $error = "Error: Text too Long!";
    $fontsize = $width / $fontsizes[0];

    $textBounds = imageftbbox($fontsize, $angle, $font, $error);

    $lower_left_x  = $textBounds[0];
    $lower_left_y  = $textBounds[1];
    $lower_right_x = $textBounds[2];
    $lower_right_y = $textBounds[3];
    $upper_right_x = $textBounds[4];
    $upper_right_y = $textBounds[5];
    $upper_left_x  = $textBounds[6];
    $upper_left_y  = $textBounds[7];

    $textWidth  =  $lower_right_x - $lower_left_x;
    $textHeight = $lower_right_y - $upper_right_y;

    $start_x_offset = ($width - $textWidth) / 2;
    $start_y_offset = (($height - $textHeight) + $fontsize * 2) / 2 + $offset;

    imagettftext($img, $fontsize, $angle, (int)$start_x_offset, (int)$start_y_offset, $color, $font, $error);
}

function greyscale(&$img)
{

    $src = $img;
    $dst = $img;

    imagecopymergegray($dst, $src, 0, 0, 0, 0, 1080, 1080, 0);

    return $dst;
}
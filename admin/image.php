<?php

include("./shared/tools.inc.php");

// Enable file_get_contents 
ini_set("allow_url_fopen", 1);

$keyword = $_GET['keyword'];
$source = $_GET['source'];

// Get resizedd image by adjusting parameters
$imageUrl = explode('?', $source)[0] . "?h=1080&w=1080&fit=crop";

$img = imagecreatefromstring(file_get_contents($imageUrl));

$fontx = "permanent-marker-v16-latin-regular.ttf";
$font_path = $_SERVER['DOCUMENT_ROOT'] . '/' . ROOT_FOLDER . '/assets/fonts/' . $fontx;

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

// Be careful when using echo statements for debugging!
// They break the image generation

// Setup GD
// putenv('GDFONTPATH=' . $_SERVER['DOCUMENT_ROOT'] . '/workouts/assets/fonts');

// echo $_SERVER['DOCUMENT_ROOT'] . '/workouts/assets/fonts';
// echo '<br/>';

// Configure image content
$description =  wordwrap($_GET["description"], 38, "\n"); // Why?
$title = $_GET["designation"];
$excercises = str_replace(", ", ",\n", $_GET["exercises"]);
$user = "@Wodai.ly";

// echo $description;
// echo '<br/>';
// echo $title;echo '<br/>';
// echo $excercises;echo '<br/>';
// echo $user;echo '<br/>';

$img = greyscale($img);
$img = darken($img);

// Fallback: Single color instead of unsplash
// $img = imagecreate(1080, 1080) or die("Can't create image!");
// // We Allocate a color to be used as the canvas background
// $colorIndigo = imagecolorallocate($img, 0x3F, 0x51, 0xB5);
// // We Apply color as canvas background
// imagefill($img, 0, 0, $colorIndigo);

$white = imagecolorallocate($img, 255, 255, 255);

$w  = imagesx($img);
$h = imagesy($img);

$fontSize = 16;

$descriptionPosY = 250;
$excercisesPosY = 400;
$brandingPosY = $h - 100;
$padding = 0.1;
$excercisesMaxHeight = $brandingPosY - $excercisesPosY;

// Configs for variable font sizes
tryWrite($img, $descriptionPosY, $padding, $padding, array(56, 48, 42, 36, 32, 28, 24, 20, 16, 12, 8), $font_path, $description, $white);
tryWrite($img, $excercisesPosY, $padding, $padding, array(48, 42, 36, 28, 24, 20, 16, 12, 8), $font_path, $excercises, $white, $excercisesMaxHeight);
tryWrite($img, $brandingPosY, $padding, $padding, array(26), $font_path, $user, $white);

header('Content-Type: image/png');
header('Content-Disposition: Attachment;filename="Wodaily - ' . $title . '.png"');

imagepng($img);
imagedestroy($img);


function tryWrite($img, $offset, $padX, $padY, $fontsizes, $font, $text, $color, $maxHeight = -1)
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

        // Verify accepted bounds
        // - Text can't intercept general paddings (horizontal / vertical)
        // - Text also can't intercept textareas above / below itself
        $intersectsText = $maxHeight > 0 && $textHeight > $maxHeight;

        if (!$intersectsText && $textWidth <= $width - $width * $padX * 2 && $textHeight <= $height - $height * $padY * 2) {

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

function darken(&$img)
{
    imagefilter($img, IMG_FILTER_GAUSSIAN_BLUR, 50);
    imagefilter($img, IMG_FILTER_BRIGHTNESS, 5);
    return $img;
}

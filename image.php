<?php

// Config
$img_src = "https://source.unsplash.com/random/1080x1080/?crossfit";
$font = "C:\Users\superuser\AppData\Local\Microsoft\Windows\Fonts\PermanentMarker-Regular.ttf"; // TODO : Relative Path
$filename = "wods.json";
 

// Load data from json
$content = file_get_contents($filename);

if($content === false) {
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

// Configure image content
$description =  wordwrap($wod["description"], 38, "\n");
$title = $wod["name"];
$user = "@Wodai.ly";

$img = imagecreatefromjpeg($img_src);
$img = greyscale($img);

// TODO Whiten / darken image for better contrast
$color = imagecolorallocate($img, 255, 255, 255);
$stroke_color = imagecolorallocate($img, 0, 0, 0);
$stroke = 1.5;

// imagefilter($img, IMG_FILTER_BRIGHTNESS, -10);
// imagefilter($img, IMG_FILTER_GRAYSCALE);
$black = imagecolorallocate($img, 0, 0, 0);


// Setup GD
putenv('GDFONTPATH=' . realpath('.'));

// Image dimensions
$width = imagesx($img);
$height = imagesy($img);

// Text dimensions for description
$text_size = imagettfbbox(24, 0, $font, $description);
$text_width = getTextWidth($text_size);
$text_height = getTextHeight($text_size);

// Text dimensions for branding
$text_size_branding = imagettfbbox(32, 0, $font, $user);
$text_width_branding = getTextWidth($text_size_branding);
$text_height_branding = getTextHeight($text_size_branding);

// TODO
// Text dimensions for caption

// TODO
// Text dimensions for instructions

$centerX = CEIL(($width - $text_width) / 7);
$centerX = $centerX<0 ? 0 : $centerX;
$centerY = CEIL(($height - $text_height) / 2.4);
$centerY = $centerY<0 ? 0 : $centerY;

printBranding($img, $width, $height, $text_width_branding, $text_height_branding, $font, $user, 32);

// Description
imagettftext($img, 36, 0, $centerX, $centerY,$color, $font, $description);

// Caption
imagettftext($img, 50, 0, $centerX+362, $centerY-90,$color, $font, $title);

header('Content-type: image/png');
header('Content-Disposition: Attachment;filename="Wodaily - ' . $title . '.png"');

imagepng($img);
imagedestroy($jpg_image);

// --------------- Functions --------------- //

function greyscale(&$img) {

    $src = $img;
    $dst = $img;

    imagecopymergegray($dst, $src, 0, 0, 0, 0, 1080, 1080, 0);

    return $dst;
}

function findObjectById($array, $permalink){
    foreach ( $array as $element ) {
        if ( $permalink == $element["permalink"] ) {
            return $element;
        }
    }

    return false;
}

function getTextWidth($textsize) {
    return max([$textsize[2], $textsize[4]]) - min([$textsize[0], $textsize[6]]);
}

function getTextHeight($textsize) {
    return max([$textsize[5], $textsize[7]]) - min([$textsize[1], $textsize[3]]);
}

// Print branding
// Lower Middle, horizontally centered
function printBranding($img, $width, $height, $textwidth, $textheight, $font, $text, $fontsize) {
    $centerX = CEIL(($width - $textwidth) / 7);
    $centerX = $centerX<0 ? 0 : $centerX;
    $centerY = CEIL(($height - $textheight) / 2.4);
    $centerY = $centerY<0 ? 0 : $centerY;

    $bottomY = $height - $textheight - 170;
    $fontcolor = imagecolorallocate($img, 237, 237, 237);
    imagettftext($img, $fontsize, 0, $centerX + 362, $bottomY, $fontcolor, $font, $text);
}

?>
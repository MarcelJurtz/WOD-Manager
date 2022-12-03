<?php

// Config
$img_src = "https://source.unsplash.com/random/1080x1080/?crossfit";
// $font = "C:\Users\superuser\AppData\Local\Microsoft\Windows\Fonts\PermanentMarker-Regular.ttf"; // TODO : Relative Path
// $font = "./assets/fonts/permanent-marker-v16-latin-regular.ttf";
$font = "permanent-marker-v16-latin-regular";
$filename = "wods.json";

$GLOBALS['debug'] = true;

if ($GLOBALS['debug']) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

// TODO: Refresh Picture while keeping text

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

$img = imagecreatefromjpeg($img_src);
$img = greyscale($img);

// Fallback: Single color instead of unsplash
// $img = imagecreate(1080, 1080) or die("Can't create image!");
// We Allocate a color to be used as the canvas background
// $colorIndigo = imagecolorallocate($img, 0x3F, 0x51, 0xB5);
// We Apply color as canvas background
// imagefill($img, 0, 0, $colorIndigo);

$white = imagecolorallocate($img, 255, 255, 255);

// putenv('GDFONTPATH=' . realpath('.') . '/assets/fonts');

$myCanvasWidth  = imagesx($img);
$myCanvasHeight = imagesy($img);

$fontSize = 16;

writeDescription($img, $myCanvasWidth, $myCanvasHeight, 16, 0, $font, $description, $white);
writeInstructions($img, $myCanvasWidth, $myCanvasHeight, 16, 0, $font, $excercises, $white);
writeBranding($img, $myCanvasWidth, $myCanvasHeight, 30, 0, $font, $user, $white);

header('Content-Type: image/png');
header('Content-Disposition: Attachment;filename="Wodaily - ' . $title . '.png"');

imagepng($img);
imagedestroy($img);

// Content is very variable in width and height
// Target is to put text in boxes to achieve a maximum frame with a horizontal padding of 10% and a vertical padding of 30%
// Smaller text should maintain a specific textsize, while bigger text is being scaled down
function writeDescription($img, $width, $height, $fontsize, $angle, $font, $text, $color)
{
    $fontsizeDecreaseInterval = 2;
    $instructionsOffset = -250;

    $padLeft = 0.1 * $width;
    $padRight = 0.1 * $width;
    $padTop = 0.3 * $height;
    $padBottom = 0.2 * $height;

    $maxAttempts = 10;
    $attempt = 0;

    do {
        $dynamicFontSize = $width / ($fontsize - $attempt * $fontsizeDecreaseInterval);
        $attempt += 1;

        // Try to fit with default size
        $textBounds = imageftbbox($dynamicFontSize, $angle, $font, $text);

        // Get the text upper, lower, left and right corner bounds of our text bounding box...
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

        // Current Text: 386/846 - Current Img: 432/864
        $widthFits = $textWidth <= $width - $padLeft - $padRight;
        $heightFits = $textHeight <= $height - $padTop - $padBottom;

        if ($widthFits && $heightFits) {

            // Text fits content, append to image and exit
            //Get the starting position for centering
            $start_x_offset = ($width - $textWidth) / 2;
            $start_y_offset = (($height - $textHeight) + $fontsize * 2) / 2 + $instructionsOffset;

            // Write text to the image using TrueType fonts
            imagettftext($img, $dynamicFontSize, $angle, (int)$start_x_offset, (int)$start_y_offset, $color, $font, $text);

            break;
        }
    } while (true);
}

// Content is very variable in width and height
// Target is to put text in boxes to achieve a maximum frame with a horizontal padding of 10% and a vertical padding of 30%
// Smaller text should maintain a specific textsize, while bigger text is being scaled down
function writeInstructions($img, $width, $height, $fontsize, $angle, $font, $text, $color)
{
    $fontsizeDecreaseInterval = 2;
    $instructionsOffset = 100;

    $padLeft = 0.1 * $width;
    $padRight = 0.1 * $width;
    $padTop = 0.3 * $height;
    $padBottom = 0.2 * $height;

    $maxAttempts = 10;
    $attempt = 0;

    do {
        $dynamicFontSize = $width / ($fontsize - $attempt * $fontsizeDecreaseInterval);
        $attempt += 1;

        // Try to fit with default size
        $textBounds = imageftbbox($dynamicFontSize, $angle, $font, $text);

        // Get the text upper, lower, left and right corner bounds of our text bounding box...
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

        // Current Text: 386/846 - Current Img: 432/864
        $widthFits = $textWidth <= $width - $padLeft - $padRight;
        $heightFits = $textHeight <= $height - $padTop - $padBottom;

        // if ($GLOBALS['debug']) {
        //     echo "Verifying image dimensions:";
        //     echo "Height Fits: " . $heightFits . " - Width Fits: " . $widthFits . "<br/>";
        //     echo "Height: " . $height . " - " . $padTop . " - " . $padBottom . " = " . $height - $padTop - $padBottom . "ASD<br/>";
        //     echo "Width: " . $width . " - " . $padLeft . " - " . $padRight . " = " . $width - $padLeft - $padRight . "<br/>";
        // }

        if ($widthFits && $heightFits) {

            // Text fits content, append to image and exit
            //Get the starting position for centering
            $start_x_offset = ($width - $textWidth) / 2;
            $start_y_offset = (($height - $textHeight) + $fontsize * 2) / 2 + $instructionsOffset;

            // Write text to the image using TrueType fonts
            imagettftext($img, $dynamicFontSize, $angle, (int)$start_x_offset, (int)$start_y_offset, $color, $font, $text);

            break;
        }

        // if ($attempt >= $maxAttempts) {
        //     echo "<script>console.err('Too many attempts to scale image')</script>";
        //     break;
        // }

        // echo "Text too big - Recalculating. Attempt " . $attempt . " Current Text: " . $textWidth . "/" . $textHeight . " - Current Img: " . $height . "/" . $width . " - Paddings (TRBL) " . $padTop . "/" . $padRight . "/" . $padBottom . "/" . $padLeft . "<br/>";
    } while (true);
}

// Branding is fixed text, centered at the lower end of the image
function writeBranding($img, $width, $height, $fontsize, $angle, $font, $text, $color)
{
    $fontsize = $width / $fontsize;

    $brandingOffset = 450;
    $myTextBoundingBox = imageftbbox($fontsize, $angle, $font, $text);

    // Get the text upper, lower, left and right corner bounds of our text bounding box...
    $lower_left_x  = $myTextBoundingBox[0];
    $lower_left_y  = $myTextBoundingBox[1];
    $lower_right_x = $myTextBoundingBox[2];
    $lower_right_y = $myTextBoundingBox[3];
    $upper_right_x = $myTextBoundingBox[4];
    $upper_right_y = $myTextBoundingBox[5];
    $upper_left_x  = $myTextBoundingBox[6];
    $upper_left_y  = $myTextBoundingBox[7];

    // Get Text Width and Height
    $myTextWidth  =  $lower_right_x - $lower_left_x; //or  $upper_right_x - $upper_left_x
    $myTextHeight = $lower_right_y - $upper_right_y; //or  $lower_left_y - $upper_left_y

    //Get the starting position for centering
    $start_x_offset = ($width - $myTextWidth) / 2;
    $start_y_offset = (($height - $myTextHeight) + $fontsize * 2) / 2 + $brandingOffset;

    // Write text to the image using TrueType fonts
    imagettftext($img, $fontsize, $angle, (int)$start_x_offset, (int)$start_y_offset, $color, $font, $text);
}

function greyscale(&$img)
{

    $src = $img;
    $dst = $img;

    imagecopymergegray($dst, $src, 0, 0, 0, 0, 1080, 1080, 0);

    return $dst;
}

function findObjectById($array, $permalink)
{
    foreach ($array as $element) {
        if ($permalink == $element["permalink"]) {
            return $element;
        }
    }

    return false;
}

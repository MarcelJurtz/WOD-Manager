<?php

// Config
$img_src = "https://source.unsplash.com/random/1080x1080/?crossfit";
// $font = "C:\Users\superuser\AppData\Local\Microsoft\Windows\Fonts\PermanentMarker-Regular.ttf"; // TODO : Relative Path
// $font = "./assets/fonts/permanent-marker-v16-latin-regular.ttf";
$font = "permanent-marker-v16-latin-regular";
$filename = "wods.json";

// TODO: Refresh Picture while keeping text

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
$excercises = implode("\n",$wod["excercises"]);
$user = "@Wodai.ly";

// echo json_encode($wod);
// echo "<br/><br/>";

// echo implode("<br>",$wod["excercises"]);

// echo "<br/><br/>";
// print_r($wod);

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
putenv('GDFONTPATH=' . realpath('.') . '/assets/fonts');

// Image dimensions
$myCanvasWidth  = imagesx($img);
$myCanvasHeight = imagesy($img);

// We Create an empty and dark canvas from these dimensions above
$myCanvas = $img;
// We Allocate a color to be used as the canvas background
$colorIndigo = imagecolorallocate($myCanvas, 0x3F, 0x51, 0xB5);
// We Apply color as canvas background
imagefill($myCanvas, 0, 0, $colorIndigo);

// We Allocate a color to be used with the canvas text
$colorWhite = imagecolorallocate($myCanvas, 0xFF, 0xFF, 0xFF);

// We Declare our TTF font path in Windows 10...
$myFont = $font;

// Static font seed value...
$fontSize = 16;
// We set the dynamic font size
$myFontSize = $myCanvasWidth / $fontSize;
// We set the text angle
$myTextAngle = 0;

// We Declare the text string to be drawn on canvas...
$myText = $excercises; //"Very very really long title";//$title;

// We Calculate and return the bounding box in pixels for the text string to be drawn on canvas...
$myTextBoundingBox = imageftbbox($myFontSize, $myTextAngle, $myFont, $myText);

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
$start_x_offset = ($myCanvasWidth - $myTextWidth) / 2;
$start_y_offset = (($myCanvasHeight - $myTextHeight) + $myFontSize * 2) / 2;

// Write text to the image using TrueType fonts
// imagettftext($myCanvas, $myFontSize, $myTextAngle, (int)$start_x_offset, (int)$start_y_offset, $colorWhite, $myFont, $myText);

writeBranding($img, $myCanvasWidth, $myCanvasHeight, 30, 0, $font, $user, $color);

// Draw a horizontal dashed line for reference only
// imagedashedline($myCanvas, 0, $myCanvasHeight/2, $myCanvasWidth, $myCanvasHeight/2, $colorWhite);
// Draw a vertical dashed line for reference only
// imagedashedline($myCanvas, $myCanvasWidth/2, 0, $myCanvasWidth/2, $myCanvasHeight, $colorWhite);

// We set the correct http header for png images...
header('Content-Type: image/png');
header('Content-Disposition: Attachment;filename="Wodaily - ' . $title . '.png"');


// We Output a PNG image to either the browser or a file
imagepng($myCanvas);
// Finally, we free any memory associated with myCanvas; the image. 
imagedestroy($myCanvas);

// header('Content-type: image/png');
// header('Content-Disposition: Attachment;filename="Wodaily - ' . $title . '.png"');

function writeBranding($img, $width, $height, $fontsize, $angle, $font, $text, $color) {

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
?>

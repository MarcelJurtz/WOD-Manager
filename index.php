<?php

// TODO

// error_reporting(E_ALL);
require_once('./admin/shared/db.php');

if (!isset($_GET['name']) || empty($_GET['name']) || !isset($_GET['token']) || empty($_GET['token'])) {
  http_response_code(404);
  include('./pages/404.html');
  die();
}

$name = $_GET['name'];
$token = $_GET['token'];

$text1 = $_GET['text1'];
$text2 = $_GET['text2'];
$text3 = $_GET['text3'];
$text4 = $_GET['text4'];

$con = getConnection();

// Log Request
$uri = $_SERVER['REQUEST_URI'];
$ip = $_SERVER['REMOTE_ADDR'];
$src = 'api';
$stmt = $con->prepare('INSERT INTO log (source, ip, params) VALUES (?, ?, ?)');
$stmt->bind_param('sss', $src, $ip, $uri);
$status = $stmt->execute();
$stmt->close();

$stmt = $con->prepare('SELECT l.id, l.template, l.font, l.landscape, l.filename, l.background_url, COALESCE(dl.ct, 0) FROM licenses l LEFT JOIN (SELECT license_id, count(*) ct FROM download_log GROUP BY license_id) AS dl ON l.id = dl.license_id WHERE l.token = ? AND l.enabled = 1');

$stmt->bind_param('s', $token);
$stmt->execute();
$stmt->bind_result($certId, $template, $font, $landscape, $filename, $backgroundUrl, $dlCount);
$stmt->fetch();
$stmt->close();

if (!isset($template) || trim($template) === '') {
  http_response_code(404);
  include('./pages/404.html');
  die();
}

// Log Download Request
$stmt = $con->prepare('INSERT INTO download_log (license_id, params) VALUES (?, ?)');
$stmt->bind_param('ss', $certId, $token);
$status = $stmt->execute();
$stmt->close();

if (!isset($filename) || trim($filename) === '') {
  $filename = "certificate";
}

if(!isset($font) || trim($font) === '') {
  $font = "roboto";
}

$template = str_replace(array('{{name}}', '{{ name }}'), $name, $template);
$template = str_replace(array('{{nummer}}', '{{ nummer }}'), $dlCount + 1, $template);
$template = str_replace(array('{{datum}}', '{{ datum }}'), date("d.m.Y"), $template);

if (isset($text1)) {
  $template = str_replace(array('{{text1}}', '{{ text1 }}'), $text1, $template);
}

if (isset($text2)) {
  $template = str_replace(array('{{text2}}', '{{ text2 }}'), $text2, $template);
}

if (isset($text3)) {
  $template = str_replace(array('{{text3}}', '{{ text3 }}'), $text3, $template);
}

if (isset($text4)) {
  $template = str_replace(array('{{text4}}', '{{ text4 }}'), $text4, $template);
}

$mpdf = new \Mpdf\Mpdf([
  'mode' => 'utf-8',
  'format' => 'A4',
  'orientation' => $landscape == 0 ? 'P' : 'L',
  'margin_left' => 0,
  'margin_right' => 0,
  'margin_top' => 0,
  'margin_bottom' => 0,
  'margin_header' => 0,
  'margin_footer' => 0,
  'fontDir' => __DIR__ . '/assets/fonts',
  'fontdata' => [
    'roboto' => [
      'R' => 'Roboto-Regular.ttf',
      'I' => 'Roboto-Ictalic.ttf',
    ],
    'roboto-mono' => [
      'R' => 'RobotoMono-Medium.ttf',
      'I' => 'RobotoMono-Italic.ttf',
    ],
    'open-sans' => [
      'R' => 'OpenSans-Regular.ttf',
      'I' => 'OpenSans-Italic.ttf'
    ],
    'lato' => [
      'R' => 'Lato-Regular.ttf',
      'I' => 'Lato-Italic.ttf'
    ],
    'comforter' => [
      'R' => 'Comforter-Regular.ttf'
    ],
    'bebas-neue' => [
      'R' => 'BebasNeue-Regular.ttf',
    ]
  ],
  'default_font' => $font
]);

try {
  if (isset($backgroundUrl) && $backgroundUrl != null && $backgroundUrl != "") {
    $mpdf->SetDefaultBodyCSS('background', "url('" . $backgroundUrl . "')");
    $mpdf->SetDefaultBodyCSS('background-image-resize', 6);
  }

  $mpdf->WriteHTML($template);
  $mpdf->Output($filename . ".pdf", 'D');
} catch (Exception $e) {
  $stmt = $con->prepare('INSERT INTO log (source, ip, params) VALUES ("server", "", ?)');
  $stmt->bind_param('s', $e->getMessage());
  $status = $stmt->execute();
  $stmt->close();
}

if (isset($redirectUrl) && trim($redirectUrl) !== '') {
  header("Location: " . $redirectUrl);
}

<?php

$imagePath = $_SERVER['DOCUMENT_ROOT'] . '/res/img/logo.webp';
$destPath = $_SERVER['DOCUMENT_ROOT'] . '/res/img/';

if (!isset($_FILES['logo'])) exit('No file uploaded');

// Store the input file under imagePath AS WEBP
$im = new Imagick($_FILES['logo']['tmp_name']);
$im->setImageFormat('webp');
$im->writeImage($imagePath);

$im = new Imagick($imagePath);
$im->resizeImage(256, 256, Imagick::FILTER_LANCZOS, 1);
$im->setImageFormat('png');
// Remove the paintTransparentImage line
// $im->paintTransparentImage('white', 0.0, 10000);
$im->writeImage($destPath . 'favicon-256.png');

$sizes = [16, 32, 64, 128];
foreach ($sizes as $size) {
    $im->resizeImage($size, $size, Imagick::FILTER_LANCZOS, 1);
    $im->writeImage($destPath . "favicon-$size.png");
}

$im->clear();
$im->destroy();

$ico = new Imagick();
$ico->setFormat('ico');
foreach ($sizes as $size) {
    $ico->readImage($destPath . "favicon-$size.png");
}
$ico->readImage($destPath . 'favicon-256.png');
$ico->setImageFormat('ico');
$ico->writeImage($destPath . 'favicon.ico');

$ico->clear();
$ico->destroy();

header('Location: /admin/settings.php');

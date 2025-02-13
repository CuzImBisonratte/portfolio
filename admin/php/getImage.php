<?php

// Check user login state
session_start();
if (!isset($_SESSION['login'])) {
    die;
}

// Check if page is set
if (!isset($_GET['page'])) {
    die;
}

// Check if img is set
if (!isset($_GET['img'])) {
    die;
}

$page = htmlspecialchars($_GET['page'], ENT_QUOTES, 'UTF-8');
$img = htmlspecialchars($_GET['img'], ENT_QUOTES, 'UTF-8');
$img_location = $_SERVER['DOCUMENT_ROOT'] . '/admin/images/' . $page . '/' . $img;

// Check if file exists
if (!file_exists($img_location . '.protected')) {
    die('File not found');
}

// Return the file from img_location with additional ".protected" extension
$img = file_get_contents($img_location . '.protected');

// Set response headers 
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime_type = finfo_buffer($finfo, $img);
finfo_close($finfo);

header('Content-Type: ' . $mime_type);
header('Content-Length: ' . strlen($img));

// Output the image
echo $img;

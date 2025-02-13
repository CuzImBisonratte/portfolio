<?php

// Check user login state
session_start();
if (!isset($_SESSION['login'])) {
    header('Location: /admin/login.php');
    die;
}

// Include config file
include_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';

// Check if file(s) were uploaded
if (!isset($_FILES['file'])) die(1);

// Check if page is set
if (!isset($_GET['page'])) die(2);
$page = $_GET['page'];

// DB connection
$con = mysqli_connect($CONFIG['db']['host'], $CONFIG['db']['user'], $CONFIG['db']['password'], $CONFIG['db']['database']);
if (mysqli_connect_errno()) {
    echo 'Failed to connect to MySQL: ' . mysqli_connect_error();
    die;
}

// Check if file is an image
if (getimagesize($_FILES['file']['tmp_name'])) {
    // Generate unique ID
    $id = bin2hex(random_bytes(4));
    // Get filename
    $name = $_FILES['file']['name'];
    // Move file to images directory
    move_uploaded_file($_FILES['file']['tmp_name'], $_SERVER['DOCUMENT_ROOT'] . '/admin/images/' . $_GET["page"] . "/" . $id . '.' . pathinfo($name, PATHINFO_EXTENSION) . ".protected");
    // Get exif data
    $exif = exif_read_data($_SERVER['DOCUMENT_ROOT'] . '/admin/images/' . $_GET["page"] . '/' . $id . '.' . pathinfo($name, PATHINFO_EXTENSION) . ".protected");
    // Fix weird exif formats
    if (isset($exif['FNumber']) && strpos($exif['FNumber'], '/') !== false) $exif['FNumber'] = explode('/', $exif['FNumber'])[0] / explode('/', $exif['FNumber'])[1];
    if (isset($exif['FocalLength']) && strpos($exif['FocalLength'], '/') !== false) $exif['FocalLength'] = explode('/', $exif['FocalLength'])[0] / explode('/', $exif['FocalLength'])[1];
    if (isset($exif['ExposureTime']) && strpos($exif['ExposureTime'], '/') !== false) $exif['ExposureTime'] = '1/' . explode('/', $exif['ExposureTime'])[1] / explode('/', $exif['ExposureTime'])[0];

    // Generate camera info string
    $camInfoString = '';
    $camInfoString .= isset($exif['Model']) ? $exif['Model'] : "";
    $camInfoString .= isset($exif['FocalLength']) ? ", " . $exif['FocalLength'] . "mm" : "";
    $camInfoString .= isset($exif['FNumber']) ? ", " . (strpos($exif['FNumber'], 'f') === false ? 'f/' . $exif['FNumber'] : $exif['FNumber']) : "";
    $camInfoString .= isset($exif['ExposureTime']) ? ", " . $exif['ExposureTime'] . (strpos($exif['ExposureTime'], 's') === false ? 's' : '') : "";
    $camInfoString .= isset($exif['ISOSpeedRatings']) ? ", ISO " . $exif['ISOSpeedRatings'] : "";

    // Save image as WEBP for faster loading with 1920x? resolution
    $im = new Imagick($_SERVER['DOCUMENT_ROOT'] . '/admin/images/' . $_GET["page"] . '/' . $id . '.' . pathinfo($name, PATHINFO_EXTENSION) . ".protected");
    $img_rotate = $im->getImageOrientation();
    $im->setImageFormat('webp');
    $im->setImageCompressionQuality(80);
    if ($img_rotate != 1) {
        switch ($img_rotate) {
            case 3:
                $im->rotateImage("#000", 180);
                break;
            case 6:
                $im->rotateImage("#000", 90);
                break;
            case 8:
                $im->rotateImage("#000", -90);
                break;
        }
    }
    $im->resizeImage(1920, 0, Imagick::FILTER_LANCZOS, 1);
    $im->writeImage($_SERVER['DOCUMENT_ROOT'] . '/admin/images/' . $_GET["page"] . '/' . $id . '.webp.protected');
    $im->clear();

    // Insert image into database
    $insert_source = $id;
    $insert_artist = isset($exif['Artist']) ? $exif['Artist'] : '';
    $insert_time = isset($exif['DateTime']) ? $exif['DateTime'] : '';
    $insert_filetype = pathinfo($name, PATHINFO_EXTENSION);
    $stmt = $con->prepare("INSERT INTO `image`(`id`, `page_id`, alt, camera, artist, time, fileformat) VALUES (?, ?, '', ?, ?, ?, ?)");
    $stmt->bind_param('ssssss', $insert_source, $page, $camInfoString, $insert_artist, $insert_time, $insert_filetype);
    $stmt->execute();
    $stmt->close();
}
echo false;

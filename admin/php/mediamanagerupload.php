<?php

// Check user login state
session_start();
if (!isset($_SESSION['login'])) {
    header('Location: /admin/login.php');
    die;
}

// Include config file
include_once $_SERVER['DOCUMENT_ROOT'] . '/admin/php/config.php';

// Check if file(s) were uploaded
if (!isset($_FILES['files'])) {
    header('Location: /admin/editor.php');
    die;
}

// Check if page is set
if (!isset($_GET['page'])) {
    header('Location: /admin/editor.php');
}

// Load page config
include_once $_SERVER['DOCUMENT_ROOT'] . '/admin/pages/' . $_GET['page'] . '/pageConfig.php';

// Loop through uploaded files
foreach ($_FILES['files']['name'] as $key => $name) {
    // Check if file is an image
    if (getimagesize($_FILES['files']['tmp_name'][$key])) {
        // Generate unique ID
        $id = bin2hex(random_bytes(4));
        // Move file to images directory
        move_uploaded_file($_FILES['files']['tmp_name'][$key], $_SERVER['DOCUMENT_ROOT'] . '/admin/pages/' . $_GET["page"] . '/images/' . $id . '.' . pathinfo($name, PATHINFO_EXTENSION));
        // Get exif data
        $exif = exif_read_data($_SERVER['DOCUMENT_ROOT'] . '/admin/pages/' . $_GET["page"] . '/images/' . $id . '.' . pathinfo($name, PATHINFO_EXTENSION));
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
        // Add image to page config
        $pageConfig['images'][$id] = array(
            'src' => $id . '.' . pathinfo($name, PATHINFO_EXTENSION),
            'alt' => '',
            'camInfo' => $camInfoString,
            'artist' => isset($exif['Artist']) ? $exif['Artist'] : '',
            'dateTaken' => isset($exif['DateTime']) ? $exif['DateTime'] : ''
        );
        // Save image as WEBP for faster loading with 1920x? resolution
        $im = new Imagick($_SERVER['DOCUMENT_ROOT'] . '/admin/pages/' . $_GET["page"] . '/images/' . $id . '.' . pathinfo($name, PATHINFO_EXTENSION));
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
        $im->writeImage($_SERVER['DOCUMENT_ROOT'] . '/admin/pages/' . $_GET["page"] . '/images/' . $id . '.webp');
        $im->clear();
    }
}

// Save page config
file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/admin/pages/' . $_GET['page'] . '/pageConfig.php', '<?php $pageConfig = ' . var_export($pageConfig, true) . ';');

// Sleep to wait for all processes to finish
sleep(3);

// Redirect to editor
header('Location: /admin/editor.php?page=' . $_GET['page'] . '&mediamanager');

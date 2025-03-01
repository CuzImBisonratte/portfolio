<?php

set_time_limit(0); // No time limit

// Check user login state
session_start();
if (!isset($_SESSION['login'])) {
    header('Location: /admin/login.php');
    die;
}

// Include config file
include_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';

// DB connection
$con = mysqli_connect($CONFIG['db']['host'], $CONFIG['db']['user'], $CONFIG['db']['password'], $CONFIG['db']['database']);
if (mysqli_connect_errno()) {
    echo 'Failed to connect to MySQL: ' . mysqli_connect_error();
    die;
}

// Check if pages POST array is given and not empty
if (!isset($_POST['pages']) || empty($_POST['pages'])) {
    echo 'No pages to deploy';
    die;
}

// Check if POST watermark_position is given
if (isset($_POST['watermark_position'])) {
    $watermarkPosition = $_POST['watermark_position'];
} else {
    $watermarkPosition = NULL;
}

// Loop through pages
foreach ($_POST['pages'] as $page) {
    // Check if page exists
    $result = $con->query('SELECT * FROM page WHERE id = "' . $page . '"');
    if ($result->num_rows == 0) {
        echo 'Page ' . $page . ' does not exist';
        die;
    }
    $pageData = $result->fetch_assoc();

    // Get all needed data for page
    $stmt = $con->prepare('SELECT cluster.type cluster_type,cluster.position cluster_position,view.position image_position, CONCAT(image.id,".",image.fileformat) image_file, image.camera img_details,image.alt img_alt,image.artist img_artist,image.time img_time FROM cluster,view,image WHERE view.cluster_id = cluster.id AND view.image_id = image.id AND image.page_id = cluster.page_id AND cluster.page_id = ?;');
    $stmt->bind_param('s', $page);
    $stmt->execute();
    $result = $stmt->get_result();
    $contentInfo = $result->fetch_all(MYSQLI_ASSOC);
    if (empty($contentInfo)) {
        echo 'No content for page ' . $page;
        continue;
    }

    // Format the content info
    $clusters = [];
    $images = [];
    foreach ($contentInfo as $content) {
        $clusterType = $content['cluster_type'];
        $clusterPosition = $content['cluster_position'];
        $imagePosition = $content['image_position'];
        $imageFile = $content['image_file'];
        $imgDetails = $content['img_details'];
        $imgAlt = $content['img_alt'];
        $imgArtist = $content['img_artist'];
        $imgTime = $content['img_time'];

        if (!isset($clusters[$clusterPosition])) {
            $clusters[$clusterPosition] = [];
        }
        if (!isset($clusters[$clusterPosition][$clusterType])) {
            $clusters[$clusterPosition][$clusterType] = [];
        }
        if (!isset($clusters[$clusterPosition][$clusterType][$imagePosition])) {
            $clusters[$clusterPosition][$clusterType][$imagePosition] = [];
        }
        $clusters[$clusterPosition][$clusterType][$imagePosition] = [
            'file' => $imageFile,
            'details' => $imgDetails,
            'alt' => $imgAlt,
            'artist' => $imgArtist,
            'time' => $imgTime
        ];
        $images[] = $imageFile;
    }

    // Delete /PAGENAME and recreate it
    $path = $_SERVER['DOCUMENT_ROOT'] . '/' . $pageData['id'];
    if (file_exists($path) && is_dir($path)) {
        function delete_files($dir)
        {
            foreach (glob($dir . '/*') as $file) {
                if (is_dir($file)) delete_files($file);
                else unlink($file);
            }
            rmdir($dir);
        }
        delete_files($path);
    }
    mkdir($path);

    // Prepare images
    foreach ($images as $image) {
        $img_name = explode(".", $image)[0];
        // Create high-res image (original size)
        $i = new Imagick($_SERVER['DOCUMENT_ROOT'] . '/admin/images/' . $pageData['id'] . '/' . $image . '.protected');
        $i->setImageCompressionQuality(50);
        $i->setImageFormat('webp');
        // Add watermark
        if ($watermarkPosition) {
            $origX = $i->getImageWidth();
            $origY = $i->getImageHeight();
            $watermarkSize = min($origX, $origY) * ($CONFIG['watermark']['scale'] ?? 0.075);
            $watermark = new Imagick($_SERVER['DOCUMENT_ROOT'] . '/res/img/logo.webp');
            $watermark->thumbnailImage($watermarkSize, 0);
            $watermark->setImageAlphaChannel(Imagick::ALPHACHANNEL_ACTIVATE);
            $watermark->evaluateImage(Imagick::EVALUATE_MULTIPLY, 0.5, Imagick::CHANNEL_ALPHA);
            switch ($watermarkPosition) {
                case 'top-right':
                    $i->compositeImage($watermark, Imagick::COMPOSITE_OVER, $origX - $watermarkSize - $CONFIG['watermark']['fixX'], 0 + $CONFIG['watermark']['fixY']);
                    break;
                case 'top-left':
                    $i->compositeImage($watermark, Imagick::COMPOSITE_OVER, $CONFIG['watermark']['fixX'], $CONFIG['watermark']['fixY']);
                    break;
                case 'bottom-right':
                    $i->compositeImage($watermark, Imagick::COMPOSITE_OVER, $origX - $watermarkSize - $CONFIG['watermark']['fixX'], $origY - $watermarkSize + $CONFIG['watermark']['fixY']);
                    break;
                case 'bottom-left':
                    $i->compositeImage($watermark, Imagick::COMPOSITE_OVER, $CONFIG['watermark']['fixX'], $origY - $watermarkSize + $CONFIG['watermark']['fixY']);
                    break;
            }
        }
        $i->writeImage($path . '/' . $img_name . '-full.webp');

        // Create low-res preview image
        $i->setImageCompressionQuality(50);
        $i->thumbnailImage(600, 0);
        $i->setImageFormat('webp');
        $i->writeImage($path . '/' . $img_name . '-thumb.webp');
    }

    // File content
    $content = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/admin/templates/page.html');
    $content = str_replace('{{title}}', $pageData['title'], $content);
    $content = str_replace('{{subtitle}}', $pageData['subtitle'], $content);

    // Generate clusters content
    $cluster_content = '';
    foreach ($clusters as $cluster) {
        $cluster_content .= '<div class="image-cluster ' . key($cluster) . '">';
        $image_num = count($cluster[key($cluster)]);
        for ($j = 1; $j <= $image_num; $j++) {
            $cluster_content .= '<img src="' . explode(".", $cluster[key($cluster)][$j]['file'])[0] . '-thumb.webp" alt="' . $cluster[key($cluster)][$j]['alt'] . '" class="i' . $j . ' onclick="openFullscreenView(' . explode(".", $cluster[key($cluster)][$j]['file'])[0] . ')" />';
        }
        $cluster_content .= '</div>';
    }

    $content = str_replace('{{clusters}}', $cluster_content, $content);

    $content = str_replace('{{footer}}', $CONFIG['footer'], $content);

    // Write content to file
    file_put_contents($path . '/index.html', $content);
}

// Redirect to admin page
header('Location: /admin/index.php');

<?php

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

// Temporary name
$tempName = bin2hex(random_bytes(4));

// Generate new images folder 
$imagesFolder = $_SERVER['DOCUMENT_ROOT'] . '/admin/images/' . $tempName;
mkdir($imagesFolder);

// Insert new page
$con->query('INSERT INTO page (id, title, subtitle, cover_image_id) VALUES ("' . $tempName . '", "No Title", "Subtitle", NULL)');

// Redirect to edit page
header('Location: /admin/editor.php?page=' . $tempName);

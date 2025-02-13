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

// Include config file
include_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';

// DB connection
$con = mysqli_connect($CONFIG['db']['host'], $CONFIG['db']['user'], $CONFIG['db']['password'], $CONFIG['db']['database']);
if (mysqli_connect_errno()) {
    echo 'Failed to connect to MySQL: ' . mysqli_connect_error();
    die;
}
$stmt = $con->prepare('UPDATE page SET cover_image_id = ? WHERE id = ?');
$stmt->bind_param('ss', $_GET['img'], $_GET['page']);
$stmt->execute();
$stmt->close();

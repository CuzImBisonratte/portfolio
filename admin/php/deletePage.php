<?php

// Check user login state
session_start();
if (!isset($_SESSION['login'])) {
    die;
}

// Check if old page is set
if (!isset($_GET['page'])) {
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

// Delete folder
$page = $_GET['page'];
if (!rmdir($_SERVER['DOCUMENT_ROOT'] . '/admin/images/' . $page)) {
    die;
}
if (is_dir($_SERVER['DOCUMENT_ROOT'] . $page)) {
    if (!rmdir($_SERVER['DOCUMENT_ROOT'] . $page)) {
        die;
    }
}

// Update database
$stmt = $con->prepare('DELETE FROM page WHERE id = ?');
$stmt->bind_param('s', $page);
$stmt->execute();
$stmt->close();

// Redirect back
header('Location: /admin/editor.php?page=' . $newURL);
die;

<?php

// Check user login state
session_start();
if (!isset($_SESSION['login'])) {
    die;
}

// Check if old page is set
if (!isset($_GET['oldURL'])) {
    die;
}

// Check if new page is set
if (!isset($_POST['pageLink'])) {
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

// Move folder
$oldURL = $_GET['oldURL'];
$newURL = strtolower($_POST['pageLink']);
if (!rename($_SERVER['DOCUMENT_ROOT'] . '/admin/images/' . $oldURL, $_SERVER['DOCUMENT_ROOT'] . '/admin/images/' . $newURL)) {
    die;
}

// Move output folder if exists
if (is_dir($_SERVER['DOCUMENT_ROOT'] . $oldURL)) {
    if (!rename($_SERVER['DOCUMENT_ROOT'] . $oldURL, $_SERVER['DOCUMENT_ROOT'] . $newURL)) {
        die;
    }
}

// Update database
$stmt = $con->prepare('UPDATE page SET id = ? WHERE id = ?');
$stmt->bind_param('ss', $newURL, $oldURL);
$stmt->execute();
$stmt->close();


// Redirect back
header('Location: /admin/editor.php?page=' . $newURL);
die;

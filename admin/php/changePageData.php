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

// Include config file
include_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';

// DB connection
$con = mysqli_connect($CONFIG['db']['host'], $CONFIG['db']['user'], $CONFIG['db']['password'], $CONFIG['db']['database']);
if (mysqli_connect_errno()) {
    echo 'Failed to connect to MySQL: ' . mysqli_connect_error();
    die;
}

if (!isset($_POST['title']) || !isset($_POST['subtitle'])) {
    die;
}

$stmt = $con->prepare('UPDATE page SET title = ?, subtitle = ? WHERE id = ?');
$stmt->bind_param('sss', $_POST['title'], $_POST['subtitle'], $_GET['page']);
$stmt->execute();
$stmt->close();

var_dump($_POST);

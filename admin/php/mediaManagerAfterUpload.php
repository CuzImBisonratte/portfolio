<?php

// Check user login state
session_start();
if (!isset($_SESSION['login'])) {
    header('Location: /admin/login.php');
    die;
}

// Include config file
include_once $_SERVER['DOCUMENT_ROOT'] . '/admin/php/config.php';

// Check if page is set
if (!isset($_GET['page'])) {
    header('Location: /admin/editor.php');
}

// Load page config
include_once $_SERVER['DOCUMENT_ROOT'] . '/admin/pages/' . $_GET['page'] . '/pageConfig.php';

// Load every file in /admin/pages/PAGE/images/*.php and add it to $pageConfig['images']
$files = glob($_SERVER['DOCUMENT_ROOT'] . '/admin/pages/' . $_GET['page'] . '/images/*.php');
foreach ($files as $file) {
    $item = include $file;
    $id = pathinfo($file, PATHINFO_FILENAME);
    $pageConfig['images'][$id] = $item;
    unlink($file);
}

// Save page config
file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/admin/pages/' . $_GET['page'] . '/pageConfig.php', '<?php $pageConfig = ' . var_export($pageConfig, true) . ';');

// Wait to make sure the file is written
sleep(3);

// Response
echo true;

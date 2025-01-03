<?php

// Check user login state
session_start();
if (!isset($_SESSION['login'])) {
    header('Location: /admin/login.php');
    die;
}

// Check if page is set
if (!isset($_GET['page'])) {
    header('Location: /admin/editor.php');
}

// Load page config
include_once $_SERVER['DOCUMENT_ROOT'] . '/admin/pages/' . $_GET['page'] . '/pageConfig.php';

// Check for actions
if (isset($_POST["pageName"])) $pageConfig['pageName'] = $_POST["pageName"];
if (isset($_POST["pageDate"])) $pageConfig['pageDate'] = $_POST["pageDate"];

// Write new page config
file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/admin/pages/' . $_GET['page'] . '/pageConfig.php', '<?php $pageConfig = ' . var_export($pageConfig, true) . ';');

// Wait for file to be written
sleep(3);

// Redirect to editor
header('Location: /admin/editor.php?page=' . $_GET['page']);

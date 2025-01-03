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

// Load page config
include_once $_SERVER['DOCUMENT_ROOT'] . '/admin/pages/' . $_GET['page'] . '/pageConfig.php';

// Check for actions
if (isset($_POST["pageName"])) $pageConfig['pageName'] = $_POST["pageName"];
if (isset($_POST["pageDate"])) $pageConfig['pageDate'] = $_POST["pageDate"];

// Write new page config
file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/admin/pages/' . $_GET['page'] . '/pageConfig.php', '<?php $pageConfig = ' . var_export($pageConfig, true) . ';');

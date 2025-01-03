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
if (isset($_GET["delete"])) {
    // Delete cluster
    unset($pageConfig['clusters'][$_GET["delete"]]);
    // Move all clusters with higher index one down
    for ($i = $_GET["delete"] + 1; $i < count($pageConfig['clusters']); $i++) {
        $pageConfig['clusters'][$i - 1] = $pageConfig['clusters'][$i];
    }
}

if (isset($_GET["moveUp"])) {
    // Move cluster up
    $temp = $pageConfig['clusters'][$_GET["moveUp"]];
    $pageConfig['clusters'][$_GET["moveUp"]] = $pageConfig['clusters'][$_GET["moveUp"] - 1];
    $pageConfig['clusters'][$_GET["moveUp"] - 1] = $temp;
}

if (isset($_GET["moveDown"])) {
    // Move cluster down
    $temp = $pageConfig['clusters'][$_GET["moveDown"]];
    $pageConfig['clusters'][$_GET["moveDown"]] = $pageConfig['clusters'][$_GET["moveDown"] + 1];
    $pageConfig['clusters'][$_GET["moveDown"] + 1] = $temp;
}

// Write new page config
file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/admin/pages/' . $_GET['page'] . '/pageConfig.php', '<?php $pageConfig = ' . var_export($pageConfig, true) . ';');

// Wait for file to be written
sleep(3);

// Redirect to editor
header('Location: /admin/editor.php?page=' . $_GET['page']);

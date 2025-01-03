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
    unset($pageConfig['clusters'][count($pageConfig['clusters']) - 1]);
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

if (isset($_GET["imgchange"])) {
    $cluster = explode("-", $_GET["imgchange"])[0];
    $image = explode("-", $_GET["imgchange"])[1];
    $newImage = explode("-", $_GET["imgchange"])[2];
    $pageConfig['clusters'][$cluster]["i" . $image] = $newImage;
}

if (isset($_GET["add"])) {
    $type = $_GET["add"];
    $img_num = strlen(str_replace('e', '', $type));
    $newCluster = array('type' => $type);
    for ($i = 1; $i <= $img_num; $i++) {
        $newCluster['i' . $i] = '';
    }
    $pageConfig['clusters'][] = $newCluster;
}

// Re-order clusters (fix indexes being out of order)
$tempArray = [];
for ($i = 0; $i < count($pageConfig['clusters']); $i++) $tempArray[] = $pageConfig['clusters'][$i];
$pageConfig['clusters'] = $tempArray;

// Write new page config
file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/admin/pages/' . $_GET['page'] . '/pageConfig.php', '<?php $pageConfig = ' . var_export($pageConfig, true) . ';');

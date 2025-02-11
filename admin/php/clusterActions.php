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

// Include config file
include_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';

// DB connection
$con = mysqli_connect($CONFIG['db']['host'], $CONFIG['db']['user'], $CONFIG['db']['password'], $CONFIG['db']['database']);
if (mysqli_connect_errno()) {
    echo 'Failed to connect to MySQL: ' . mysqli_connect_error();
    die;
}

$cluster = [];
if (isset($_GET["delete"]) || isset($_GET["moveUp"]) || isset($_GET["moveDown"])) {
    // query current cluster
    $clusterID = $_GET['delete'] || $_GET['moveUp'] || $_GET['moveDown'];
    $stmt = $con->prepare('SELECT id,page_id,type,position FROM cluster WHERE page_id = ? AND position = ? ORDER BY position ASC');
    $stmt->bind_param('ss', $_GET['page'], $clusterID);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($cluster['id'], $cluster['page_id'], $cluster['type'], $cluster['position']);
    $stmt->fetch();
    $stmt->close();
}

// Check for actions
if (isset($_GET["delete"])) {
    $stmt = $con->prepare('DELETE FROM cluster WHERE page_id = ? AND id = ?');
    $stmt->bind_param('ss', $_GET['page'], $cluster['id']);
    $stmt->execute();
    $stmt->close();
    $stmt = $con->prepare('UPDATE cluster SET position = position - 1 WHERE position > ? AND page_id = ?');
    $stmt->bind_param('ss', $cluster['position'], $_GET['page']);
    $stmt->execute();
    $stmt->close();
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

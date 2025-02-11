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
    $clusterID = $_GET['delete'] ?? $_GET['moveUp'] ?? $_GET['moveDown'];
    $stmt = $con->prepare('SELECT id,page_id,type,position FROM cluster WHERE page_id = ? AND id = ? ORDER BY position ASC');
    $stmt->bind_param('ss', $_GET['page'], $clusterID);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($cluster['id'], $cluster['page_id'], $cluster['type'], $cluster['position']);
    $stmt->fetch();
    $stmt->close();
}

$cluster_max;
if (isset($_GET['moveDown']) || isset($_GET['add'])) {
    // Query number of cluster
    $stmt = $con->prepare('SELECT MAX(position) FROM cluster WHERE page_id = ?');
    $stmt->bind_param('s', $_GET['page']);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($cluster_max);
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
    if ($cluster['position'] == 1) die;

    // Move cluster above to temp position
    $pos_current = $cluster['position'];
    $pos_above = $pos_current - 1;
    $stmt = $con->prepare('UPDATE cluster SET position = -1 WHERE position = ? AND page_id = ?');
    $stmt->bind_param('ss', $pos_above, $_GET['page']);
    $stmt->execute();
    $stmt->close();

    // Move current cluster up
    $stmt = $con->prepare('UPDATE cluster SET position = position - 1 WHERE position = ? AND page_id = ?');
    $stmt->bind_param('ss', $pos_current, $_GET['page']);
    $stmt->execute();
    $stmt->close();

    // Move cluster above down
    $stmt = $con->prepare('UPDATE cluster SET position = ? WHERE position = -1 AND page_id = ?');
    $stmt->bind_param('ss', $pos_current, $_GET['page']);
    $stmt->execute();
    $stmt->close();
}

if (isset($_GET["moveDown"])) {
    if ($cluster['position'] == $cluster_max) die;

    // Move cluster below to temp position
    $pos_current = $cluster['position'];
    $pos_below = $pos_current + 1;
    $stmt = $con->prepare('UPDATE cluster SET position = -1 WHERE position = ? AND page_id = ?');
    $stmt->bind_param('ss', $pos_below, $_GET['page']);
    $stmt->execute();
    $stmt->close();

    // Move current cluster down
    $stmt = $con->prepare('UPDATE cluster SET position = position + 1 WHERE position = ? AND page_id = ?');
    $stmt->bind_param('ss', $pos_current, $_GET['page']);
    $stmt->execute();
    $stmt->close();

    // Move cluster below up
    $stmt = $con->prepare('UPDATE cluster SET position = ? WHERE position = -1 AND page_id = ?');
    $stmt->bind_param('ss', $pos_current, $_GET['page']);
    $stmt->execute();
    $stmt->close();
}

if (isset($_GET["imgchange"])) {
    $cluster = explode("-", $_GET["imgchange"])[0];
    $image = explode("-", $_GET["imgchange"])[1];
    $newImage = explode("-", $_GET["imgchange"])[2];
    $pageConfig['clusters'][$cluster]["i" . $image] = $newImage;
}

if (isset($_GET["add"])) {
    $type = $_GET["add"];
    $id = bin2hex(random_bytes(4));
    $pos_new = $cluster_max + 1;
    $stmt = $con->prepare('INSERT INTO cluster (id, page_id, type, position) VALUES (?, ?, ?, ?)');
    $stmt->bind_param('sssi', $id, $_GET['page'], $type, $pos_new);
    $stmt->execute();
    $stmt->close();
}

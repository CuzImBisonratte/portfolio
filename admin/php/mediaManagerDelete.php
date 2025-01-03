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

// Check if img is set
if (!isset($_GET['img'])) {
    die;
}

// Delete images
foreach (glob($_SERVER['DOCUMENT_ROOT'] . '/admin/pages/' . $_GET['page'] . '/images/' . $_GET['img'] . '.*') as $file) {
    unlink($file);
}

// Delete image from page config
unset($pageConfig['images'][$_GET['img']]);

// Delete image from page config clusters
foreach ($pageConfig['clusters'] as $clusterKey => $cluster) {
    foreach ($cluster as $imageKey => $image) {
        if ($image == $_GET['img']) {
            $pageConfig['clusters'][$clusterKey][$imageKey] = '';
        }
    }
}
// Write new page config
file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/admin/pages/' . $_GET['page'] . '/pageConfig.php', '<?php $pageConfig = ' . var_export($pageConfig, true) . ';');

// Echo success
echo true;

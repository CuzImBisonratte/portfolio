<?php

// Check user login state
session_start();
if (!isset($_SESSION['login'])) {
    header('Location: /admin/login.php');
    die;
}

// Include config file
include_once $_SERVER['DOCUMENT_ROOT'] . '/admin/php/config.php';

// Check if old password is correct
if (!password_verify($_POST['oldPass'], $CONFIG['password'])) {
    echo 'Old password is incorrect<br><a href="/admin/settings.php">Back to settings</a>';
    die;
}

// Check if new password is two times the same
if ($_POST['newPass'] !== $_POST['newPass2']) {
    echo 'New passwords do not match<br><a href="/admin/settings.php">Back to settings</a>';
    die;
}

// Check if new password is too short
if (strlen($_POST['newPass']) < 8) {
    echo 'New password is too short - minimum 8 characters<br><a href="/admin/settings.php">Back to settings</a>';
    die;
}

// Set new password
$CONFIG['password'] = password_hash($_POST['newPass'], PASSWORD_DEFAULT);

// Write new password to config file
file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/admin/php/config.php', "<?php\n\n\$CONFIG = " . var_export($CONFIG, true) . ";\n");

// Destroy session
session_destroy();
header('Location: /admin/login.php');

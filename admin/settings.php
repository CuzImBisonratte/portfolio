<?php

// Check user login state
session_start();
if (!isset($_SESSION['login'])) {
    header('Location: /admin/login.php');
    die;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="/admin/css/admin.css">
    <link rel="stylesheet" href="/admin/css/settings.css">
    <link rel="stylesheet" href="/css/fonts.css">
    <link rel="stylesheet" href="/css/base.css">
    <link rel="icon" href="/res/img/favicon.ico" type="image/x-icon" />
    <link rel="shortcut icon" href="/res/img/favicon.ico" type="image/x-icon" />
</head>

<body>
    <div class="nav">
        <div class="nav-left">
            <a href="/admin/">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                </svg>
            </a>
        </div>
        <div class="nav-center">
            <a href="/">
                <img src="/res/img/logo.webp" alt="" />
            </a>
        </div>
        <div class="nav-right">
            <a href="/admin/logout.php">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 9V5.25A2.25 2.25 0 0 1 10.5 3h6a2.25 2.25 0 0 1 2.25 2.25v13.5A2.25 2.25 0 0 1 16.5 21h-6a2.25 2.25 0 0 1-2.25-2.25V15M12 9l3 3m0 0-3 3m3-3H2.25" />
                </svg>
            </a>
        </div>
    </div>
    <main>
        <div>
            <h1>Settings</h1>
            <div class="settings-panel">
                <h2>Change Password</h2>
                <form class="quartered-form" action="/admin/php/newPass.php" method="post">
                    <input type="password" name="oldPass" placeholder="Old Password" required>
                    <input type="password" name="newPass" placeholder="New Password" required>
                    <input type="password" name="newPass2" placeholder="Repeat New Password" required>
                    <input type="submit" value="Change Password">
                </form>
            </div>
            <div class="settings-panel">
                <h2>Change Logo</h2>
                <form class="halfed-form" action="/admin/php/generateLogo.php" method="post" enctype="multipart/form-data">
                    <input type="file" id="logo-choose" name="logo" accept="image/*" required>
                    <label for="logo-choose" class="logo-upload">Choose an Image</label>
                    <input type="submit" value="Change Logo">
                </form>
            </div>
        </div>
    </main>
    <div class=" footer">
    </div>
</body>

</html>
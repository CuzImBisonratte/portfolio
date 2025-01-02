<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title>Portfolio Login</title>
	<link rel="stylesheet" href="/admin/css/admin.css" />
	<link rel="stylesheet" href="/admin/css/login.css" />
	<link rel="stylesheet" href="/css/fonts.css" />
	<link rel="stylesheet" href="/css/base.css" />
	<link rel="icon" href="/res/img/favicon.ico" type="image/x-icon" />
	<link rel="shortcut icon" href="/res/img/favicon.ico" type="image/x-icon" />
</head>

<body>
	<div class="nav">
		<div class="nav-left"></div>
		<div class="nav-center">
			<a href="/">
				<img src="/res/img/logo.webp" alt="" />
			</a>
		</div>
	</div>
	<main>
		<div>
			<h1>Login</h1>
			<form action="/admin/login.php" method="post">
				<input type="password" name="password" placeholder="Password" required />
				<input type="submit" value="Login" />
			</form>
		</div>

		<span> This is a management page. Please enter the password to login. </span>
	</main>
	<div class="footer"></div>
</body>

</html>


<?php

// Check if user is already logged in
session_start();
if (isset($_SESSION['login'])) {
	header('Location: /admin/');
	die;
}

// Check if login form is submitted
if (!isset($_POST['password'])) {
	die;
}

// Check if password is correct
include_once $_SERVER['DOCUMENT_ROOT'] . '/admin/php/config.php';
if (password_verify($_POST['password'], $CONFIG['password'])) {
	$_SESSION['login'] = true;
	header('Location: /admin/');
} else {
	echo "Password is incorrect";
}

?>
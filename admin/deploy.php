<?php

// Check user login state
session_start();
if (!isset($_SESSION['login'])) {
    header('Location: /admin/login.php');
    die;
}

// Load config
include_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';

// DB connection
$con = new mysqli($CONFIG['db']['host'], $CONFIG['db']['user'], $CONFIG['db']['password'], $CONFIG['db']['database']);
if (mysqli_errno($con)) die('Failed to connect to database');

// Get all pages
$portfolios = $con->query('SELECT id, title, subtitle, cover_image_id FROM page')->fetch_all(MYSQLI_ASSOC);

// Check space-seperated list of pages to deploy by default
if (isset($_GET['deploy'])) $active_pages = explode(' ', html_entity_decode($_GET['deploy']));
else $active_pages = [];

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portfolio Admin</title>
    <link rel="stylesheet" href="/admin/css/admin.css">
    <link rel="stylesheet" href="/admin/css/deploy.css">
    <link rel="stylesheet" href="/css/fonts.css">
    <link rel="stylesheet" href="/css/base.css">
    <link rel="icon" href="/res/img/favicon.ico" type="image/x-icon" />
    <link rel="shortcut icon" href="/res/img/favicon.ico" type="image/x-icon" />
</head>

<body>
    <div class="nav">
        <div class="nav-left">
            <a href="javascript:history.back()">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
            </a>
        </div>
        <div class="nav-center">
            <a href="/">
                <img src="/res/img/logo.webp" alt="" />
            </a>
        </div>
        <div class="nav-right">
            <a href="/admin/">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                </svg>
            </a>
            <a href="/admin/settings.php">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                </svg>
            </a>
            <a href="/admin/logout.php">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 9V5.25A2.25 2.25 0 0 1 10.5 3h6a2.25 2.25 0 0 1 2.25 2.25v13.5A2.25 2.25 0 0 1 16.5 21h-6a2.25 2.25 0 0 1-2.25-2.25V15M12 9l3 3m0 0-3 3m3-3H2.25" />
                </svg>
            </a>
        </div>
    </div>
    <main>
        <form action="/admin/php/deploy.php" method="post">
            <div class="page_list">
                <h2>Pages to deploy</h2>
                <?php foreach ($portfolios as $portfolio) : ?>
                    <!-- <?php var_dump($portfolio); ?> -->
                    <input name="pages[]" type="checkbox" id="<?= $portfolio['id'] ?>" value="<?php echo $portfolio['id']; ?>" <?php if (in_array($portfolio['id'], $active_pages)) echo 'checked'; ?>>
                    <label for="<?= $portfolio['id'] ?>"><?php echo $portfolio['title']; ?></label>
                    <br>
                <?php endforeach; ?>
            </div>
            <div class="deploy_settings">
                <h2>Deployment settings</h2>
                <div class="setting">
                    <h3>Watermark</h3>
                    Add logo to the images as a watermark?
                    <input type="checkbox" name="watermark" id="watermark" checked onchange="document.getElementById('watermark_position').style.display = this.checked ? 'block' : 'none';">
                    <div id="watermark_position">
                        <hr>
                        <input type="radio" name="watermark_position" id="top-left" value="top-left">
                        <label for="top-left">Top left</label>
                        <input type="radio" name="watermark_position" id="top-right" value="top-right">
                        <label for="top-right">Top right</label>
                        <input type="radio" name="watermark_position" id="bottom-left" value="bottom-left" checked>
                        <label for="bottom-left">Bottom left</label>
                        <input type="radio" name="watermark_position" id="bottom-right" value="bottom-right">
                        <label for="bottom-right">Bottom right</label>
                    </div>
                </div>
            </div>
            <div class="deploy_button">
                <a href="javascript:deploy();" id="deploy_button">
                    Deploy page(s) now
                    <svg id="deploy_rocket" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.59 14.37a6 6 0 0 1-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 0 0 6.16-12.12A14.98 14.98 0 0 0 9.631 8.41m5.96 5.96a14.926 14.926 0 0 1-5.841 2.58m-.119-8.54a6 6 0 0 0-7.381 5.84h4.8m2.581-5.84a14.927 14.927 0 0 0-2.58 5.84m2.699 2.7c-.103.021-.207.041-.311.06a15.09 15.09 0 0 1-2.448-2.448 14.9 14.9 0 0 1 .06-.312m-2.24 2.39a4.493 4.493 0 0 0-1.757 4.306 4.493 4.493 0 0 0 4.306-1.758M16.5 9a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z" />
                    </svg>
                </a>
            </div>
            <input type="submit" value="Deploy" style="display: none;" id="deploy_submit">
        </form>
    </main>
    <div class="overlay deploy-spinner" id="deploy-spinner">
        <h1>
            Page is being processed,<br>
            This may take a while...
        </h1>
        <div class="spinner-square">
            <div class="square-1 square"></div>
            <div class="square-2 square"></div>
            <div class="square-3 square"></div>
            <div class="square-4 square"></div>
        </div>
    </div>
    <div class="footer"></div>
    <script>
        function deploy() {
            document.getElementById('deploy-spinner').style.display = 'grid';
            document.getElementById('deploy_submit').click();
        }
    </script>
</body>

</html>
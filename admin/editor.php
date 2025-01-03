<?php

// Check user login state
session_start();
if (!isset($_SESSION['login'])) {
    header('Location: /admin/login.php');
    die;
}

// Check if page exists
if (!isset($_GET['page'])) {
    header('Location: /admin/');
    die;
}
if (!file_exists('pages/' . $_GET['page'] . '/pageConfig.php')) {
    header('Location: /admin/');
    die;
}

// Load page config
require_once('pages/' . $_GET['page'] . '/pageConfig.php');

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portfolio Editor</title>
    <link rel="stylesheet" href="/admin/css/admin.css">
    <link rel="stylesheet" href="/admin/css/editor.css">
    <link rel="stylesheet" href="/css/portfolio.css">
    <link rel="stylesheet" href="/css/fonts.css">
    <link rel="stylesheet" href="/css/base.css">
    <link rel="icon" href="/res/img/favicon.ico" type="image/x-icon" />
    <link rel="shortcut icon" href="/res/img/favicon.ico" type="image/x-icon" />
</head>

<body>
    <div class="nav">
        <div class="nav-left">
            <a href="javascript:mediaManager.open()">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                </svg>
            </a>
            <a href="javascript:publish()">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.59 14.37a6 6 0 0 1-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 0 0 6.16-12.12A14.98 14.98 0 0 0 9.631 8.41m5.96 5.96a14.926 14.926 0 0 1-5.841 2.58m-.119-8.54a6 6 0 0 0-7.381 5.84h4.8m2.581-5.84a14.927 14.927 0 0 0-2.58 5.84m2.699 2.7c-.103.021-.207.041-.311.06a15.09 15.09 0 0 1-2.448-2.448 14.9 14.9 0 0 1 .06-.312m-2.24 2.39a4.493 4.493 0 0 0-1.757 4.306 4.493 4.493 0 0 0 4.306-1.758M16.5 9a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z" />
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
    <div class="wysiwyg-editor">
        <form class="portfolio-title" action="/admin/php/changePageData.php?page=<?= htmlspecialchars($_GET["page"]) ?>" method="post">
            <input class="portfolio-name" type="text" name="pageName" value="<?= isset($pageConfig['pageName']) ? $pageConfig['pageName'] : 'No name set!' ?>" onblur="this.form.submit()">
            <input class="portfolio-date" type="text" name="pageDate" value="<?= isset($pageConfig['pageDate']) ? $pageConfig['pageDate'] : 'No date set!' ?>" onblur="this.form.submit()">
            <!-- Hidden submit button, so enter key can be used to submit form -->
            <input type="submit" style="display: none">
        </form>
        <?php

        // Loop through all image clusters in pageconfig
        foreach ($pageConfig['clusters'] as $index => $cluster) {
            $isFirst = $index == 0 ? 'firstUpDisabled' : '';
            $isLast = $index == count($pageConfig['clusters']) - 1 ? 'lastDownDisabled' : '';
            $upJS = $index == 0 ? '' : 'editor.moveImageClusterUp(' . $index . ')';
            $downJS = $index == count($pageConfig['clusters']) - 1 ? '' : 'editor.moveImageClusterDown(' . $index . ')';
            echo <<<EOL
            <div class="image-cluster-container">
            <div class="image-cluster-actions">
                <a href="javascript:editor.deleteImageCluster($index)">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                    </svg>
                </a>
                <a href="javascript:$upJS" class="$isFirst">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 15.75 7.5-7.5 7.5 7.5" />
                    </svg>
                </a>
                <a href="javascript:$downJS" class="$isLast">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                    </svg>
                </a>
            </div>
            EOL;
            echo '<div class="image-cluster ' . $cluster['type'] . '">';
            for ($i = 1; $i <= count($cluster); $i++) {
                if (isset($cluster['i' . $i])) {
                    $img_src = $pageConfig['images'][$cluster['i' . $i]]['src'];
                    echo '<img src="/admin/pages/' . $_GET['page'] . '/images/' . $img_src . '" alt="" class="i' . $i . '" />';
                }
            }
            echo '</div>';
            echo '</div>';
        }

        ?>
    </div>
    <div class="overlay mediamanager" id="mediamanager">
        <div>
            <nav>
                <div class="nav-left">
                    <form action="/admin/php/mediamanagerupload.php?page=<?= htmlspecialchars($_GET["page"]) ?>" method="post" enctype="multipart/form-data">
                        <input type="file" name="files[]" id="file" accept="image/*" style="display: none" multiple onchange="this.form.submit()">
                        <label for="file">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
                            </svg>
                        </label>
                    </form>
                </div>
                <div class="nav-center">
                    <h2>Media Manager</h2>
                </div>
                <div class="nav-right">
                    <a href="javascript:mediaManager.close()">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </a>
                </div>
            </nav>
            <main>
                <?php

                // Loop through all images in pageconfig
                foreach ($pageConfig['images'] as $image) {
                    echo '<div class="mediaManagerImageContainer" id="' . $image['id'] . '">';
                    echo '<img src="/admin/pages/' . $_GET['page'] . '/images/' . $image['src'] . '" alt="">';
                    echo '<a class="image-delete" href="javascript:mediaManager.deleteImage(\'' . $image['id'] . '\')">';
                    echo '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">';
                    echo '<path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />';
                    echo '</svg>';
                    echo '</a>';
                    echo '<a class="image-info" href="javascript:mediaManager.editMetadata(\'' . $image['id'] . '\')">';
                    echo '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">';
                    echo '<path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />';
                    echo '</svg>';
                    echo '</a>';
                    echo '</div>';
                }

                ?>
            </main>
        </div>
    </div>
    <div class="footer"></div>
    <script src="/admin/js/editor.js"></script>
    <script src="/admin/js/mediamanager.js"></script>
    <?php if (isset($_GET['mediamanager'])) echo '<script>mediaManager.open()</script>'; ?>
    <?php echo "<script>const PAGE = '" . $_GET['page'] . "'</script>"; ?>
</body>

</html>
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

// Load page data
$page = [];
$clusters = [];
$images = [];
$stmt = $con->prepare("SELECT title, subtitle, id, cover_image_id FROM page WHERE id = ?");
$stmt->bind_param('s', $_GET['page']);
$stmt->execute();
$stmt->bind_result($page['title'], $page['subtitle'], $page['id'], $page['cover_image_id']); #
$stmt->fetch();
if ($page['id'] == null) {
    $stmt->close();
    header('Location: /admin/');
    die;
}
$stmt->close();
$stmt = $con->prepare("SELECT id, type, position FROM cluster WHERE page_id = ?");
$stmt->bind_param('s', $page['id']);
$stmt->execute();
$stmt->bind_result($cluster['id'], $cluster['type'], $cluster['position']);
while ($stmt->fetch()) {
    $clusters[$cluster['id']] = json_decode(json_encode($cluster), true);
}
$stmt->close();
$stmt = $con->prepare("SELECT id, alt, camera, artist, time, fileformat FROM image WHERE page_id = ?");
$stmt->bind_param('s', $page['id']);
$stmt->execute();
$stmt->bind_result($image['id'], $image['alt'], $image['camera'], $image['artist'], $image['time'], $image['fileformat']);
while ($stmt->fetch()) {
    $images[$image['id']] = json_decode(json_encode($image), true);
}
$stmt->close();
$stmt = $con->prepare("SELECT cluster_id, image_id, position FROM view WHERE cluster_id = ?");
foreach ($clusters as $cluster) {
    $stmt->bind_param('s', $cluster['id']);
    $stmt->execute();
    $stmt->bind_result($view['cluster_id'], $view['image_id'], $view['position']);
    while ($stmt->fetch()) {
        // Integrate views into clusters
        $clusters[$view['cluster_id']]['i' . $view['position']] = $view['image_id'];
    }
}
$stmt->close();

// Fix sorting
array_multisort(array_column($clusters, 'position'), SORT_ASC, $clusters);

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
            <a href="javascript:editor.deploy()">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.59 14.37a6 6 0 0 1-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 0 0 6.16-12.12A14.98 14.98 0 0 0 9.631 8.41m5.96 5.96a14.926 14.926 0 0 1-5.841 2.58m-.119-8.54a6 6 0 0 0-7.381 5.84h4.8m2.581-5.84a14.927 14.927 0 0 0-2.58 5.84m2.699 2.7c-.103.021-.207.041-.311.06a15.09 15.09 0 0 1-2.448-2.448 14.9 14.9 0 0 1 .06-.312m-2.24 2.39a4.493 4.493 0 0 0-1.757 4.306 4.493 4.493 0 0 0 4.306-1.758M16.5 9a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z" />
                </svg>
            </a>
            <a href="javascript:editor.editPage()">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                </svg>
            </a>
            <div class="rule" style="height: 2rem; width: 1px; background-color: #888; margin: 0 0.25rem;transform: translateY(50%);"></div>
            <a href="javascript:mediaManager.open()">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                </svg>
            </a>
            <a href="javascript:editor.addCluster()">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
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
        <form class="portfolio-metadata" action="javascript:changePageData()">
            <input class="portfolio-title" type="text" name="title" value="<?= isset($page['title']) ? $page['title'] : 'No title set!' ?>" onblur="this.form.submit()">
            <input class="portfolio-subtitle" type="text" name="subtitle" value="<?= isset($page['subtitle']) ? $page['subtitle'] : 'No subtitle set!' ?>" onblur="this.form.submit()">
        </form>
        <?php

        // Loop through all image clusters in pageconfig
        foreach ($clusters as $index => $cluster) {
            $isFirst = $cluster['position'] == 1 ? 'firstUpDisabled' : '';
            $isLast = $cluster['position'] == count($clusters) ? 'lastDownDisabled' : '';
            $upJS = $cluster['position'] == 1 ? '' : 'editor.moveImageClusterUp(\'' . $index . '\')';
            $downJS = $cluster['position'] == count($clusters) ? '' : 'editor.moveImageClusterDown(\'' . $index . '\')';
            echo <<<EOL
            <div class="image-cluster-container" id="cluster$index">
            <div class="image-cluster-actions">
                <a href="javascript:editor.deleteImageCluster('$index')" class="delete-button">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                    </svg>
                </a>
                <a href="javascript:$upJS" class="$isFirst up-button">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 15.75 7.5-7.5 7.5 7.5" />
                    </svg>
                </a>
                <a href="javascript:$downJS" class="$isLast down-button">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                    </svg>
                </a>
            </div>
            EOL;
            echo '<div class="image-cluster ' . $cluster['type'] . '">';
            for ($i = 1; $i <= strlen(str_replace('e', '', $cluster['type'])); $i++) {
                $img_src = $cluster['i' . $i];
                $img_path = $img_src ? '/admin/php/getImage.php?page=' . $_GET['page'] . '&img=' . $img_src . '.webp' : '/res/img/placeholder.webp';
                echo '<img src="' . $img_path . '" alt="" class="i' . $i . '" onclick="editor.chooseImage(\'' . $index . '-' . $i . '\')">';
            }
            echo '</div>';
            echo '</div>';
        }
        ?>
    </div>
    <div class="overlay addCluster" id="addCluster">
        <div>
            <div onclick="editor.addClusterElement('l')">
                <div class="l">
                    <div class="addClusterPlaceholderImage i1"></div>
                </div>
            </div>
            <div onclick="editor.addClusterElement('le')">
                <div class="le">
                    <div class="addClusterPlaceholderImage i1"></div>
                </div>
            </div>
            <div onclick="editor.addClusterElement('lee')">
                <div class="lee">
                    <div class="addClusterPlaceholderImage i1"></div>
                </div>
            </div>
            <div onclick="editor.addClusterElement('p')">
                <div class="p">
                    <div class="addClusterPlaceholderImage i1"></div>
                </div>
            </div>
            <div onclick="editor.addClusterElement('s')">
                <div class="s">
                    <div class="addClusterPlaceholderImage i1"></div>
                </div>
            </div>
            <div onclick="editor.addClusterElement('ll')">
                <div class="ll">
                    <div class="addClusterPlaceholderImage i1"></div>
                    <div class="addClusterPlaceholderImage i2"></div>
                </div>
            </div>
            <div onclick="editor.addClusterElement('pp')">
                <div class="pp">
                    <div class="addClusterPlaceholderImage i1"></div>
                    <div class="addClusterPlaceholderImage i2"></div>
                </div>
            </div>
            <div onclick="editor.addClusterElement('ss')">
                <div class="ss">
                    <div class="addClusterPlaceholderImage i1"></div>
                    <div class="addClusterPlaceholderImage i2"></div>
                </div>
            </div>
            <div onclick="editor.addClusterElement('sp')">
                <div class="sp">
                    <div class="addClusterPlaceholderImage i1"></div>
                    <div class="addClusterPlaceholderImage i2"></div>
                </div>
            </div>
            <div onclick="editor.addClusterElement('ps')">
                <div class="ps">
                    <div class="addClusterPlaceholderImage i1"></div>
                    <div class="addClusterPlaceholderImage i2"></div>
                </div>
            </div>
            <div onclick="editor.addClusterElement('lll')">
                <div class="lll">
                    <div class="addClusterPlaceholderImage i1"></div>
                    <div class="addClusterPlaceholderImage i2"></div>
                    <div class="addClusterPlaceholderImage i3"></div>
                </div>
            </div>
            <div onclick="editor.addClusterElement('ppp')">
                <div class="ppp">
                    <div class="addClusterPlaceholderImage i1"></div>
                    <div class="addClusterPlaceholderImage i2"></div>
                    <div class="addClusterPlaceholderImage i3"></div>
                </div>
            </div>
            <div onclick="editor.addClusterElement('sss')">
                <div class="sss">
                    <div class="addClusterPlaceholderImage i1"></div>
                    <div class="addClusterPlaceholderImage i2"></div>
                    <div class="addClusterPlaceholderImage i3"></div>
                </div>
            </div>
            <div onclick="editor.addClusterElement('pll')">
                <div class="pll">
                    <div class="addClusterPlaceholderImage i1"></div>
                    <div class="addClusterPlaceholderImage i2"></div>
                    <div class="addClusterPlaceholderImage i3"></div>
                </div>
            </div>
            <div onclick="editor.addClusterElement('llp')">
                <div class="llp">
                    <div class="addClusterPlaceholderImage i1"></div>
                    <div class="addClusterPlaceholderImage i2"></div>
                    <div class="addClusterPlaceholderImage i3"></div>
                </div>
            </div>
            <div onclick="editor.addClusterElement('ssss')">
                <div class="ssss">
                    <div class="addClusterPlaceholderImage i1"></div>
                    <div class="addClusterPlaceholderImage i2"></div>
                    <div class="addClusterPlaceholderImage i3"></div>
                    <div class="addClusterPlaceholderImage i4"></div>
                </div>
            </div>
            <div onclick="editor.addClusterElement('plpl')">
                <div class="plpl">
                    <div class="addClusterPlaceholderImage i1"></div>
                    <div class="addClusterPlaceholderImage i2"></div>
                    <div class="addClusterPlaceholderImage i3"></div>
                    <div class="addClusterPlaceholderImage i4"></div>
                </div>
            </div>
            <div onclick="editor.addClusterElement('lplp')">
                <div class="lplp">
                    <div class="addClusterPlaceholderImage i1"></div>
                    <div class="addClusterPlaceholderImage i2"></div>
                    <div class="addClusterPlaceholderImage i3"></div>
                    <div class="addClusterPlaceholderImage i4"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="overlay mediamanager" id="mediamanager">
        <div>
            <nav>
                <div class="nav-left">
                    <form action="" method="post" enctype="multipart/form-data">
                        <input type="file" name="files[]" id="file" accept="image/*" style="display: none" multiple onchange="mediaManager.submitUpload(this);" max="<?= ini_get('max_file_uploads'); ?>">
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
                foreach ($images as $index => $image) {
                    echo '<div class="mediaManagerImageContainer" id="' . $index . '">';
                    echo '<img src="/admin/php/getImage.php?page=' . $_GET['page'] . '&img=' . $index . ".webp" . '" alt="" onclick="mediaManager.click(\'' . $index . '\')">';
                    echo '<a class="image-delete" href="javascript:mediaManager.deleteImage(\'' . $index . '\')">';
                    echo '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">';
                    echo '<path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />';
                    echo '</svg>';
                    echo '</a>';
                    echo '<a class="image-info" href="javascript:mediaManager.editMetadata(\'' . $index . '\')">';
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
    <div class="overlay upload-spinner" id="upload-spinner">
        <h1>
            Images are Uploading,<br>
            Please Wait...
        </h1>
        <div class="spinner-square">
            <div class="square-1 square"></div>
            <div class="square-2 square"></div>
            <div class="square-3 square"></div>
            <div class="square-4 square"></div>
        </div>
    </div>
    <div class="overlay editPage" id="editPage">
        <div>
            <nav>
                <div class="nav-left"></div>
                <div class="nav-center">
                    <h2>Page Metadata Editor</h2>
                </div>
                <div class="nav-right">
                    <a href="javascript:editor.closeEditPage()">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </a>
                </div>
            </nav>
            <main>
                <div>
                    <form action="/admin/php/changePageUrl.php?oldURL=<?= $page['id'] ?>" method="post" class="pagelink_form">
                        <h2 class="pagelink_title">Page link</h2>
                        <div class="pagelink_prefix">
                            https://<?php echo $_SERVER['HTTP_HOST'] ?>/
                        </div>
                        <input type="text" name="pageLink" class="pagelink_input" value="<?= $page['id'] ?>" pattern="[a-zA-Z0-9\-]+" title="Only letters, numbers, and hyphens are allowed">
                        <input type="submit" value="Change Link" class="pagelink_submit">
                    </form>
                    <form action="/admin/php/changeCoverImage.php?oldURL=<?= $page['id'] ?>" method="post" class="coverimage_form">
                        <h2 class="coverimage_title">Cover image</h2>
                        <div class="coverimage_preview">
                            <img src="/admin/php/getImage.php?page=<?= $page['id'] ?>&img=<?= $page['cover_image_id'] ?>.webp" alt="No cover image set for this page" id="coverImagePreview">
                        </div>
                        <label class="coverimage_pick" onclick="editor.chooseCover()">Choose another image</label>
                    </form>
                    <form class="pagedelete_form" action="javascript:editor.deletePage()">
                        <h2 class="pagedelete_title">Delete this page</h2>
                        <input type="submit" value="Delete page" class="pagedelete_submit">
                    </form>
                </div>
            </main>
        </div>
    </div>
    <div class="footer"></div>
    <script src="/admin/js/editor.js"></script>
    <script src="/admin/js/mediamanager.js"></script>
    <?php if (isset($_GET['mediamanager'])) echo '<script>mediaManager.open()</script>'; ?>
    <?php if (isset($_GET['edit'])) echo '<script>editor.editPage()</script>'; ?>
    <script>
        <?php
        echo "const PAGE = '" . $_GET['page'] . "';";
        echo "const MAX_UPLOAD_SIZE = " . min(convertToBytes(ini_get('upload_max_filesize')), convertToBytes(ini_get('post_max_size'))) . ";";
        echo "const MAX_UPLOAD_FILES = " . ini_get('max_file_uploads') . ";";
        echo "const IMAGES = " . json_encode($images) . ";";

        function convertToBytes($size)
        {
            $unit = strtolower(substr($size, -1));
            $size = (int) $size;
            if ($unit == 'k') $size *= 1024;
            if ($unit == 'm') $size *= 1024 * 1024;
            if ($unit == 'g') $size *= 1024 * 1024 * 1024;
            return $size;
        }
        ?>
    </script>
    <style>
        .waiting *,
        .waiting img {
            cursor: wait !important;
        }
    </style>
</body>

</html>
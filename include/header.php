<?php
if (!isset($baseUrl)) {
    $baseUrl = '';
}
$pageTitle = isset($pageTitle) ? $pageTitle . ' — ' : '';

// Path absolut ke folder project agar CSS selalu terbaca dari semua halaman
$scriptPath = str_replace('\\', '/', $_SERVER['SCRIPT_NAME']);
$projectDir = dirname($scriptPath);
if (basename($projectDir) === 'pages') {
    $projectDir = dirname($projectDir);
}
$cssHref = (strlen($projectDir) > 1 ? $projectDir : '') . '/assets/app.css';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?>Inventaris Sepatu</title>
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous">
    <?php
$cssFile = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'app.css';
$cssVer = file_exists($cssFile) ? filemtime($cssFile) : time();
?>
    <link rel="stylesheet" type="text/css" href="<?= htmlspecialchars($cssHref) ?>?v=<?= (int) $cssVer ?>">
</head>
<body class="app-body">

<header class="app-topbar">
    <div class="app-topbar-inner">
        <a class="app-topbar-brand" href="<?= $baseUrl ?>index.php">
            <span class="app-topbar-icon">
                <i class="fa-solid fa-shoe-prints"></i>
            </span>
            <span class="app-topbar-brand-text">
                <span class="app-topbar-title">Inventaris Sepatu</span>
                <span class="app-topbar-sub">Sistem Pendataan Barang</span>
            </span>
        </a>
        <div class="app-topbar-right">
            <div class="app-topbar-date">
                <i class="fa-regular fa-calendar"></i>
                <span><?= date('d/m/Y') ?></span>
            </div>
        </div>
    </div>
</header>

<div class="app-layout">
    <aside class="app-sidebar collapse collapse-horizontal" id="sidebarCollapse">
        <?php include __DIR__ . '/menu.php'; ?>
    </aside>
    <div class="app-main-wrap">
        <button class="app-sidebar-toggle d-lg-none" type="button" aria-label="Buka menu" data-bs-toggle="collapse" data-bs-target="#sidebarCollapse">
            <span></span><span></span><span></span>
        </button>
        <main class="app-main">

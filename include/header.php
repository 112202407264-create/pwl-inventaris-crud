<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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

<?php
$hour = (int) date('H');
if ($hour >= 5 && $hour < 12) {
    $greeting = 'Selamat pagi';
} elseif ($hour >= 12 && $hour < 15) {
    $greeting = 'Selamat siang';
} elseif ($hour >= 15 && $hour < 18) {
    $greeting = 'Selamat sore';
} else {
    $greeting = 'Selamat malam';
}
?>
<header class="app-topbar shadow-sm">
    <div class="app-topbar-inner container-fluid">
        <div class="d-flex align-items-center gap-3">
            <a class="app-topbar-brand" href="<?= $baseUrl ?>index.php">
                <span class="app-topbar-icon">
                    <i class="fa-solid fa-shoe-prints"></i>
                </span>
                <span class="app-topbar-brand-text">
                    <span class="app-topbar-title">Inventaris Sepatu</span>
                    <span class="app-topbar-sub">Sistem Pendataan Barang</span>
                </span>
            </a>
            <div class="d-none d-md-flex flex-column small text-light-50">
                <span><?= $greeting; ?>, selamat bekerja!</span>
                <span>Pastikan data barang selalu terbarui.</span>
            </div>
        </div>

        <div class="app-topbar-right d-flex align-items-center gap-3">
            <form class="d-none d-lg-flex align-items-center" role="search" action="<?= $baseUrl ?>pages/data_barang.php" method="get">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-transparent border-end-0">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </span>
                    <input type="text" class="form-control border-start-0" name="q" placeholder="Cari barang...">
                </div>
            </form>

            <div class="dropdown">
                <button class="btn btn-sm btn-outline-light d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="avatar rounded-circle bg-light text-primary d-inline-flex align-items-center justify-content-center" style="width:28px;height:28px;">
                        <i class="fa-solid fa-user"></i>
                    </span>
                    <span class="d-none d-sm-inline text-start">
                        <span class="d-block lh-1 small">
                            <?= isset($_SESSION['username']) && $_SESSION['username'] !== '' ? htmlspecialchars((string)$_SESSION['username']) : 'Administrator' ?>
                        </span>
                    </span>
                    <i class="fa-solid fa-chevron-down extra-small"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                    <li class="dropdown-header small text-muted">Akun</li>
                    <li><a class="dropdown-item" href="#"><i class="fa-solid fa-id-badge me-2"></i>Profil</a></li>
                    <li><a class="dropdown-item" href="#"><i class="fa-solid fa-gear me-2"></i>Pengaturan</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li>
                            <a class="dropdown-item text-danger" href="<?= $baseUrl ?>logout.php">
                                <i class="fa-solid fa-right-from-bracket me-2"></i>Keluar
                            </a>
                        </li>
                    <?php else: ?>
                        <li>
                            <a class="dropdown-item" href="<?= $baseUrl ?>login.php">
                                <i class="fa-solid fa-right-to-bracket me-2"></i>Masuk
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
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

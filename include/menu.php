<?php
$baseUrl = isset($baseUrl) ? $baseUrl : '';
?>
<div class="app-sidebar-inner">
    <nav class="app-nav-vertical">
        <a class="app-nav-item <?= (basename($_SERVER['PHP_SELF']) === 'dashboard.php') ? 'active' : '' ?>" href="<?= $baseUrl ?>pages/dashboard.php">
            <span class="app-nav-icon"><i class="fa-solid fa-house"></i></span>
            Dashboard
        </a>
        <a class="app-nav-item <?= (basename($_SERVER['PHP_SELF']) === 'index.php' || basename($_SERVER['PHP_SELF']) === 'data_barang.php') ? 'active' : '' ?>" href="<?= $baseUrl ?>index.php">
            <span class="app-nav-icon"><i class="fa-solid fa-boxes-stacked"></i></span>
            Data Barang
        </a>
    </nav>
</div>

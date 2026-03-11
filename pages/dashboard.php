<?php
require_once '../koneksi.php';
$baseUrl = '../';
$pageTitle = 'Dashboard';
include '../include/header.php';
?>

<div class="app-shell">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Dashboard</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-2">
                            Selamat datang di aplikasi <strong>Inventaris Sepatu</strong>.
                        </p>
                        <p class="text-muted mb-3">
                            Gunakan menu di atas untuk mengelola data sepatu. Dari <strong>Data Barang</strong> Anda bisa melihat, menambah, mengubah, dan menghapus stok sepatu.
                        </p>
                        <a href="<?= $baseUrl ?>index.php" class="btn btn-primary">Lihat Data Barang</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../include/footer.php'; ?>


<?php
require_once '../koneksi.php';

$barang = [];
$error  = '';

try {
    $stmt   = $pdo->query("SELECT * FROM barang ORDER BY kode_barang ASC");
    $barang = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = 'Gagal mengambil data: ' . $e->getMessage();
}

$baseUrl = '../';
$pageTitle = 'Data Barang';
include '../include/header.php';
?>

<div class="app-shell">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h3 class="mb-0 page-title">Data Sepatu</h3>
                <div class="subtle small">Manajemen stok sepatu di gudang/toko.</div>
            </div>
            <a href="../tambah.php" class="btn btn-success">+ Tambah Sepatu</a>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger" role="alert">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle mb-0">
                        <thead class="table-primary">
                        <tr>
                            <th>No</th>
                            <th>Kode</th>
                            <th>Nama Barang</th>
                            <th>Jumlah</th>
                            <th>Satuan</th>
                            <th>Lokasi</th>
                            <th>Tanggal Masuk</th>
                            <th width="150">Aksi</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (count($barang) === 0): ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted">Belum ada data sepatu.</td>
                            </tr>
                        <?php else: ?>
                            <?php $no = 1; foreach ($barang as $row): ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><?= htmlspecialchars($row['kode_barang']) ?></td>
                                    <td><?= htmlspecialchars($row['nama_barang']) ?></td>
                                    <td><?= htmlspecialchars($row['jumlah']) ?></td>
                                    <td><?= htmlspecialchars($row['satuan']) ?></td>
                                    <td><?= htmlspecialchars($row['lokasi']) ?></td>
                                    <td><?= htmlspecialchars($row['tanggal_masuk'] ?: '-') ?></td>
                                    <td>
                                        <a href="../edit.php?id=<?= urlencode($row['id']) ?>" class="btn btn-sm btn-warning">Edit</a>
                                        <a href="../hapus.php?id=<?= urlencode($row['id']) ?>"
                                           class="btn btn-sm btn-danger"
                                           onclick="return confirm('Yakin ingin menghapus data ini?');">
                                            Hapus
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../include/footer.php'; ?>


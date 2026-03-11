<?php
require_once 'koneksi.php';

// Proses simpan data jika form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kode_barang   = $_POST['kode_barang'] ?? '';
    $nama_barang   = $_POST['nama_barang'] ?? '';
    $jumlah        = $_POST['jumlah'] ?? '';
    $satuan        = $_POST['satuan'] ?? '';
    $lokasi        = $_POST['lokasi'] ?? '';
    $tanggal_masuk = $_POST['tanggal_masuk'] ?? '';

    // Validasi sederhana
    if ($kode_barang === '' || $nama_barang === '' || $jumlah === '') {
        $error = 'Kode barang, nama barang, dan jumlah wajib diisi.';
    } else {
        $jumlah_int    = (int)$jumlah;
        $tanggal_masuk = $tanggal_masuk ?: null;

        try {
            if ($tanggal_masuk) {
                $stmt = $pdo->prepare(
                    "INSERT INTO barang (kode_barang, nama_barang, jumlah, satuan, lokasi, tanggal_masuk)
                     VALUES (:kode, :nama, :jumlah, :satuan, :lokasi, :tanggal)"
                );
                $stmt->execute([
                    ':kode'    => trim($kode_barang),
                    ':nama'    => trim($nama_barang),
                    ':jumlah'  => $jumlah_int,
                    ':satuan'  => trim($satuan),
                    ':lokasi'  => trim($lokasi),
                    ':tanggal' => $tanggal_masuk,
                ]);
            } else {
                $stmt = $pdo->prepare(
                    "INSERT INTO barang (kode_barang, nama_barang, jumlah, satuan, lokasi, tanggal_masuk)
                     VALUES (:kode, :nama, :jumlah, :satuan, :lokasi, NULL)"
                );
                $stmt->execute([
                    ':kode'   => trim($kode_barang),
                    ':nama'   => trim($nama_barang),
                    ':jumlah' => $jumlah_int,
                    ':satuan' => trim($satuan),
                    ':lokasi' => trim($lokasi),
                ]);
            }

            header('Location: index.php?msg=Data barang berhasil ditambahkan');
            exit;
        } catch (PDOException $e) {
            $error = 'Gagal menyimpan data: ' . $e->getMessage();
        }
    }
}

$baseUrl = '';
$pageTitle = 'Tambah Sepatu';
include 'include/header.php';
?>

<div class="app-shell">
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Tambah Sepatu</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>

                    <form method="post" action="">
                        <div class="mb-3">
                            <label for="kode_barang" class="form-label">Kode Sepatu</label>
                            <input type="text" class="form-control" id="kode_barang" name="kode_barang"
                                   required value="<?= htmlspecialchars($_POST['kode_barang'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label for="nama_barang" class="form-label">Nama Sepatu</label>
                            <input type="text" class="form-control" id="nama_barang" name="nama_barang"
                                   required value="<?= htmlspecialchars($_POST['nama_barang'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label for="jumlah" class="form-label">Jumlah/Stok</label>
                            <input type="number" class="form-control" id="jumlah" name="jumlah" min="0"
                                   required value="<?= htmlspecialchars($_POST['jumlah'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label for="satuan" class="form-label">Satuan</label>
                            <input type="text" class="form-control" id="satuan" name="satuan"
                                   value="<?= htmlspecialchars($_POST['satuan'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label for="lokasi" class="form-label">Lokasi</label>
                            <input type="text" class="form-control" id="lokasi" name="lokasi"
                                   value="<?= htmlspecialchars($_POST['lokasi'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label for="tanggal_masuk" class="form-label">Tanggal Masuk</label>
                            <input type="date" class="form-control" id="tanggal_masuk" name="tanggal_masuk"
                                   value="<?= htmlspecialchars($_POST['tanggal_masuk'] ?? '') ?>">
                        </div>
                        <div class="d-flex justify-content-between">
                            <a href="index.php" class="btn btn-secondary">Kembali</a>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

<?php include 'include/footer.php'; ?>

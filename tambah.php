<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    $rememberCookieName = 'remember_user_id';
    if (!empty($_COOKIE[$rememberCookieName])) {
        require_once 'koneksi.php';
        $uid = (int)$_COOKIE[$rememberCookieName];
        if ($uid > 0) {
            try {
                $stmt = $pdo->prepare('SELECT user_id, username FROM users WHERE user_id = :uid LIMIT 1');
                $stmt->execute([':uid' => $uid]);
                $u = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($u) {
                    $_SESSION['user_id'] = (int)$u['user_id'];
                    $_SESSION['username'] = (string)$u['username'];
                }
            } catch (PDOException $e) {
                // abaikan
            }
        }
    }

    if (!isset($_SESSION['user_id'])) {
        require_once 'koneksi.php';

        $usersCount = 0;
        try {
            $stmt = $pdo->query('SELECT COUNT(*) AS cnt FROM users');
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $usersCount = (int)($row['cnt'] ?? 0);
        } catch (PDOException $e) {
            $usersCount = 0;
        }

        $target = $usersCount > 0 ? 'login.php' : 'register.php';
        $msgText = $usersCount > 0 ? 'Silakan login terlebih dahulu untuk mengakses halaman admin.' : 'Silakan daftar terlebih dahulu untuk mengakses halaman admin.';
        header('Location: ' . $target . '?msg=' . urlencode($msgText));
        exit;
    }
}

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

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

$sessionTimeoutSeconds = 10;
if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['last_activity']) && (time() - (int)$_SESSION['last_activity']) > $sessionTimeoutSeconds) {
        $_SESSION = [];
        session_destroy();
        $secure = (!empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off');
        setcookie('remember_user_id', '', time() - 3600, '/', '', $secure, true);
        header('Location: login.php?msg=' . urlencode('Sesi habis. Silakan login kembali.'));
        exit;
    }
    $_SESSION['last_activity'] = time();
}

require_once 'koneksi.php';

// 1. Ambil kode barang secara aman dari parameter GET atau POST
$kode_barang = $_GET['kode_barang'] ?? ($_POST['kode_barang'] ?? '');

if ($kode_barang === '') {
    header('Location: index.php');
    exit;
}

$error = '';

// 2. Ambil data lama dari database (dilakukan di awal agar form punya nilai default)
try {
    $stmt = $pdo->prepare("SELECT * FROM barang WHERE kode_barang = :kode");
    $stmt->execute([':kode' => $kode_barang]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $data = null;
}

if (!$data) {
    header('Location: index.php?msg=' . urlencode('Data sepatu tidak ditemukan'));
    exit;
}

// 3. Proses Update Data jika tombol ditekkan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_barang   = trim($_POST['nama_barang'] ?? '');
    $jumlah        = trim($_POST['jumlah'] ?? '');
    $satuan        = trim($_POST['satuan'] ?? '');
    $lokasi        = trim($_POST['lokasi'] ?? '');
    $tanggal_masuk = trim($_POST['tanggal_masuk'] ?? '');

    // Validasi input kosong
    if ($nama_barang === '' || $jumlah === '') {
        $error = 'Nama barang dan jumlah wajib diisi.';
    } else {
        try {
            // Gunakan tanggal lama jika input tanggal dikosongkan secara paksa
            if ($tanggal_masuk === '') {
                $tanggal_masuk = $data['tanggal_masuk']; 
            }

            // Eksekusi query Update
            $sql = "UPDATE barang SET 
                        nama_barang = :nama,
                        jumlah      = :jumlah,
                        satuan      = :satuan,
                        lokasi      = :lokasi,
                        tanggal_masuk = :tanggal
                    WHERE kode_barang = :kode";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':nama'    => $nama_barang,
                ':jumlah'  => (int)$jumlah,
                ':satuan'  => $satuan,
                ':lokasi'  => $lokasi,
                ':tanggal' => $tanggal_masuk,
                ':kode'    => $kode_barang
            ]);

            // Jika berhasil, alihkan ke halaman utama
            header('Location: index.php?msg=' . urlencode('Data sepatu berhasil diperbarui!'));
            exit;
        } catch (PDOException $e) {
            $error = 'Gagal menyimpan ke database: ' . $e->getMessage();
        }
    }
}

// Pengaturan untuk template
$baseUrl = '';
$pageTitle = 'Edit Sepatu';
include 'include/header.php';
?>

<div class="app-shell">
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fa-solid fa-pen-to-square me-2"></i> Edit Data Sepatu</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger">
                            <i class="fa-solid fa-triangle-exclamation me-2"></i> <?= htmlspecialchars($error) ?>
                        </div>
                    <?php endif; ?>

                    <form method="post" action="edit.php?kode_barang=<?= urlencode($kode_barang) ?>">
                        
                        <input type="hidden" name="kode_barang" value="<?= htmlspecialchars($kode_barang) ?>">

                        <div class="mb-3">
                            <label for="kode_barang_tampil" class="form-label">Kode Sepatu</label>
                            <input type="text" class="form-control bg-light text-muted" id="kode_barang_tampil" 
                                   disabled value="<?= htmlspecialchars($data['kode_barang']) ?>">
                            <div class="form-text">Kode barang otomatis terkunci dan tidak dapat diubah.</div>
                        </div>
                        <div class="mb-3">
                            <label for="nama_barang" class="form-label">Nama Sepatu <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nama_barang" name="nama_barang"
                                   required value="<?= htmlspecialchars($_POST['nama_barang'] ?? $data['nama_barang']) ?>">
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="jumlah" class="form-label">Jumlah/Stok <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="jumlah" name="jumlah" min="0"
                                       required value="<?= htmlspecialchars($_POST['jumlah'] ?? $data['jumlah']) ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="satuan" class="form-label">Satuan</label>
                                <input type="text" class="form-control" id="satuan" name="satuan"
                                       value="<?= htmlspecialchars($_POST['satuan'] ?? $data['satuan']) ?>">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="lokasi" class="form-label">Lokasi Gudang</label>
                            <input type="text" class="form-control" id="lokasi" name="lokasi"
                                   value="<?= htmlspecialchars($_POST['lokasi'] ?? $data['lokasi']) ?>">
                        </div>
                        <div class="mb-3">
                            <label for="tanggal_masuk" class="form-label">Tanggal Masuk</label>
                            <input type="date" class="form-control" id="tanggal_masuk" name="tanggal_masuk"
                                   required value="<?= htmlspecialchars($_POST['tanggal_masuk'] ?? $data['tanggal_masuk']) ?>">
                        </div>
                        
                        <hr class="mt-4 mb-3">
                        <div class="d-flex justify-content-between">
                            <a href="index.php" class="btn btn-secondary">
                                <i class="fa-solid fa-arrow-left me-1"></i> Batal
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fa-solid fa-floppy-disk me-1"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

<?php include 'include/footer.php'; ?>
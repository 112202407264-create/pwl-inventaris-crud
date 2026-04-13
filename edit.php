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

$sessionTimeoutSeconds = 10800; // 3 jam
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
$errors = []; // 4c. Simpan pesan kesalahan dalam sebuah array
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_barang   = trim($_POST['nama_barang'] ?? '');
    $jumlah        = trim($_POST['jumlah'] ?? '');
    $harga         = trim($_POST['harga'] ?? '');
    $satuan        = trim($_POST['satuan'] ?? '');
    $lokasi        = trim($_POST['lokasi'] ?? '');
    $tanggal_masuk = trim($_POST['tanggal_masuk'] ?? '');

    // 4. Implementasi Validasi Server
    if ($nama_barang === '') {
        $errors[] = 'Nama barang wajib diisi.';
    }
    if ($jumlah === '' || !is_numeric($jumlah)) {
        $errors[] = 'Jumlah harus berupa angka.';
    }
    if ($harga === '' || !is_numeric($harga)) {
        $errors[] = 'Harga harus berupa angka.';
    }

    if (empty($errors)) {
        try {
            // Gunakan tanggal lama jika input tanggal dikosongkan secara paksa
            if ($tanggal_masuk === '') {
                $tanggal_masuk = $data['tanggal_masuk']; 
            }

            // 6. Implementasi Logika Unggah File
            $gambar_path = $data['gambar'] ?? null; // Default pakai gambar lama
            if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
                // 6d. Pindahkan direktori (folder uploads/)
                $upload_dir = 'uploads/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                $file_tmp = $_FILES['gambar']['tmp_name'];
                $file_name = $_FILES['gambar']['name'];
                $file_size = $_FILES['gambar']['size'];
                $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                $allowed_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                
                // 6b. Validasi tipe file dan ukurannya
                if (!in_array($file_ext, $allowed_exts)) {
                    $errors[] = 'Format gambar tidak valid (hanya JPG, PNG, GIF, WEBP).';
                } elseif ($file_size > 2000000) { // maksimal 2MB
                    $errors[] = 'Ukuran gambar maksimal 2MB.';
                } else {
                    // 6c. Buat nama unik
                    $new_file_name = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9.\-_]/', '', $file_name);
                    $dest_path = $upload_dir . $new_file_name;
                    
                    if (move_uploaded_file($file_tmp, $dest_path)) {
                        // Hapus gambar lama jika ada
                        if ($gambar_path && file_exists($gambar_path)) {
                            unlink($gambar_path);
                        }
                        // 6e. Simpan nama unik ke variabel (nanti ke basis data)
                        $gambar_path = $dest_path;
                    } else {
                        $errors[] = 'Gagal mengunggah gambar baru.';
                    }
                }
            }

            if (empty($errors)) {
                // 5. Implementasi Prepared Statements (UPDATE)
                $sql = "UPDATE barang SET 
                            nama_barang = :nama,
                            jumlah      = :jumlah,
                            harga       = :harga,
                            satuan      = :satuan,
                            lokasi      = :lokasi,
                            gambar      = :gambar,
                            tanggal_masuk = :tanggal
                        WHERE kode_barang = :kode";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':nama'    => $nama_barang,
                    ':jumlah'  => (int)$jumlah,
                    ':harga'   => (int)$harga,
                    ':satuan'  => $satuan,
                    ':lokasi'  => $lokasi,
                    ':gambar'  => $gambar_path,
                    ':tanggal' => $tanggal_masuk,
                    ':kode'    => $kode_barang
                ]);

                // Jika berhasil, alihkan ke halaman utama
                header('Location: index.php?msg=' . urlencode('Data sepatu berhasil diperbarui!'));
                exit;
            }
        } catch (PDOException $e) {
            $errors[] = 'Gagal menyimpan ke database.';
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
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $err): ?>
                                    <li><i class="fa-solid fa-triangle-exclamation me-1"></i> <?= htmlspecialchars($err) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="post" action="edit.php?kode_barang=<?= urlencode($kode_barang) ?>" enctype="multipart/form-data">
                        
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
                            <div class="col-md-4 mb-3">
                                <label for="jumlah" class="form-label">Jumlah/Stok <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="jumlah" name="jumlah" min="0"
                                       required value="<?= htmlspecialchars($_POST['jumlah'] ?? $data['jumlah']) ?>">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="harga" class="form-label">Harga (Rp) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="harga" name="harga" min="0"
                                       required value="<?= htmlspecialchars($_POST['harga'] ?? ($data['harga'] ?? 0)) ?>">
                            </div>
                            <div class="col-md-4 mb-3">
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
                            <label for="gambar" class="form-label">Gambar Barang (Opsional)</label>
                            <?php if (!empty($data['gambar'])): ?>
                                <div class="mb-2">
                                    <img src="<?= htmlspecialchars($data['gambar']) ?>" alt="Gambar" width="150" class="img-thumbnail">
                                </div>
                            <?php endif; ?>
                            <!-- 3. Modifikasi form -->
                            <input type="file" class="form-control" id="gambar" name="gambar" accept="image/*">
                            <div class="form-text">Biarkan kosong jika tidak ingin mengubah gambar. Batas upload maksimal 2MB.</div>
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
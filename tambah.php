<?php
require_once 'koneksi.php';

// Proses simpan data jika form disubmit
$errors = []; // 4c: simpan pesan kesalahan dalam sebuah array
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kode_barang   = $_POST['kode_barang'] ?? '';
    $nama_barang   = trim($_POST['nama_barang'] ?? '');
    $jumlah        = $_POST['jumlah'] ?? '';
    $harga         = $_POST['harga'] ?? '';
    $satuan        = $_POST['satuan'] ?? '';
    $lokasi        = $_POST['lokasi'] ?? '';
    $tanggal_masuk = $_POST['tanggal_masuk'] ?? '';

    // 4. Implementasi Validasi Server
    // 4a. Periksa apakah nama_barang tidak kosong.
    if ($kode_barang === '') {
        $errors[] = 'Kode barang tidak boleh kosong.';
    }
    if ($nama_barang === '') {
        $errors[] = 'Nama barang tidak boleh kosong.';
    }
    
    // 4b. Pastikan jumlah dan harga adalah nilai numerik
    if ($jumlah === '' || !is_numeric($jumlah)) {
        $errors[] = 'Jumlah harus berupa angka.';
    }
    if ($harga === '' || !is_numeric($harga)) {
        $errors[] = 'Harga harus berupa angka.';
    }

    if (empty($errors)) {
        $jumlah_int    = (int)$jumlah;
        $harga_int     = (int)$harga;
        $tanggal_masuk = $tanggal_masuk ?: null;

        // 6. Implementasi Logika Unggah File
        $gambar_path = null;
        // 6a. Periksa apakah ada file yang diunggah dan tidak ada error
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
            
            // 6b. Validasi tipe file dan ukurannya
            $allowed_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (!in_array($file_ext, $allowed_exts)) {
                $errors[] = 'Format gambar tidak valid (hanya JPG, PNG, GIF, WEBP).';
            } elseif ($file_size > 2000000) { // maksimal 2MB
                $errors[] = 'Ukuran gambar maksimal 2MB.';
            } else {
                // 6c. Buat nama file unik (kombinasi uniqid() dengan nama asli)
                $new_file_name = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9.\-_]/', '', $file_name);
                $dest_path = $upload_dir . $new_file_name;
                
                if (move_uploaded_file($file_tmp, $dest_path)) {
                    // 6e. Simpan nama file unik ke basis data
                    $gambar_path = $dest_path; 
                } else {
                    $errors[] = 'Gagal memindahkan file gambar.';
                }
            }
        }

        if (empty($errors)) {
            try {
                // 5. Implementasi Prepared Statements (INSERT)
                $query = "INSERT INTO barang (kode_barang, nama_barang, jumlah, harga, satuan, lokasi, gambar, tanggal_masuk)
                          VALUES (:kode, :nama, :jumlah, :harga, :satuan, :lokasi, :gambar, :tanggal)";
                
                $stmt = $pdo->prepare($query);
                $stmt->execute([
                    ':kode'    => trim($kode_barang),
                    ':nama'    => $nama_barang,
                    ':jumlah'  => $jumlah_int,
                    ':harga'   => $harga_int,
                    ':satuan'  => trim($satuan),
                    ':lokasi'  => trim($lokasi),
                    ':gambar'  => $gambar_path,
                    ':tanggal' => $tanggal_masuk, // Menyimpan nama file unik (via variabel path)
                ]);

                header('Location: index.php?msg=Data barang berhasil ditambahkan');
                exit;
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) {
                     $errors[] = 'Kode barang sudah digunakan, silakan gunakan kode lain.';
                } else {
                     $errors[] = 'Gagal menyimpan data ke database.';
                }
                
                if ($gambar_path && file_exists($gambar_path)) {
                    unlink($gambar_path);
                }
            }
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
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $err): ?>
                                    <li><?= htmlspecialchars($err) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <!-- 3. Modifikasi Form: tambahkan atribut enctype="multipart/form-data" -->
                    <form method="post" action="" enctype="multipart/form-data">
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
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="jumlah" class="form-label">Jumlah/Stok</label>
                                <input type="number" class="form-control" id="jumlah" name="jumlah" min="0"
                                       required value="<?= htmlspecialchars($_POST['jumlah'] ?? '') ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="harga" class="form-label">Harga (Rp)</label>
                                <input type="number" class="form-control" id="harga" name="harga" min="0"
                                       required value="<?= htmlspecialchars($_POST['harga'] ?? '') ?>">
                            </div>
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
                            <!-- 3. Modifikasi Form: input file gambar -->
                            <label for="gambar" class="form-label">Gambar Barang (Opsional)</label>
                            <input type="file" class="form-control" id="gambar" name="gambar" accept="image/*">
                            <div class="form-text">Batas upload gambar maksimal 2MB. Ekstensi .jpg, .png, .gif, .webp diperbolehkan.</div>
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

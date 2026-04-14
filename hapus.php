<?php
require_once 'include/auth.php';
require_once 'koneksi.php';

// Ambil kode_barang dari parameter URL
$kode_barang = isset($_GET['kode_barang']) ? trim($_GET['kode_barang']) : '';

// Jika kode barang kosong, kembalikan dengan pesan error
if ($kode_barang === '') {
    header('Location: index.php?msg=' . urlencode('Gagal: Parameter kode_barang tidak ditemukan di URL!'));
    exit;
}

try {
    // Cari data untuk mendapatkan path gambar
    $stmtSelect = $pdo->prepare("SELECT gambar FROM barang WHERE kode_barang = :kode_barang");
    $stmtSelect->execute([':kode_barang' => $kode_barang]);
    $data = $stmtSelect->fetch(PDO::FETCH_ASSOC);

    // Eksekusi query DELETE
    $stmt = $pdo->prepare("DELETE FROM barang WHERE kode_barang = :kode_barang");
    $stmt->execute([':kode_barang' => $kode_barang]);

    // Cek apakah ada data yang benar-benar terhapus
    if ($stmt->rowCount() > 0) {
        // Hapus file gambar secara fisik jika ada
        if ($data && !empty($data['gambar']) && file_exists($data['gambar'])) {
            unlink($data['gambar']);
        }
        header('Location: index.php?msg=' . urlencode('Data sepatu berhasil dihapus!'));
    } else {
        // Jika rowCount 0, berarti kode_barang dicari tapi tidak ada di database
        header('Location: index.php?msg=' . urlencode('Gagal: Kode sepatu tidak ditemukan di database.'));
    }
    exit;

} catch (PDOException $e) {
    // Tampilkan pesan error jika query gagal agar kita tahu penyebab pastinya
    die("Error Database: " . $e->getMessage());
}
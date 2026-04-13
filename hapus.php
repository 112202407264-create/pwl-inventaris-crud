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
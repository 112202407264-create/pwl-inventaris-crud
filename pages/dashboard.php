<?php
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(0); 
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    // Autologin dari cookie Remember Me
    $rememberCookieName = 'remember_user_id';
    if (!empty($_COOKIE[$rememberCookieName])) {
        require_once '../koneksi.php';
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
                // Abaikan autologin saat struktur tabel belum ada/bermasalah
            }
        }
    }

    if (isset($_SESSION['user_id'])) {
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }


    require_once '../koneksi.php';

    $usersCount = 0;
    try {
        $stmt = $pdo->query('SELECT COUNT(*) AS cnt FROM users');
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $usersCount = (int)($row['cnt'] ?? 0);
    } catch (PDOException $e) {
        // Jika tabel users belum ada, anggap belum ada akun
        $usersCount = 0;
    }

    if ($usersCount > 0) {
        header('Location: ../login.php?msg=' . urlencode('Silakan login terlebih dahulu untuk mengakses dashboard.'));
    } else {
        header('Location: ../register.php?msg=' . urlencode('Silakan daftar terlebih dahulu untuk mengakses dashboard.'));
    }
    exit;
}

$sessionTimeoutSeconds = 60;
if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['last_activity']) && (time() - (int)$_SESSION['last_activity']) > $sessionTimeoutSeconds) {
        $_SESSION = [];
        session_destroy();
        $secure = (!empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off');
        setcookie('remember_user_id', '', time() - 3600, '/', '', $secure, true);
        header('Location: ../login.php?msg=' . urlencode('Sesi habis. Silakan login kembali.'));
        exit;
    }
    $_SESSION['last_activity'] = time();
}

// Jika lolos pengecekan di atas, berarti user sudah login
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
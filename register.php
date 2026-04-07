<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'koneksi.php';

$error = '';
$msg = isset($_GET['msg']) ? trim((string)$_GET['msg']) : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    $password_confirm = (string)($_POST['password_confirm'] ?? '');

    if ($username === '' || $password === '' || $password_confirm === '') {
        $error = 'Semua kolom wajib diisi.';
    } elseif (!preg_match('/^[a-zA-Z0-9_]{3,30}$/', $username)) {
        $error = 'Username hanya boleh huruf, angka, dan underscore (3-30 karakter).';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter.';
    } elseif ($password !== $password_confirm) {
        $error = 'Konfirmasi password tidak sesuai.';
    } else {
        try {
            // Cek username sudah dipakai atau belum
            $stmt = $pdo->prepare('SELECT user_id FROM users WHERE username = :username LIMIT 1');
            $stmt->execute([':username' => $username]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existing) {
                $error = 'Username sudah digunakan. Silakan pilih username lain.';
            } else {
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare('INSERT INTO users (username, password_hash) VALUES (:username, :password_hash)');
                $stmt->execute([
                    ':username' => $username,
                    ':password_hash' => $password_hash,
                ]);

                header('Location: login.php?msg=' . urlencode('Registrasi berhasil. Silakan login.'));
                exit;
            }
        } catch (PDOException $e) {
            // Umumnya tabel users belum ada
            $error = 'Gagal register: ' . ($e->getCode() === '23000' ? 'username sudah dipakai.' : 'cek struktur tabel (users).');
        }
    }
}

// Blok PHP digabung di sini (tanpa perlu membuka tag <?php baru)
$cssFile = __DIR__ . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'app.css';
$cssVer = file_exists($cssFile) ? filemtime($cssFile) : time();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register — Inventaris Sepatu</title>
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="assets/app.css?v=<?= (int)$cssVer ?>">
</head>
<body class="auth-page-body">
<div class="auth-card">
    <div class="auth-card-header">
        <h5>Daftar Akun Baru</h5>
        <div class="auth-card-subtitle">Daftar untuk mengakses sistem</div>
    </div>

    <div class="auth-card-body">
        <?php if ($msg): ?>
            <div class="alert alert-success" role="alert">
                <?= htmlspecialchars($msg) ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger" role="alert">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form class="auth-form" method="post" action="">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" required
                       value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>

            <div class="mb-3">
                <label for="password_confirm" class="form-label">Konfirmasi Password</label>
                <input type="password" class="form-control" id="password_confirm" name="password_confirm" required>
            </div>

            <button type="submit" class="btn btn-primary auth-primary-btn">
                <i class="fa-solid fa-user-plus me-1"></i> Daftar
            </button>
        </form>

        <div class="text-center mt-3 small">
            Sudah punya akun? <a href="login.php" class="auth-link">Login di sini</a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
</body>
</html>
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(0); 
    session_start();
}

require_once 'koneksi.php';

$rememberCookieName = 'remember_user_id';
$rememberSeconds = 10;

// Autologin dari cookie "Remember Me"
if (empty($_SESSION['user_id']) && !empty($_COOKIE[$rememberCookieName])) {
    $uid = (int)$_COOKIE[$rememberCookieName];
    if ($uid > 0) {
        $stmt = $pdo->prepare('SELECT user_id, username FROM users WHERE user_id = :uid LIMIT 1');
        $stmt->execute([':uid' => $uid]);
        $u = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($u) {
            $_SESSION['user_id'] = (int)$u['user_id'];
            $_SESSION['username'] = (string)$u['username'];
            $_SESSION['last_activity'] = time();
        } else {
            // Cookie sudah tidak valid (user sudah dihapus), bersihkan
            $secure = (!empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off');
            setcookie($rememberCookieName, '', time() - 3600, '/', '', $secure, true);
        }
    }
}

// Jika sudah login, langsung arahkan ke dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: pages/dashboard.php');
    exit;
}

$error = '';
$msg = isset($_GET['msg']) ? trim((string)$_GET['msg']) : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    $rememberMe = isset($_POST['remember_me']) && ($_POST['remember_me'] === '1' || $_POST['remember_me'] === 1);

    if ($username === '' || $password === '') {
        $error = 'Username dan password wajib diisi.';
    } else {
        try {
            $stmt = $pdo->prepare('SELECT user_id, username, password_hash FROM users WHERE username = :username LIMIT 1');
            $stmt->execute([':username' => $username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user || !password_verify($password, (string)$user['password_hash'])) {
                $error = 'Username atau password salah.';
            } else {
                // Regenerasi session agar lebih aman terhadap session fixation
                session_regenerate_id(true);
                $_SESSION['user_id'] = (int)$user['user_id'];
                $_SESSION['username'] = (string)$user['username'];
                $_SESSION['last_activity'] = time();

                $secure = (!empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off');

                if ($rememberMe) {
                    // Set cookie Remember Me selama 1 menit
                    $expires = time() + $rememberSeconds;
                    setcookie($rememberCookieName, (string)$_SESSION['user_id'], $expires, '/', '', $secure, true);
                } else {
                    // Hapus cookie remember me jika ada
                    setcookie($rememberCookieName, '', time() - 3600, '/', '', $secure, true);
                    
                    // PERTEGAS: Set cookie session bawaan PHP agar benar-benar mati saat browser ditutup
                    setcookie(session_name(), session_id(), 0, '/', '', $secure, true);
                }

                header('Location: pages/dashboard.php');
                exit;
            }
        } catch (PDOException $e) {
            $error = 'Gagal login: cek struktur tabel (users).';
        }
    }
}

// Blok PHP digabung di sini
$cssFile = __DIR__ . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'app.css';
$cssVer = file_exists($cssFile) ? filemtime($cssFile) : time();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Inventaris Sepatu</title>
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="assets/app.css?v=<?= (int)$cssVer ?>">
</head>
<body class="auth-page-body">
<div class="auth-card">
    <div class="auth-card-header">
        <h5>Selamat Datang</h5>
        <div class="auth-card-subtitle">Silakan login untuk melanjutkan</div>
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

            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" value="1" id="remember_me" name="remember_me"
                    <?= !empty($_POST['remember_me']) ? 'checked' : '' ?>>
                <label class="form-check-label" for="remember_me">Remember Me</label>
            </div>

            <button type="submit" class="btn btn-primary auth-primary-btn">
                <i class="fa-solid fa-right-to-bracket me-1"></i> Masuk Sekarang
            </button>
        </form>

        <div class="text-center mt-3 small">
            Belum punya akun? <a href="register.php" class="auth-link">Daftar di sini</a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
</body>
</html>
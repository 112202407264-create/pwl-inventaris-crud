<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Hapus semua data session
$_SESSION = [];

// Matikan session
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], (bool)$params['secure'], (bool)$params['httponly']);
}

session_destroy();

// Hapus cookie Remember Me
$rememberCookieName = 'remember_user_id';
$secure = (!empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off');
setcookie($rememberCookieName, '', time() - 3600, '/', '', $secure, true);

header('Location: login.php?msg=' . urlencode('Anda berhasil logout.'));
exit;


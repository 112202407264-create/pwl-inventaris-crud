<?php
// Mulai session (aman dipanggil berkali-kali)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek apakah user sudah login
// Jika belum, arahkan ke halaman login
if (empty($_SESSION['user_id'])) {
    $loginUrl = (isset($baseUrl) ? $baseUrl : '') . 'login.php';
    header('Location: ' . $loginUrl);
    exit;
}

<?php
// ============================================================
// logout.php — Proses Keluar
// ============================================================
// Yang dilakukan:
//   1. Hapus semua data session
//   2. Hapus cookie Remember Me
//   3. Redirect ke halaman login
// ============================================================

session_start();

// 1. Hancurkan session
session_destroy();

// 2. Hapus cookie Remember Me (nama harus sama persis dengan yang di login.php)
//    Caranya: set nilai kosong dengan waktu expire di masa lalu
setcookie('remember_me', '', time() - 3600, '/');

// 3. Balik ke login
header('Location: login.php?msg=' . urlencode('Anda berhasil logout.'));
exit;

<?php
session_start();
session_destroy();
setcookie('remember_me', '', time() - 3600, '/');
header('Location: login.php?msg=' . urlencode('Anda berhasil logout.'));
exit;

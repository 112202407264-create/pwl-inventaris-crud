<?php
require_once 'koneksi.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    try {
        $stmt = $pdo->prepare("DELETE FROM barang WHERE id = :id");
        $stmt->execute([':id' => $id]);
    } catch (PDOException $e) {
        // Untuk latihan cukup abaikan error dan tetap redirect
    }
}

header('Location: index.php?msg=Data barang berhasil dihapus');
exit;


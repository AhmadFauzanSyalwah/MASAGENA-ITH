<?php
session_start();
require_once __DIR__ . '/../config/database.php';
if (!isset($_SESSION['peran']) || $_SESSION['peran'] !== 'admin') {
    header('Location: ' . BASE_URL . '/auth/login.php');
    exit;
}

$id = $_GET['id'] ?? 0;
if ($id) {
    // Hapus pengurus terkait dulu (opsional, bisa juga cascade)
    $stmt = $conn->prepare("DELETE FROM pengurus_organisasi WHERE id_organisasi = :id");
    $stmt->execute([':id' => $id]);
    // Hapus organisasi
    $stmt = $conn->prepare("DELETE FROM organisasi WHERE id_organisasi = :id");
    $stmt->execute([':id' => $id]);
}
header('Location: index.php');
exit;
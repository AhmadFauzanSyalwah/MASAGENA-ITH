<?php
// dashboard/pengurus/proses_hapus.php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['peran'], ['pengurus', 'admin'])) {
    header('Location: ../../auth/login.php');
    exit;
}
require_once '../../config/database.php';

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
if ($id) {
    $pdo->prepare("DELETE FROM konten_kegiatan WHERE id_konten = ?")->execute([$id]);
    $_SESSION['success'] = 'Kegiatan berhasil dihapus.';
}
header('Location: kelola_konten.php');
exit;
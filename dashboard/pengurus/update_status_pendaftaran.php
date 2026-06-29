<?php
// dashboard/pengurus/update_status_pendaftaran.php
session_start();

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['peran'], ['pengurus', 'admin'])) {
    header('Location: ../../auth/login.php');
    exit;
}

require_once '../../config/database.php';

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$status = isset($_POST['status']) ? $_POST['status'] : '';

if (!$id || !in_array($status, ['menunggu', 'diterima', 'ditolak'])) {
    header('Location: pendaftaran.php?msg=error&text=' . urlencode('Data tidak valid.'));
    exit;
}

$update = $pdo->prepare("UPDATE pendaftaran SET status_pendaftaran = ? WHERE id_pendaftaran = ?");
if ($update->execute([$status, $id])) {
    $message = 'Status berhasil diperbarui menjadi ' . ucfirst($status);
    header('Location: pendaftaran.php?msg=success&text=' . urlencode($message));
} else {
    header('Location: pendaftaran.php?msg=error&text=' . urlencode('Gagal memperbarui status.'));
}
exit;
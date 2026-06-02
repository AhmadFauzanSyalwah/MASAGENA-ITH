<?php
require_once 'connection.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: kelola-pendaftaran-kegiatan.php');
    exit;
}

$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
$status = $_POST['status'] ?? '';
$allowed = ['diterima', 'ditolak', 'pending'];

if ($id <= 0 || !in_array($status, $allowed, true)) {
    header('Location: kelola-pendaftaran-kegiatan.php?error=invalid');
    exit;
}

$stmt = mysqli_prepare($conn, "UPDATE pendaftaran_kegiatan SET status_pendaftaran = ? WHERE id_pendaftaran_kegiatan = ?");
mysqli_stmt_bind_param($stmt, 'si', $status, $id);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

header('Location: kelola-pendaftaran-kegiatan.php?updated=1');
exit;
?>

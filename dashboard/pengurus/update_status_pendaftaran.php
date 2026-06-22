<?php
require_once __DIR__ . '/../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: kelola_pendaftaran_kegiatan.php');
    exit;
}

$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
$status = $_POST['status'] ?? '';

$allowedStatus = ['menunggu', 'diterima', 'ditolak'];

if ($id <= 0 || !in_array($status, $allowedStatus, true)) {
    header('Location: kelola_pendaftaran_kegiatan.php?error=invalid');
    exit;
}

$stmt = mysqli_prepare($conn, "
    UPDATE pendaftaran
    SET status_pendaftaran = ?
    WHERE id_pendaftaran = ?
    LIMIT 1
");

if (!$stmt) {
    header('Location: kelola_pendaftaran_kegiatan.php?error=prepare');
    exit;
}

mysqli_stmt_bind_param($stmt, 'si', $status, $id);

$ok = mysqli_stmt_execute($stmt);
$error = mysqli_stmt_error($stmt);

mysqli_stmt_close($stmt);

if (!$ok) {
    header('Location: kelola_pendaftaran_kegiatan.php?error=update');
    exit;
}

header('Location: kelola_pendaftaran_kegiatan.php?updated=1');
exit;
?>
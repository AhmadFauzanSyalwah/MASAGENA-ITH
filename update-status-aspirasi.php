<?php
include 'connection.php';
include 'components.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$status = $_GET['status'] ?? '';
$validStatus = ['proses', 'selesai', 'ditolak'];

if ($id <= 0 || !in_array($status, $validStatus, true)) {
    die('Status atau ID aspirasi tidak valid.');
}

$stmt = mysqli_prepare($conn, "UPDATE aspirasi SET status = ? WHERE id_aspirasi = ?");
mysqli_stmt_bind_param($stmt, 'si', $status, $id);
$ok = mysqli_stmt_execute($stmt);
$error = mysqli_stmt_error($stmt);
mysqli_stmt_close($stmt);

if ($ok) {
    $back = $_SERVER['HTTP_REFERER'] ?? 'kelola-aspirasi.php';
    header('Location: ' . $back);
    exit;
}

die('Gagal update status: ' . h($error));
?>

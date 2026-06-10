<?php
session_start();
if (!isset($_SESSION['id_mahasiswa'])) {
    header('Location: login.php');
    exit;
}
require_once __DIR__ . '/../config/database.php';

$stmt = $conn->prepare("SELECT * FROM tbmahasiswa WHERE id_mahasiswa = :id");
$stmt->execute([':id' => $_SESSION['id_mahasiswa']]);
$mahasiswa = $stmt->fetch(PDO::FETCH_ASSOC);

if ($mahasiswa['is_verified'] == 1) {
    header('Location: /dashboard/mahasiswa.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Verifikasi Akun</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center" style="min-height:100vh;">
<div class="text-center">
    <h4 class="text-warning">Akun Belum Diverifikasi</h4>
    <p>Akun Anda menunggu verifikasi admin.</p>
    <a href="logout.php" class="btn btn-secondary">Keluar</a>
</div>
</body>
</html>
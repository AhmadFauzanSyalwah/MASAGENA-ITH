<?php
session_start();
require_once __DIR__ . '/../config/database.php';

$nim   = trim($_POST['nim'] ?? '');
$nama  = trim($_POST['nama'] ?? '');
$email = trim($_POST['email'] ?? '');
$pass  = $_POST['password'] ?? '';

if (empty($nim) || empty($nama) || empty($email) || empty($pass)) {
    $_SESSION['error'] = 'Harap isi semua kolom.';
    header('Location: register.php');
    exit;
}

// Cek duplikasi
$stmt = $conn->prepare("SELECT id_mahasiswa FROM tbmahasiswa WHERE nim = :n OR email = :e LIMIT 1");
$stmt->execute([':n' => $nim, ':e' => $email]);
if ($stmt->fetch()) {
    $_SESSION['error'] = 'NIM atau email sudah terdaftar.';
    header('Location: register.php');
    exit;
}

// Insert
$stmt = $conn->prepare("INSERT INTO tbmahasiswa (nim, nama, email, password, is_verified) VALUES (:n, :nm, :e, :p, 0)");
$stmt->execute([':n' => $nim, ':nm' => $nama, ':e' => $email, ':p' => $pass]);

$_SESSION['success'] = 'Pendaftaran berhasil. Silakan login setelah akun diverifikasi admin.';
header('Location: login.php');
exit;
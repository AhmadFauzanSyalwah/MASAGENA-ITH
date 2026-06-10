<?php
session_start();
require_once __DIR__ . '/../config/database.php';

$credential = trim($_POST['credential'] ?? '');
$password   = $_POST['password'] ?? '';

if (empty($credential) || empty($password)) {
    $_SESSION['error'] = 'Harap isi semua kolom.';
    header('Location: ' . BASE_URL . '/auth/login.php');
    exit;
}

// 1. Cek mahasiswa (NIM atau email)
$stmt = $conn->prepare("SELECT * FROM tbmahasiswa WHERE (nim = :cred OR email = :cred) LIMIT 1");
$stmt->execute([':cred' => $credential]);
$mahasiswa = $stmt->fetch(PDO::FETCH_ASSOC);

if ($mahasiswa && $mahasiswa['password'] === $password) {
    if ($mahasiswa['is_verified'] != 1) {
        $_SESSION['id_mahasiswa'] = $mahasiswa['id_mahasiswa'];
        header('Location: ' . BASE_URL . '/auth/verifikasi.php');
        exit;
    }
    $_SESSION['id_mahasiswa'] = $mahasiswa['id_mahasiswa'];
    $_SESSION['nama'] = $mahasiswa['nama'];
    $_SESSION['nim'] = $mahasiswa['nim'];
    $_SESSION['peran'] = 'mahasiswa';
    header('Location: ' . BASE_URL . '/dashboard/mahasiswa.php');
    exit;
}

// 2. Cek pengurus (nama_pengurus)
$stmt = $conn->prepare("SELECT * FROM pengurus_organisasi WHERE nama_pengurus = :cred LIMIT 1");
$stmt->execute([':cred' => $credential]);
$pengurus = $stmt->fetch(PDO::FETCH_ASSOC);

if ($pengurus && $pengurus['password'] === $password) {
    $_SESSION['id_pengurus']   = $pengurus['id_pengurus'];
    $_SESSION['nama']          = $pengurus['nama_pengurus'];
    $_SESSION['peran']         = 'pengurus';
    $_SESSION['id_organisasi'] = $pengurus['id_organisasi'];   // ← tambahan satu baris ini
    header('Location: ' . BASE_URL . '/dashboard/pengurus.php');
    exit;
}

// 3. Cek admin (username)
$stmt = $conn->prepare("SELECT * FROM administrator WHERE username = :cred LIMIT 1");
$stmt->execute([':cred' => $credential]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if ($admin && $admin['password'] === $password) {
    $_SESSION['id_admin'] = $admin['id_admin'];
    $_SESSION['nama'] = $admin['nama_lengkap'];
    $_SESSION['peran'] = 'admin';
    header('Location: ' . BASE_URL . '/dashboard/admin.php');
    exit;
}

// Jika tidak cocok
$_SESSION['error'] = 'Kredensial tidak valid.';
header('Location: ' . BASE_URL . '/auth/login.php');
exit;
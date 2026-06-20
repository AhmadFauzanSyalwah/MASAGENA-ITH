<?php
// config/session_check.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 1. Cek apakah session user_id sudah ada
if (!isset($_SESSION['user_id']) || !isset($_SESSION['peran'])) {
    header("Location: ../auth/login.php");
    exit();
}

require_once __DIR__ . '/database.php';

$user_id = $_SESSION['user_id'];
$peran = $_SESSION['peran'];
$user = false;

try {
    // 2. Ambil data dari tabel yang sesuai dengan peran session
    if ($peran === 'admin') {
        $stmt = $pdo->prepare("SELECT id_admin AS id, nama_lengkap AS nama, 'admin' AS peran FROM administrator WHERE id_admin = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

    } elseif ($peran === 'pengurus') {
        $stmt = $pdo->prepare("SELECT id_pengurus AS id, nama_pengurus AS nama, 'pengurus' AS peran, id_organisasi, jabatan FROM pengurus_organisasi WHERE id_pengurus = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

    } elseif ($peran === 'mahasiswa') {
        $stmt = $pdo->prepare("SELECT id_mahasiswa AS id, nama, 'mahasiswa' AS peran, nim FROM tbmahasiswa WHERE id_mahasiswa = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // 3. Jika user tidak ditemukan di tabel manapun, hancurkan session
    if (!$user) {
        session_destroy();
        header("Location: ../auth/login.php?error=" . urlencode("Sesi tidak valid, silakan login kembali"));
        exit();
    }

    // 4. Perbarui data session dengan data terbaru dari database
    $_SESSION['nama'] = $user['nama'];
    $_SESSION['peran'] = $user['peran'];
    
    if ($peran === 'pengurus') {
        $_SESSION['id_organisasi'] = $user['id_organisasi'];
        $_SESSION['jabatan'] = $user['jabatan'];
    } elseif ($peran === 'mahasiswa') {
        $_SESSION['nim'] = $user['nim'];
    }

} catch (PDOException $e) {
    die("Kesalahan sistem pada validasi sesi: " . $e->getMessage());
}
?>
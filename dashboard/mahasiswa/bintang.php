<?php
// dashboard/mahasiswa/batal_pendaftaran.php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['peran'] != 'mahasiswa') {
    header('Location: ../../auth/login.php');
    exit;
}

require_once '../../config/database.php';

$id_pendaftaran = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_pendaftaran <= 0) {
    $_SESSION['error'] = 'ID pendaftaran tidak valid.';
    header('Location: pendaftaran.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Cek kepemilikan dan status
$check = $pdo->prepare("SELECT id_pendaftaran, status_pendaftaran FROM pendaftaran WHERE id_pendaftaran = ? AND id_mahasiswa = ?");
$check->execute([$id_pendaftaran, $user_id]);
$data = $check->fetch(PDO::FETCH_ASSOC);

if (!$data) {
    $_SESSION['error'] = 'Pendaftaran tidak ditemukan atau bukan milik Anda.';
    header('Location: pendaftaran.php');
    exit;
}

if ($data['status_pendaftaran'] != 'menunggu') {
    $_SESSION['error'] = 'Pendaftaran tidak dapat dibatalkan karena statusnya sudah ' . $data['status_pendaftaran'] . '.';
    header('Location: pendaftaran.php');
    exit;
}

// ============================================================
// CEK APAKAH STATUS 'batal' TERSEDIA DI ENUM
// ============================================================
$enumCheck = $pdo->query("SHOW COLUMNS FROM pendaftaran LIKE 'status_pendaftaran'")->fetch(PDO::FETCH_ASSOC);
$enumValues = [];
if ($enumCheck && preg_match("/^enum\((.*)\)$/", $enumCheck['Type'], $matches)) {
    $enumValues = array_map(function($v) {
        return trim($v, "'");
    }, explode(',', str_replace("'", "", $matches[1])));
}

$hasBatal = in_array('batal', $enumValues);

if ($hasBatal) {
    // Update status menjadi 'batal'
    $update = $pdo->prepare("UPDATE pendaftaran SET status_pendaftaran = 'batal' WHERE id_pendaftaran = ?");
    $success = $update->execute([$id_pendaftaran]);
    if ($success) {
        $_SESSION['success'] = 'Pendaftaran berhasil dibatalkan.';
    } else {
        $_SESSION['error'] = 'Gagal membatalkan pendaftaran.';
    }
} else {
    // Opsi alternatif: hapus data (karena tidak ada 'batal' di ENUM)
    // Atau tampilkan pesan dan tawarkan solusi
    $_SESSION['error'] = 'Status "batal" tidak tersedia di database. Silakan hubungi admin untuk menambahkan opsi "batal" pada kolom status_pendaftaran. Sebagai solusi sementara, pendaftaran akan dihapus.';
    // Hapus data (opsional)
    $delete = $pdo->prepare("DELETE FROM pendaftaran WHERE id_pendaftaran = ?");
    if ($delete->execute([$id_pendaftaran])) {
        $_SESSION['success'] = 'Pendaftaran berhasil dihapus (karena status "batal" tidak tersedia).';
    } else {
        $_SESSION['error'] = 'Gagal menghapus pendaftaran.';
    }
}

header('Location: pendaftaran.php');
exit;
?>
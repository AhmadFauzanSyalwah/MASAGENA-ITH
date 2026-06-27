<?php
require_once __DIR__ . '/../../config/database.php';
require_once '../../config/session_check.php';

// Pastikan request adalah POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: aspirasi_saya.php');
    exit;
}

// Ambil input
$idAspirasi = (int)($_POST['id_aspirasi'] ?? 0);
$isi = trim($_POST['isi_komentar'] ?? '');

// Validasi dasar
if ($idAspirasi <= 0 || $isi === '') {
    die('Komentar tidak valid.');
}

$level = 'mahasiswa';

try {
    // PERBAIKAN: Mengubah kolom 'isi_komentar' menjadi 'komentar' agar sesuai dengan database
    $sql = "INSERT INTO komentar_aspirasi (id_aspirasi, level_user, isi_komentar, created_at) 
            VALUES (?, ?, ?, NOW())";
            
    $stmt = $pdo->prepare($sql);
    $ok = $stmt->execute([
        $idAspirasi,
        $level,
        $isi
    ]);

    if ($ok) {
        header("Location: detail_aspirasi.php?id=$idAspirasi");
        exit;
    } else {
        die('Gagal menyimpan komentar.');
    }
} catch (PDOException $e) {
    die('Error Database: ' . $e->getMessage());
}
?>
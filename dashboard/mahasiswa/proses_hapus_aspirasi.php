<?php
// 1. Load konfigurasi database
require_once __DIR__ . '/../../config/database.php';

// 2. Load berkas fungsi (PENTING: Pastikan path ini benar menuju file components.php)
require_once __DIR__ . '/../../include/components.php';

// 3. Cek sesi login
require_once __DIR__ . '/../../config/session_check.php';

// 4. Pastikan metode request adalah POST untuk keamanan
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: aspirasi_saya.php");
    exit;
}

// 5. Ambil data mahasiswa
$mahasiswa = active_mahasiswa($pdo);
$id_aspirasi = isset($_POST['id_aspirasi']) ? (int)$_POST['id_aspirasi'] : 0;

// 6. Validasi data
if (!$mahasiswa || $id_aspirasi === 0) {
    header("Location: aspirasi_saya.php?status=error&msg=invalid");
    exit;
}

try {
    // 7. Jalankan hapus dengan proteksi id_mahasiswa (Agar tidak bisa hapus punya orang lain)
    $stmt = $pdo->prepare("DELETE FROM aspirasi WHERE id_aspirasi = ? AND id_mahasiswa = ?");
    $result = $stmt->execute([$id_aspirasi, (int)$mahasiswa['id_mahasiswa']]);

    if ($result) {
        header("Location: aspirasi_saya.php?status=success");
    } else {
        header("Location: aspirasi_saya.php?status=error");
    }
} catch (PDOException $e) {
    // Jika ada error database
    header("Location: aspirasi_saya.php?status=error&msg=db");
}
exit;
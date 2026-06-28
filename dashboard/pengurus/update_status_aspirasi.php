<?php
// Memulai sesi sistem 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Panggil konfigurasi database dan komponen
require_once __DIR__ . '/../../config/session_check.php';
require_once __DIR__ . '/../../config/database.php'; // Mengambil koneksi $pdo
require_once __DIR__ . '/../../include/components.php';

// Proteksi akses halaman, pastikan hanya peran pengurus yang dapat membuka
if ($_SESSION['peran'] != 'pengurus') {
    header("Location: ../" . $_SESSION['peran'] . "/index.php");
    exit();
}

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$status = $_GET['status'] ?? '';

$validStatus = ['proses', 'selesai', 'ditolak'];

function redirect_back($fallback = 'aspirasi_masuk.php') {
    $referer = $_SERVER['HTTP_REFERER'] ?? '';
    $host = $_SERVER['HTTP_HOST'] ?? '';

    if ($referer !== '') {
        $parts = parse_url($referer);

        if (!isset($parts['host']) || $parts['host'] === $host) {
            header('Location: ' . $referer);
            exit;
        }
    }

    header('Location: ' . $fallback);
    exit;
}

if ($id <= 0 || !in_array($status, $validStatus, true)) {
    header('Location: aspirasi_masuk.php?error=1');
    exit;
}

// Validasi skema tabel aspirasi
if (!aspirasi_schema_ready($pdo)) {
    die('Database aspirasi belum siap. Jalankan skrip update database terlebih dahulu.');
}

// Menjalankan query update menggunakan PDO
try {
    $stmt = $pdo->prepare("
        UPDATE aspirasi
        SET status = ?
        WHERE id_aspirasi = ?
        LIMIT 1
    ");

    $ok = $stmt->execute([$status, $id]);

    if ($ok) {
        redirect_back('aspirasi_masuk.php?updated=1');
    }
} catch (PDOException $e) {
    die('Gagal update status aspirasi: ' . htmlspecialchars($e->getMessage()));
}
?>
<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../include/components.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$status = $_GET['status'] ?? '';

$validStatus = ['proses', 'selesai', 'ditolak'];

function redirect_back($fallback = 'kelola_aspirasi.php') {
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
    header('Location: kelola_aspirasi.php?error=1');
    exit;
}

if (!aspirasi_schema_ready($conn)) {
    die('Database aspirasi belum siap. Jalankan aspirasi-update.sql terlebih dahulu.');
}

$stmt = mysqli_prepare($conn, "
    UPDATE aspirasi
    SET status = ?
    WHERE id_aspirasi = ?
    LIMIT 1
");

if (!$stmt) {
    die('Prepare update status aspirasi gagal: ' . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, 'si', $status, $id);

$ok = mysqli_stmt_execute($stmt);
$error = mysqli_stmt_error($stmt);

mysqli_stmt_close($stmt);

if ($ok) {
    redirect_back('kelola_aspirasi.php?updated=1');
}

die('Gagal update status aspirasi: ' . h($error));
?>
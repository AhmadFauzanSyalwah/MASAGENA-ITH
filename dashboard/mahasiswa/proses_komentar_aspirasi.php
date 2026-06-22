<?php
require_once __DIR__ . '/../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: aspirasi_saya.php');
    exit;
}

$idAspirasi = (int)($_POST['id_aspirasi'] ?? 0);
$isi = trim($_POST['isi_komentar'] ?? '');

if ($idAspirasi <= 0 || $isi === '') {
    die('Komentar tidak valid');
}

$level = 'mahasiswa';

$stmt = mysqli_prepare($conn, "
    INSERT INTO komentar_aspirasi
    (id_aspirasi, level_user, isi_komentar, tanggal)
    VALUES (?, ?, ?, NOW())
");

mysqli_stmt_bind_param(
    $stmt,
    'iss',
    $idAspirasi,
    $level,
    $isi
);

if (!mysqli_stmt_execute($stmt)) {
    die(mysqli_stmt_error($stmt));
}

mysqli_stmt_close($stmt);

header(
    "Location: detail_aspirasi.php?id=$idAspirasi"
);
exit;
?>
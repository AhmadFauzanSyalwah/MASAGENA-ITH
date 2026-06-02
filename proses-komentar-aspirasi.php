<?php
include 'connection.php';
include 'components.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: kelola-aspirasi.php');
    exit;
}

$idAspirasi = isset($_POST['id_aspirasi']) ? (int) $_POST['id_aspirasi'] : 0;
$levelUser = $_POST['level_user'] ?? 'admin';
$isiKomentar = trim($_POST['isi_komentar'] ?? '');

if ($idAspirasi <= 0 || $isiKomentar === '') {
    die('Data tanggapan tidak lengkap.');
}

if (!in_array($levelUser, ['admin', 'mahasiswa'], true)) {
    $levelUser = 'admin';
}

$idUser = 1;
if ($levelUser === 'mahasiswa') {
    $mahasiswa = active_mahasiswa($conn);
    if ($mahasiswa) {
        $idUser = (int) $mahasiswa['id_mahasiswa'];
    }
}

$stmt = mysqli_prepare($conn, "
    INSERT INTO komentar (id_aspirasi, id_user, level_user, isi_komentar)
    VALUES (?, ?, ?, ?)
");
mysqli_stmt_bind_param($stmt, 'iiss', $idAspirasi, $idUser, $levelUser, $isiKomentar);
$ok = mysqli_stmt_execute($stmt);
$error = mysqli_stmt_error($stmt);
mysqli_stmt_close($stmt);

if ($ok) {
    header('Location: detail-aspirasi.php?id=' . $idAspirasi);
    exit;
}

die('Gagal menyimpan tanggapan: ' . h($error));
?>

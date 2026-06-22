<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../include/components.php';
require_once __DIR__ . '/../../include/pendaftaran-helper.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: kegiatan.php');
    exit;
}

function back_with_error($message, $id_konten = 0) {
    $target = 'form_pendaftaran_kegiatan.php';

    $params = [
        'error' => $message
    ];

    if ($id_konten > 0) {
        $params['id_konten'] = (int) $id_konten;
    }

    header('Location: ' . $target . '?' . http_build_query($params));
    exit;
}

$id_konten = isset($_POST['id_konten']) ? (int) $_POST['id_konten'] : 0;
$nim = trim($_POST['nim'] ?? '');
$email = trim($_POST['email'] ?? '');
$defaultKuota = (int) pendaftaran_default_kuota();

if ($id_konten <= 0) {
    back_with_error('Kegiatan wajib dipilih.', $id_konten);
}

if ($nim === '' || $email === '') {
    back_with_error('NIM dan email wajib diisi.', $id_konten);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    back_with_error('Format email tidak valid.', $id_konten);
}

mysqli_begin_transaction($conn);

try {
    $stmtMhs = mysqli_prepare($conn, "
        SELECT id_mahasiswa, nim, nama, email
        FROM tbmahasiswa
        WHERE nim = ?
        AND email = ?
        LIMIT 1
    ");

    if (!$stmtMhs) {
        throw new Exception('Prepare data mahasiswa gagal: ' . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmtMhs, 'ss', $nim, $email);
    mysqli_stmt_execute($stmtMhs);

    $resMhs = mysqli_stmt_get_result($stmtMhs);
    $mahasiswa = mysqli_fetch_assoc($resMhs);

    mysqli_stmt_close($stmtMhs);

    if (!$mahasiswa) {
        throw new Exception('NIM dan email tidak ditemukan di data mahasiswa.');
    }

    $id_mahasiswa = (int) $mahasiswa['id_mahasiswa'];

    $stmtKegiatan = mysqli_prepare($conn, "
        SELECT id_konten, judul
        FROM konten_kegiatan
        WHERE id_konten = ?
        AND status_publikasi = 'publish'
        LIMIT 1
        FOR UPDATE
    ");

    if (!$stmtKegiatan) {
        throw new Exception('Prepare kegiatan gagal: ' . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmtKegiatan, 'i', $id_konten);
    mysqli_stmt_execute($stmtKegiatan);

    $resKegiatan = mysqli_stmt_get_result($stmtKegiatan);
    $kegiatan = mysqli_fetch_assoc($resKegiatan);

    mysqli_stmt_close($stmtKegiatan);

    if (!$kegiatan) {
        throw new Exception('Kegiatan tidak ditemukan atau belum publish.');
    }

    $stmtCek = mysqli_prepare($conn, "
        SELECT id_pendaftaran
        FROM pendaftaran
        WHERE id_mahasiswa = ?
        AND id_konten = ?
        LIMIT 1
    ");

    if (!$stmtCek) {
        throw new Exception('Prepare cek pendaftaran gagal: ' . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmtCek, 'ii', $id_mahasiswa, $id_konten);
    mysqli_stmt_execute($stmtCek);

    $resCek = mysqli_stmt_get_result($stmtCek);
    $sudahDaftar = mysqli_fetch_assoc($resCek);

    mysqli_stmt_close($stmtCek);

    if ($sudahDaftar) {
        throw new Exception('Kamu sudah mendaftar kegiatan ini.');
    }

    $stmtJumlah = mysqli_prepare($conn, "
        SELECT
            COUNT(*) AS total,
            COALESCE(MAX(NULLIF(kuota_maks, 0)), ?) AS kuota
        FROM pendaftaran
        WHERE id_konten = ?
        AND status_pendaftaran != 'ditolak'
    ");

    if (!$stmtJumlah) {
        throw new Exception('Prepare cek kuota gagal: ' . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmtJumlah, 'ii', $defaultKuota, $id_konten);
    mysqli_stmt_execute($stmtJumlah);

    $resJumlah = mysqli_stmt_get_result($stmtJumlah);
    $jumlahData = mysqli_fetch_assoc($resJumlah);

    mysqli_stmt_close($stmtJumlah);

    $kuota = (int) ($jumlahData['kuota'] ?? $defaultKuota);
    $totalPeserta = (int) ($jumlahData['total'] ?? 0);

    if ($kuota > 0 && $totalPeserta >= $kuota) {
        throw new Exception('Kuota kegiatan sudah penuh.');
    }

    $stmtInsert = mysqli_prepare($conn, "
        INSERT INTO pendaftaran
        (
            id_mahasiswa,
            id_konten,
            status_pendaftaran,
            kuota_maks
        )
        VALUES
        (
            ?,
            ?,
            'menunggu',
            ?
        )
    ");

    if (!$stmtInsert) {
        throw new Exception('Prepare simpan pendaftaran gagal: ' . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmtInsert, 'iii', $id_mahasiswa, $id_konten, $kuota);

    if (!mysqli_stmt_execute($stmtInsert)) {
        throw new Exception('Pendaftaran gagal disimpan: ' . mysqli_stmt_error($stmtInsert));
    }

    mysqli_stmt_close($stmtInsert);

    mysqli_commit($conn);

    header(
        'Location: cek_status_pendaftaran.php?nim=' .
        urlencode($nim) .
        '&email=' .
        urlencode($email) .
        '&success=1'
    );
    exit;

} catch (Exception $e) {
    mysqli_rollback($conn);
    back_with_error($e->getMessage(), $id_konten);
}
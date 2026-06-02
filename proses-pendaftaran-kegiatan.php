<?php
require_once 'connection.php';
require_once 'components.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: kegiatan.php');
    exit;
}

function back_with_error($message, $id_konten = 0) {
    $target = 'form-pendaftaran-kegiatan.php';
    if ($id_konten > 0) {
        $target .= '?id_konten=' . (int) $id_konten . '&error=' . urlencode($message);
    } else {
        $target .= '?error=' . urlencode($message);
    }
    header('Location: ' . $target);
    exit;
}

$id_konten = isset($_POST['id_konten']) ? (int) $_POST['id_konten'] : 0;
$nama_lengkap = trim($_POST['nama_lengkap'] ?? '');
$nim = trim($_POST['nim'] ?? '');
$program_studi = trim($_POST['program_studi'] ?? '');
$no_hp = trim($_POST['no_hp'] ?? '');
$email = trim($_POST['email'] ?? '');
$catatan_tambahan = trim($_POST['catatan_tambahan'] ?? '');

if ($id_konten <= 0) {
    back_with_error('Kegiatan wajib dipilih.', $id_konten);
}

if ($nama_lengkap === '' || $nim === '' || $program_studi === '' || $no_hp === '' || $email === '') {
    back_with_error('Semua data wajib diisi kecuali catatan tambahan.', $id_konten);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    back_with_error('Format email tidak valid.', $id_konten);
}

$id_mahasiswa = null;

if (!empty($_SESSION['id_mahasiswa'])) {
    $id_mahasiswa = (int) $_SESSION['id_mahasiswa'];
} else {
    $stmtMhs = mysqli_prepare($conn, "SELECT id_mahasiswa FROM tbmahasiswa WHERE nim = ? LIMIT 1");
    mysqli_stmt_bind_param($stmtMhs, 's', $nim);
    mysqli_stmt_execute($stmtMhs);
    $resMhs = mysqli_stmt_get_result($stmtMhs);
    $mhs = mysqli_fetch_assoc($resMhs);
    mysqli_stmt_close($stmtMhs);

    if ($mhs) {
        $id_mahasiswa = (int) $mhs['id_mahasiswa'];
    }
}

mysqli_begin_transaction($conn);

try {
    // Cek kegiatan dan kunci baris agar pengecekan kuota lebih aman.
    $stmtKegiatan = mysqli_prepare($conn, "SELECT id_konten, kuota FROM konten_kegiatan WHERE id_konten = ? LIMIT 1 FOR UPDATE");
    mysqli_stmt_bind_param($stmtKegiatan, 'i', $id_konten);
    mysqli_stmt_execute($stmtKegiatan);
    $resKegiatan = mysqli_stmt_get_result($stmtKegiatan);
    $kegiatan = mysqli_fetch_assoc($resKegiatan);
    mysqli_stmt_close($stmtKegiatan);

    if (!$kegiatan) {
        throw new Exception('Kegiatan tidak ditemukan.');
    }

    // Cegah daftar ganda berdasarkan NIM dan kegiatan.
    $stmtCek = mysqli_prepare($conn, "SELECT id_pendaftaran_kegiatan FROM pendaftaran_kegiatan WHERE nim = ? AND id_konten = ? LIMIT 1");
    mysqli_stmt_bind_param($stmtCek, 'si', $nim, $id_konten);
    mysqli_stmt_execute($stmtCek);
    $resCek = mysqli_stmt_get_result($stmtCek);
    $sudahDaftar = mysqli_fetch_assoc($resCek);
    mysqli_stmt_close($stmtCek);

    if ($sudahDaftar) {
        throw new Exception('Kamu sudah mendaftar kegiatan ini.');
    }

    // Hitung peserta yang belum ditolak.
    $stmtJumlah = mysqli_prepare($conn, "
        SELECT COUNT(*) AS total
        FROM pendaftaran_kegiatan
        WHERE id_konten = ?
        AND status_pendaftaran != 'ditolak'
    ");
    mysqli_stmt_bind_param($stmtJumlah, 'i', $id_konten);
    mysqli_stmt_execute($stmtJumlah);
    $resJumlah = mysqli_stmt_get_result($stmtJumlah);
    $jumlahData = mysqli_fetch_assoc($resJumlah);
    mysqli_stmt_close($stmtJumlah);

    $kuota = (int) $kegiatan['kuota'];
    $totalPeserta = (int) ($jumlahData['total'] ?? 0);

    if ($kuota > 0 && $totalPeserta >= $kuota) {
        throw new Exception('Kuota kegiatan sudah penuh.');
    }

    $stmtInsert = mysqli_prepare($conn, "
        INSERT INTO pendaftaran_kegiatan
        (id_mahasiswa, id_konten, nama_lengkap, nim, program_studi, no_hp, email, catatan_tambahan, status_pendaftaran)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')
    ");
    mysqli_stmt_bind_param(
        $stmtInsert,
        'iissssss',
        $id_mahasiswa,
        $id_konten,
        $nama_lengkap,
        $nim,
        $program_studi,
        $no_hp,
        $email,
        $catatan_tambahan
    );

    if (!mysqli_stmt_execute($stmtInsert)) {
        throw new Exception('Pendaftaran gagal disimpan.');
    }
    mysqli_stmt_close($stmtInsert);

    mysqli_commit($conn);

    header('Location: cek-status-pendaftaran.php?nim=' . urlencode($nim) . '&email=' . urlencode($email) . '&success=1');
    exit;
} catch (Exception $e) {
    mysqli_rollback($conn);
    back_with_error($e->getMessage(), $id_konten);
}
?>

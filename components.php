<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function h($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function rupiah_date($date) {
    if (empty($date) || $date === '0000-00-00' || $date === '0000-00-00 00:00:00') {
        return '-';
    }

    $bulan = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];

    $timestamp = strtotime($date);
    if (!$timestamp) {
        return '-';
    }

    $d = (int) date('j', $timestamp);
    $m = (int) date('n', $timestamp);
    $y = date('Y', $timestamp);

    return $d . ' ' . $bulan[$m] . ' ' . $y;
}

function tanggal_indo($date) {
    if (empty($date) || $date === '0000-00-00' || $date === '0000-00-00 00:00:00') {
        return '-';
    }

    $timestamp = strtotime($date);
    if (!$timestamp) {
        return '-';
    }

    return rupiah_date($date) . ' ' . date('H:i', $timestamp);
}

function short_text($text, $limit = 95) {
    $plain = trim(strip_tags((string) $text));

    if (function_exists('mb_strlen')) {
        if (mb_strlen($plain) <= $limit) {
            return $plain;
        }
        return mb_substr($plain, 0, $limit) . '...';
    }

    if (strlen($plain) <= $limit) {
        return $plain;
    }
    return substr($plain, 0, $limit) . '...';
}

function table_column_exists($conn, $table, $column) {
    $table = mysqli_real_escape_string($conn, $table);
    $column = mysqli_real_escape_string($conn, $column);
    $result = mysqli_query($conn, "SHOW COLUMNS FROM `$table` LIKE '$column'");
    return $result && mysqli_num_rows($result) > 0;
}

function aspirasi_schema_ready($conn) {
    return table_column_exists($conn, 'aspirasi', 'kode_aspirasi')
        && table_column_exists($conn, 'aspirasi', 'id_organisasi')
        && table_column_exists($conn, 'aspirasi', 'is_anonim');
}

function schema_warning() {
    ?>
    <div class="alert danger">
        <strong>Database aspirasi belum siap.</strong><br>
        Jalankan file <code>aspirasi-update.sql</code> di phpMyAdmin terlebih dahulu. File itu menambah kolom
        <code>kode_aspirasi</code>, <code>id_organisasi</code>, dan <code>is_anonim</code> agar fitur anonim dan cek status bisa berjalan.
    </div>
    <?php
}

function current_mahasiswa($conn) {
    if (empty($_SESSION['id_mahasiswa'])) {
        return null;
    }

    $id = (int) $_SESSION['id_mahasiswa'];
    $stmt = mysqli_prepare($conn, "SELECT id_mahasiswa, nim, nama, email FROM tbmahasiswa WHERE id_mahasiswa = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $data = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    return $data ?: null;
}

function demo_mahasiswa($conn) {
    $result = mysqli_query($conn, "SELECT id_mahasiswa, nim, nama, email FROM tbmahasiswa ORDER BY id_mahasiswa ASC LIMIT 1");
    if ($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }
    return null;
}

function active_mahasiswa($conn) {
    $mahasiswa = current_mahasiswa($conn);
    if ($mahasiswa) {
        return $mahasiswa;
    }
    return demo_mahasiswa($conn);
}

function render_header($title = 'MASAGENA-ITH', $active = 'kegiatan') {
    $aspirasiPage = in_array($active, ['aspirasi', 'status', 'kelola'], true);
    $cssFile = $aspirasiPage ? 'assets/css/aspirasi.css' : 'assets/css/pendaftaran-kegiatan.css';

    $menu = [
        'beranda' => ['label' => 'BERANDA', 'url' => 'index.php'],
        'kegiatan' => ['label' => 'KEGIATAN', 'url' => 'kegiatan.php'],
        'pengumuman' => ['label' => 'PENGUMUMAN', 'url' => 'kegiatan.php'],
        'aspirasi' => ['label' => 'ASPIRASI', 'url' => 'aspirasi.php'],
        'status' => ['label' => 'CEK STATUS', 'url' => $aspirasiPage ? 'cek-status-aspirasi.php' : 'cek-status-pendaftaran.php'],
        'kelola' => ['label' => 'KELOLA', 'url' => $aspirasiPage ? 'kelola-aspirasi.php' : 'kelola-pendaftaran-kegiatan.php'],
    ];
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= h($title); ?></title>
        <link rel="stylesheet" href="<?= h($cssFile); ?>">
    </head>
    <body>
        <header class="site-header">
            <div class="brand-area">
                <?php if (file_exists(__DIR__ . '/assets/img/logo-1.png')) { ?>
                    <img class="brand-logo-img" src="assets/img/logo-1.png" alt="Logo 1">
                <?php } else { ?>
                    <div class="brand-logo-placeholder">Logo 1</div>
                <?php } ?>

                <?php if (file_exists(__DIR__ . '/assets/img/logo-2.png')) { ?>
                    <img class="brand-logo-img" src="assets/img/logo-2.png" alt="Logo 2">
                <?php } else { ?>
                    <div class="brand-logo-placeholder">Logo 2</div>
                <?php } ?>

                <div>
                    <h1>MASAGENA-ITH</h1>
                    <p>MEDIA AKSES SEPUTAR AGENDA DAN KEGIATAN MAHASISWA</p>
                    <p>INSTITUT TEKNOLOGI BACHARUDDIN JUSUF HABIBIE</p>
                </div>
            </div>

            <nav class="nav-pill">
                <?php foreach ($menu as $key => $item) { ?>
                    <a class="<?= $active === $key ? 'active' : ''; ?>" href="<?= h($item['url']); ?>">
                        <?= h($item['label']); ?>
                    </a>
                <?php } ?>
            </nav>
        </header>
    <?php
}

function render_footer() {
    ?>
    </body>
    </html>
    <?php
}

function status_badge($status) {
    $status = strtolower((string) $status);
    $class = 'badge-pending';

    if ($status === 'diterima') {
        $class = 'badge-accepted';
    } elseif ($status === 'ditolak') {
        $class = 'badge-rejected';
    }

    return '<span class="status-badge ' . $class . '">' . h(strtoupper($status)) . '</span>';
}

function status_aspirasi_badge($status) {
    $status = strtolower((string) $status);
    $class = 'badge-process';

    if ($status === 'selesai') {
        $class = 'badge-done';
    } elseif ($status === 'ditolak') {
        $class = 'badge-rejected';
    }

    return '<span class="status-badge ' . $class . '">' . h(strtoupper($status)) . '</span>';
}

function generate_kode_aspirasi() {
    return 'ASP-' . date('ymdHis') . '-' . random_int(10, 99);
}
?>

<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../include/components.php';

$schemaReady = aspirasi_schema_ready($conn);
$statusFilter = $_GET['status'] ?? '';
$kategoriFilter = $_GET['kategori'] ?? '';

$statusValid = ['proses', 'selesai', 'ditolak'];
$kategoriValid = ['Kritik', 'Saran', 'Keluhan', 'Apresiasi', 'Lainnya'];

$where = [];
$params = [];
$types = '';

if ($statusFilter !== '' && in_array($statusFilter, $statusValid, true)) {
    $where[] = 'aspirasi.status = ?';
    $params[] = $statusFilter;
    $types .= 's';
}

if ($kategoriFilter !== '' && in_array($kategoriFilter, $kategoriValid, true)) {
    $where[] = 'aspirasi.kategori = ?';
    $params[] = $kategoriFilter;
    $types .= 's';
}

$sqlWhere = count($where) ? 'WHERE ' . implode(' AND ', $where) : '';
$rows = null;

$stat = [
    'proses' => 0,
    'selesai' => 0,
    'ditolak' => 0,
];

if ($schemaReady) {
    $sql = "
        SELECT
            aspirasi.*,
            organisasi.nama_organisasi,
            tbmahasiswa.nama AS nama_mahasiswa,
            tbmahasiswa.nim
        FROM aspirasi
        LEFT JOIN organisasi 
            ON aspirasi.id_organisasi = organisasi.id_organisasi
        LEFT JOIN tbmahasiswa 
            ON aspirasi.id_mahasiswa = tbmahasiswa.id_mahasiswa
        $sqlWhere
        ORDER BY aspirasi.tanggal DESC
    ";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        die('Prepare kelola aspirasi gagal: ' . mysqli_error($conn));
    }

    if (!empty($params)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }

    mysqli_stmt_execute($stmt);
    $rows = mysqli_stmt_get_result($stmt);
    mysqli_stmt_close($stmt);

    $statResult = mysqli_query($conn, "
        SELECT status, COUNT(*) AS total 
        FROM aspirasi 
        GROUP BY status
    ");

    if ($statResult) {
        while ($s = mysqli_fetch_assoc($statResult)) {
            if (isset($stat[$s['status']])) {
                $stat[$s['status']] = (int) $s['total'];
            }
        }
    }
}

require_once __DIR__ . '/../../include/header.php';
?>

<div class="page-light">
    <section class="section-heading-row">
        <div>
            <h2>Kelola Aspirasi</h2>
            <div class="heading-line"></div>
            <p class="lead-text">
                Halaman pengurus/admin untuk memantau aspirasi dan mengubah status tindak lanjut.
            </p>
        </div>

        <div class="heading-actions">
            <a href="../../dashboard/mahasiswa/aspirasi.php" class="btn-heading">Form Aspirasi</a>
        </div>
    </section>

    <?php if (!$schemaReady) { ?>
        <?php schema_warning(); ?>
    <?php } ?>

    <section class="stats-grid">
        <div class="stat-card">
            <span>Proses</span>
            <strong><?= (int) $stat['proses']; ?></strong>
        </div>

        <div class="stat-card">
            <span>Selesai</span>
            <strong><?= (int) $stat['selesai']; ?></strong>
        </div>

        <div class="stat-card">
            <span>Ditolak</span>
            <strong><?= (int) $stat['ditolak']; ?></strong>
        </div>
    </section>

    <section class="filter-card">
        <form method="GET" action="kelola_aspirasi.php">
            <select name="status">
                <option value="">Semua Status</option>

                <option value="proses" <?= $statusFilter === 'proses' ? 'selected' : ''; ?>>
                    Proses
                </option>

                <option value="selesai" <?= $statusFilter === 'selesai' ? 'selected' : ''; ?>>
                    Selesai
                </option>

                <option value="ditolak" <?= $statusFilter === 'ditolak' ? 'selected' : ''; ?>>
                    Ditolak
                </option>
            </select>

            <select name="kategori">
                <option value="">Semua Kategori</option>

                <?php foreach ($kategoriValid as $kat) { ?>
                    <option value="<?= h($kat); ?>" <?= $kategoriFilter === $kat ? 'selected' : ''; ?>>
                        <?= h($kat); ?>
                    </option>
                <?php } ?>
            </select>

            <button type="submit">Filter</button>
            <a href="kelola_aspirasi.php">Reset</a>
        </form>
    </section>

    <section class="table-card">
        <table>
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Judul</th>
                    <th>Kategori</th>
                    <th>Tujuan</th>
                    <th>Pengirim</th>
                    <th>Tanggal</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                <?php if ($rows && mysqli_num_rows($rows) > 0) { ?>
                    <?php while ($row = mysqli_fetch_assoc($rows)) { ?>
                        <tr>
                            <td>
                                <span class="code-chip small">
                                    <?= h($row['kode_aspirasi'] ?? '-'); ?>
                                </span>
                            </td>

                            <td>
                                <strong><?= h($row['judul'] ?? '-'); ?></strong><br>
                                <small><?= h(short_text($row['isi_aspirasi'] ?? '', 80)); ?></small>
                            </td>

                            <td><?= h($row['kategori'] ?? '-'); ?></td>

                            <td><?= h($row['nama_organisasi'] ?: 'Umum'); ?></td>

                            <td>
                                <?php if ((int) ($row['is_anonim'] ?? 0) === 1) { ?>
                                    <span class="anon-chip">Anonim</span>
                                <?php } else { ?>
                                    <?= h($row['nama_mahasiswa'] ?: 'Mahasiswa'); ?><br>
                                    <small><?= h($row['nim'] ?: '-'); ?></small>
                                <?php } ?>
                            </td>

                            <td><?= h(tanggal_indo($row['tanggal'] ?? '')); ?></td>

                            <td>
                                <?= status_aspirasi_badge($row['status'] ?? 'proses'); ?>
                            </td>

                            <td class="action-cell">
                                <a href="detail_aspirasi.php?id=<?= (int) $row['id_aspirasi']; ?>">
                                    Detail
                                </a>

                                <a href="update_status_aspirasi.php?id=<?= (int) $row['id_aspirasi']; ?>&status=proses">
                                    Proses
                                </a>

                                <a href="update_status_aspirasi.php?id=<?= (int) $row['id_aspirasi']; ?>&status=selesai">
                                    Selesai
                                </a>

                                <a 
                                    class="danger-link" 
                                    href="update_status_aspirasi.php?id=<?= (int) $row['id_aspirasi']; ?>&status=ditolak"
                                    onclick="return confirm('Yakin ingin menolak aspirasi ini?');"
                                >
                                    Tolak
                                </a>
                            </td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr>
                        <td colspan="8" class="empty-cell">
                            Belum ada data aspirasi.
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </section>
</div>

<?php
require_once __DIR__ . '/../../include/footer.php';
?>
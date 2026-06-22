<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../include/components.php';

$schemaReady = aspirasi_schema_ready($conn);
$mahasiswa = active_mahasiswa($conn);
$rows = null;

if ($schemaReady && $mahasiswa) {
    $idMahasiswa = (int) $mahasiswa['id_mahasiswa'];

    $stmt = mysqli_prepare($conn, "
        SELECT 
            aspirasi.*, 
            organisasi.nama_organisasi
        FROM aspirasi
        LEFT JOIN organisasi 
            ON aspirasi.id_organisasi = organisasi.id_organisasi
        WHERE aspirasi.id_mahasiswa = ?
        ORDER BY aspirasi.tanggal DESC
    ");

    if (!$stmt) {
        die('Prepare aspirasi saya gagal: ' . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, 'i', $idMahasiswa);
    mysqli_stmt_execute($stmt);

    $rows = mysqli_stmt_get_result($stmt);

    mysqli_stmt_close($stmt);
}

require_once __DIR__ . '/../../include/header.php';
?>

<div class="page-light">
    <section class="section-heading-row">
        <div>
            <h2>Aspirasi Saya</h2>
            <div class="heading-line"></div>
            <p class="lead-text">Daftar aspirasi non-anonim milik mahasiswa aktif.</p>
        </div>

        <div class="heading-actions">
            <a href="aspirasi.php" class="btn-heading">Kirim Aspirasi</a>
            <a href="cek_status_aspirasi.php" class="btn-heading btn-status">Cek Kode Aspirasi</a>
        </div>
    </section>

    <?php if (!$schemaReady) { ?>
        <?php schema_warning(); ?>
    <?php } ?>

    <?php if ($mahasiswa) { ?>
        <div class="alert neutral">
            Mode mahasiswa:
            <strong><?= h($mahasiswa['nama']); ?></strong>
            — <?= h($mahasiswa['nim']); ?>.
            Aspirasi anonim tidak muncul di halaman ini. Cek aspirasi anonim memakai kode aspirasi.
        </div>
    <?php } else { ?>
        <div class="alert danger">
            Data mahasiswa tidak ditemukan. Pastikan data mahasiswa tersedia di tabel <strong>tbmahasiswa</strong>.
        </div>
    <?php } ?>

    <section class="table-card">
        <table>
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Judul</th>
                    <th>Kategori</th>
                    <th>Tujuan</th>
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

                            <td><?= h($row['judul'] ?? '-'); ?></td>
                            <td><?= h($row['kategori'] ?? '-'); ?></td>
                            <td><?= h($row['nama_organisasi'] ?: 'Umum'); ?></td>
                            <td><?= h(tanggal_indo($row['tanggal'] ?? '')); ?></td>
                            <td><?= status_aspirasi_badge($row['status'] ?? 'proses'); ?></td>

                            <td>
                                <a class="table-link" href="detail_aspirasi.php?id=<?= (int) $row['id_aspirasi']; ?>">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr>
                        <td colspan="7" class="empty-cell">
                            Belum ada aspirasi non-anonim.
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
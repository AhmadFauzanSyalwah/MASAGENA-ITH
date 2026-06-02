<?php
include 'connection.php';
include 'components.php';

render_header('Cek Status Aspirasi', 'status');
$schemaReady = aspirasi_schema_ready($conn);
$kode = trim($_GET['kode'] ?? $_POST['kode_aspirasi'] ?? '');
$success = isset($_GET['success']);
$data = null;
$komentar = null;

if ($schemaReady && $kode !== '') {
    $stmt = mysqli_prepare($conn, "
        SELECT
            aspirasi.*,
            organisasi.nama_organisasi,
            tbmahasiswa.nama AS nama_mahasiswa,
            tbmahasiswa.nim,
            tbmahasiswa.email
        FROM aspirasi
        LEFT JOIN organisasi ON aspirasi.id_organisasi = organisasi.id_organisasi
        LEFT JOIN tbmahasiswa ON aspirasi.id_mahasiswa = tbmahasiswa.id_mahasiswa
        WHERE aspirasi.kode_aspirasi = ?
        LIMIT 1
    ");
    mysqli_stmt_bind_param($stmt, 's', $kode);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $data = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if ($data) {
        $idAspirasi = (int) $data['id_aspirasi'];
        $stmtKomentar = mysqli_prepare($conn, "
            SELECT * FROM komentar
            WHERE id_aspirasi = ?
            ORDER BY tanggal ASC
        ");
        mysqli_stmt_bind_param($stmtKomentar, 'i', $idAspirasi);
        mysqli_stmt_execute($stmtKomentar);
        $komentar = mysqli_stmt_get_result($stmtKomentar);
        mysqli_stmt_close($stmtKomentar);
    }
}
?>

<main class="page-light">
    <section class="section-heading-row">
        <div>
            <h2>Cek Status Aspirasi</h2>
            <div class="heading-line"></div>
            <p class="lead-text">Masukkan kode aspirasi untuk melihat status tindak lanjut.</p>
        </div>
        <div class="heading-actions">
            <a href="aspirasi.php" class="btn-heading">Kirim Aspirasi Baru</a>
        </div>
    </section>

    <?php if (!$schemaReady) { schema_warning(); } ?>

    <?php if ($success && $kode !== '') { ?>
        <div class="alert success">
            Aspirasi berhasil dikirim. Simpan kode ini untuk mengecek status: <strong><?= h($kode); ?></strong>
        </div>
    <?php } ?>

    <section class="status-layout">
        <form class="status-card" method="POST">
            <label>Kode Aspirasi</label>
            <input type="text" name="kode_aspirasi" value="<?= h($kode); ?>" placeholder="Contoh: ASP-260602101530-42" required>
            <button type="submit" class="btn-submit" <?= !$schemaReady ? 'disabled' : ''; ?>>Cek Status</button>
        </form>

        <?php if ($schemaReady && $kode !== '' && !$data) { ?>
            <div class="alert danger">Data aspirasi dengan kode tersebut tidak ditemukan.</div>
        <?php } ?>

        <?php if ($data) { ?>
            <article class="detail-card">
                <div class="detail-top">
                    <div>
                        <span class="code-chip"><?= h($data['kode_aspirasi']); ?></span>
                        <h3><?= h($data['judul']); ?></h3>
                    </div>
                    <?= status_aspirasi_badge($data['status']); ?>
                </div>

                <div class="meta-grid">
                    <div><span>Kategori</span><strong><?= h($data['kategori']); ?></strong></div>
                    <div><span>Tujuan</span><strong><?= h($data['nama_organisasi'] ?: 'Umum / Semua Organisasi'); ?></strong></div>
                    <div><span>Pengirim</span><strong><?= ((int) $data['is_anonim'] === 1) ? 'Anonim' : h($data['nama_mahasiswa'] ?: 'Mahasiswa'); ?></strong></div>
                    <div><span>Tanggal</span><strong><?= h(tanggal_indo($data['tanggal'])); ?></strong></div>
                </div>

                <div class="content-box">
                    <?= nl2br(h($data['isi_aspirasi'])); ?>
                </div>

                <h4>Tanggapan</h4>
                <?php if ($komentar && mysqli_num_rows($komentar) > 0) { ?>
                    <div class="comment-list">
                        <?php while ($row = mysqli_fetch_assoc($komentar)) { ?>
                            <div class="comment-item <?= $row['level_user'] === 'admin' ? 'admin-comment' : ''; ?>">
                                <div class="comment-head">
                                    <strong><?= $row['level_user'] === 'admin' ? 'Pengurus/Admin' : 'Mahasiswa'; ?></strong>
                                    <span><?= h(tanggal_indo($row['tanggal'])); ?></span>
                                </div>
                                <p><?= nl2br(h($row['isi_komentar'])); ?></p>
                            </div>
                        <?php } ?>
                    </div>
                <?php } else { ?>
                    <p class="empty-text">Belum ada tanggapan.</p>
                <?php } ?>

                <a class="btn-heading compact" href="detail-aspirasi.php?id=<?= (int) $data['id_aspirasi']; ?>">Lihat Detail</a>
            </article>
        <?php } ?>
    </section>
</main>

<?php render_footer(); ?>

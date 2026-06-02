<?php
include 'connection.php';
include 'components.php';

render_header('Detail Aspirasi', 'aspirasi');
$schemaReady = aspirasi_schema_ready($conn);
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$data = null;
$komentar = null;

if ($schemaReady && $id > 0) {
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
        WHERE aspirasi.id_aspirasi = ?
        LIMIT 1
    ");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $data = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if ($data) {
        $stmtKomentar = mysqli_prepare($conn, "SELECT * FROM komentar WHERE id_aspirasi = ? ORDER BY tanggal ASC");
        mysqli_stmt_bind_param($stmtKomentar, 'i', $id);
        mysqli_stmt_execute($stmtKomentar);
        $komentar = mysqli_stmt_get_result($stmtKomentar);
        mysqli_stmt_close($stmtKomentar);
    }
}
?>

<main class="page-light">
    <section class="section-heading-row">
        <div>
            <h2>Detail Aspirasi</h2>
            <div class="heading-line"></div>
        </div>
        <div class="heading-actions">
            <a href="kelola-aspirasi.php" class="btn-heading">Kelola Aspirasi</a>
            <a href="cek-status-aspirasi.php" class="btn-heading btn-status">Cek Status</a>
        </div>
    </section>

    <?php if (!$schemaReady) { schema_warning(); } ?>

    <?php if ($schemaReady && !$data) { ?>
        <div class="alert danger">Detail aspirasi tidak ditemukan.</div>
    <?php } ?>

    <?php if ($data) { ?>
        <section class="detail-card wide">
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

            <div class="content-box big">
                <?= nl2br(h($data['isi_aspirasi'])); ?>
            </div>

            <div class="status-actions">
                <a href="update-status-aspirasi.php?id=<?= (int) $data['id_aspirasi']; ?>&status=proses">Tandai Proses</a>
                <a href="update-status-aspirasi.php?id=<?= (int) $data['id_aspirasi']; ?>&status=selesai">Tandai Selesai</a>
                <a class="danger-link" href="update-status-aspirasi.php?id=<?= (int) $data['id_aspirasi']; ?>&status=ditolak">Tolak</a>
            </div>
        </section>

        <section class="comment-section">
            <div class="comment-panel">
                <h3>Tanggapan / Diskusi</h3>
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
            </div>

            <form class="comment-form" action="proses-komentar-aspirasi.php" method="POST">
                <input type="hidden" name="id_aspirasi" value="<?= (int) $data['id_aspirasi']; ?>">

                <label>Balas Sebagai</label>
                <select name="level_user" required>
                    <option value="admin">Pengurus/Admin</option>
                    <option value="mahasiswa">Mahasiswa</option>
                </select>

                <label>Isi Tanggapan</label>
                <textarea name="isi_komentar" rows="6" placeholder="Tulis tanggapan..." required></textarea>

                <button type="submit" class="btn-submit">Kirim Tanggapan</button>
            </form>
        </section>
    <?php } ?>
</main>

<?php render_footer(); ?>

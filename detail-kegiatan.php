<?php
require_once 'connection.php';
require_once 'components.php';

$id_konten = isset($_GET['id_konten']) ? (int) $_GET['id_konten'] : 0;

$stmt = mysqli_prepare($conn, "
    SELECT
        k.*,
        COUNT(p.id_pendaftaran_kegiatan) AS jumlah_peserta
    FROM konten_kegiatan k
    LEFT JOIN pendaftaran_kegiatan p
        ON p.id_konten = k.id_konten
        AND p.status_pendaftaran != 'ditolak'
    WHERE k.id_konten = ?
    GROUP BY k.id_konten
    LIMIT 1
");
mysqli_stmt_bind_param($stmt, 'i', $id_konten);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$kegiatan = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

render_header('Detail Kegiatan - MASAGENA-ITH', 'pengumuman');
?>

<main class="page-light">
    <?php if (!$kegiatan) { ?>
        <section class="detail-wrap">
            <div class="empty-state">
                <h2>Kegiatan tidak ditemukan</h2>
                <p>Data kegiatan tidak ada atau sudah dihapus.</p>
                <a href="kegiatan.php" class="btn-primary wide-small">Kembali</a>
            </div>
        </section>
    <?php } else { ?>
        <?php
            $kuota = (int) $kegiatan['kuota'];
            $jumlah = (int) $kegiatan['jumlah_peserta'];
            $penuh = $kuota > 0 && $jumlah >= $kuota;
        ?>

        <section class="detail-wrap">
            <article class="detail-card">
                <div class="detail-image">
                    <?php if (!empty($kegiatan['foto'])) { ?>
                        <img src="<?= h($kegiatan['foto']); ?>" alt="<?= h($kegiatan['judul_kegiatan']); ?>">
                    <?php } else { ?>
                        <div class="image-placeholder-large">MASAGENA-ITH</div>
                    <?php } ?>
                </div>

                <div class="detail-content">
                    <p class="eyebrow">Detail Kegiatan</p>
                    <h2><?= h($kegiatan['judul_kegiatan']); ?></h2>
                    <p class="detail-description"><?= nl2br(h($kegiatan['isi_kegiatan'])); ?></p>

                    <div class="detail-info-grid">
                        <div>
                            <span>Tanggal Kegiatan</span>
                            <strong><?= h(rupiah_date($kegiatan['tanggal_kegiatan'])); ?></strong>
                        </div>
                        <div>
                            <span>Lokasi</span>
                            <strong><?= h($kegiatan['lokasi'] ?: '-'); ?></strong>
                        </div>
                        <div>
                            <span>Kuota</span>
                            <strong><?= $jumlah; ?>/<?= $kuota; ?> peserta</strong>
                        </div>
                        <div>
                            <span>Status</span>
                            <strong><?= $penuh ? 'Kuota Penuh' : 'Pendaftaran Dibuka'; ?></strong>
                        </div>
                    </div>

                    <div class="detail-actions">
                        <a href="kegiatan.php" class="btn-outline wide-small">Kembali</a>
                        <?php if ($penuh) { ?>
                            <button class="btn-disabled wide-small" disabled>Kuota Penuh</button>
                        <?php } else { ?>
                            <a href="form-pendaftaran-kegiatan.php?id_konten=<?= (int) $kegiatan['id_konten']; ?>" class="btn-primary wide-small">Daftar Kegiatan</a>
                        <?php } ?>
                    </div>
                </div>
            </article>
        </section>
    <?php } ?>
</main>

<?php render_footer(); ?>

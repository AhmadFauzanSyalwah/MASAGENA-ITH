<?php
require_once 'connection.php';
require_once 'components.php';

render_header('Kegiatan Terbaru - MASAGENA-ITH', 'pengumuman');

$query = mysqli_query($conn, "
    SELECT
        k.id_konten,
        k.judul_kegiatan,
        k.isi_kegiatan,
        k.foto,
        k.tanggal_upload,
        k.tanggal_kegiatan,
        k.kuota,
        k.lokasi,
        COUNT(p.id_pendaftaran_kegiatan) AS jumlah_peserta
    FROM konten_kegiatan k
    LEFT JOIN pendaftaran_kegiatan p
        ON p.id_konten = k.id_konten
        AND p.status_pendaftaran != 'ditolak'
    GROUP BY k.id_konten
    ORDER BY k.tanggal_upload DESC
");
?>

<main class="page-light">
    <section class="section-heading section-heading-row">
        <div>
            <h2>Kegiatan Terbaru</h2>
            <div class="heading-line"></div>
        </div>

        <div class="heading-actions">
            <a href="kegiatan.php" class="btn-heading">Lihat Semua <span>→</span></a>
            <a href="cek-status-pendaftaran.php" class="btn-heading btn-status">Cek Status <span>→</span></a>
        </div>
    </section>

    <section class="card-grid">
        <?php if (!$query || mysqli_num_rows($query) === 0) { ?>
            <div class="empty-state">
                <h3>Belum ada kegiatan</h3>
                <p>Tambahkan data kegiatan di tabel <strong>konten_kegiatan</strong> terlebih dahulu.</p>
            </div>
        <?php } ?>

        <?php while ($row = mysqli_fetch_assoc($query)) { ?>
            <?php
                $kuota = (int) $row['kuota'];
                $jumlah = (int) $row['jumlah_peserta'];
                $penuh = $kuota > 0 && $jumlah >= $kuota;
                $foto = trim((string) $row['foto']);
            ?>

            <article class="activity-card <?= $penuh ? 'is-full' : ''; ?>">
                <div class="activity-image">
                    <?php if ($foto !== '') { ?>
                        <img src="<?= h($foto); ?>" alt="<?= h($row['judul_kegiatan']); ?>">
                    <?php } ?>
                </div>

                <div class="activity-body">
                    <h3><?= h($row['judul_kegiatan']); ?></h3>
                    <p><?= h(short_text($row['isi_kegiatan'], 90)); ?></p>

                    <div class="card-separator"></div>

                    <div class="activity-meta">
                        <div class="quota-text">
                            <span class="quota-icon">♙</span>
                            <?php if ($penuh) { ?>
                                <strong>KUOTA PENUH</strong>
                            <?php } else { ?>
                                <strong>Kuota: <?= $jumlah; ?>/<?= $kuota; ?></strong>
                            <?php } ?>
                        </div>
                        <span><?= h(rupiah_date($row['tanggal_kegiatan'])); ?></span>
                    </div>

                    <div class="activity-actions">
                        <a class="btn-outline" href="detail-kegiatan.php?id_konten=<?= (int) $row['id_konten']; ?>">Detail</a>

                        <?php if ($penuh) { ?>
                            <button class="btn-disabled" disabled>Daftar</button>
                        <?php } else { ?>
                            <a class="btn-primary" href="form-pendaftaran-kegiatan.php?id_konten=<?= (int) $row['id_konten']; ?>">Daftar</a>
                        <?php } ?>
                    </div>
                </div>
            </article>
        <?php } ?>
    </section>
</main>

<?php render_footer(); ?>

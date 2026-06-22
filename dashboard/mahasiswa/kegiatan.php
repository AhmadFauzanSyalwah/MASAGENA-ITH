<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../include/components.php';
require_once __DIR__ . '/../../include/pendaftaran-helper.php';

$defaultKuota = (int) pendaftaran_default_kuota();

$query = mysqli_query($conn, "
    SELECT
        k.id_konten,
        k.judul,
        k.deskripsi,
        k.lampiran,
        k.created_at,
        k.tanggal_kegiatan,
        k.kategori,
        COALESCE(
            (
                SELECT MAX(NULLIF(p2.kuota_maks, 0))
                FROM pendaftaran p2
                WHERE p2.id_konten = k.id_konten
            ),
            $defaultKuota
        ) AS kuota,
        (
            SELECT COUNT(*)
            FROM pendaftaran p
            WHERE p.id_konten = k.id_konten
            AND p.status_pendaftaran != 'ditolak'
        ) AS jumlah_peserta
    FROM konten_kegiatan k
    WHERE k.status_publikasi = 'publish'
    ORDER BY k.created_at DESC
");

if (!$query) {
    die('Query kegiatan gagal: ' . mysqli_error($conn));
}

require_once __DIR__ . '/../../include/header.php';
?>

<div class="page-light">
    <section class="section-heading section-heading-row">
        <div>
            <h2>Kegiatan Terbaru</h2>
            <div class="heading-line"></div>
        </div>

        <div class="heading-actions">
            <a href="kegiatan.php" class="btn-heading">Lihat Semua <span>→</span></a>
            <a href="cek_status_pendaftaran.php" class="btn-heading btn-status">Cek Status <span>→</span></a>
        </div>
    </section>

    <section class="card-grid">
        <?php if (mysqli_num_rows($query) === 0) { ?>
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
                $foto = pendaftaran_lampiran_src($row['lampiran']);
            ?>

            <article class="activity-card <?= $penuh ? 'is-full' : ''; ?>">
                <div class="activity-image">
                    <?php if ($foto !== '') { ?>

                        <img 
                            src="<?= h($foto); ?>" 
                            alt="<?= h($row['judul']); ?>"
                        >

                    <?php } else { ?>

                        <img 
                            class="activity-logo-placeholder"
                            src="<?= h(asset_url('img/logo-1.png')); ?>"
                            alt="MASAGENA-ITH"
                        >

                    <?php } ?>
                </div>

                <div class="activity-body">
                    <h3><?= h($row['judul']); ?></h3>
                    <p><?= h(short_text($row['deskripsi'], 90)); ?></p>

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
                        <a class="btn-outline" href="detail_kegiatan.php?id_konten=<?= (int) $row['id_konten']; ?>">Detail</a>

                        <?php if ($penuh) { ?>
                            <button class="btn-disabled" disabled>Daftar</button>
                        <?php } else { ?>
                            <a class="btn-primary" href="form_pendaftaran_kegiatan.php?id_konten=<?= (int) $row['id_konten']; ?>">Daftar</a>
                        <?php } ?>
                    </div>
                </div>
            </article>
        <?php } ?>
    </section>
</div>

<?php
require_once __DIR__ . '/../../include/footer.php';
?>
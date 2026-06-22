<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../include/components.php';
require_once __DIR__ . '/../../include/pendaftaran-helper.php';

$id_konten = isset($_GET['id_konten']) ? (int) $_GET['id_konten'] : 0;
$defaultKuota = (int) pendaftaran_default_kuota();
$kegiatan = null;

if ($id_konten > 0) {
    $stmt = mysqli_prepare($conn, "
        SELECT
            k.id_konten,
            k.judul,
            k.deskripsi,
            k.tanggal_kegiatan,
            k.kategori,
            k.lampiran,
            k.status_publikasi,
            k.created_at,
            COALESCE(
                (
                    SELECT MAX(NULLIF(p2.kuota_maks, 0))
                    FROM pendaftaran p2
                    WHERE p2.id_konten = k.id_konten
                ),
                ?
            ) AS kuota,
            (
                SELECT COUNT(*)
                FROM pendaftaran p
                WHERE p.id_konten = k.id_konten
                AND p.status_pendaftaran != 'ditolak'
            ) AS jumlah_peserta
        FROM konten_kegiatan k
        WHERE k.id_konten = ?
        AND k.status_publikasi = 'publish'
        LIMIT 1
    ");

    if (!$stmt) {
        die('Prepare detail kegiatan gagal: ' . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, 'ii', $defaultKuota, $id_konten);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $kegiatan = mysqli_fetch_assoc($result);

    mysqli_stmt_close($stmt);
}

require_once __DIR__ . '/../../include/header.php';
?>

<div class="page-light">
    <?php if (!$kegiatan) { ?>
        <section class="detail-wrap">
            <div class="empty-state">
                <h2>Kegiatan tidak ditemukan</h2>
                <p>Data kegiatan tidak ada, belum publish, atau sudah dihapus.</p>
                <a href="kegiatan.php" class="btn-primary wide-small">Kembali</a>
            </div>
        </section>
    <?php } else { ?>
        <?php
            $kuota = (int) $kegiatan['kuota'];
            $jumlah = (int) $kegiatan['jumlah_peserta'];
            $penuh = $kuota > 0 && $jumlah >= $kuota;
            $foto = pendaftaran_lampiran_src($kegiatan['lampiran']);
        ?>

        <section class="detail-wrap">
            <article class="detail-card">
                <div class="detail-image">
                    <?php if ($foto !== '') { ?>
                        <img src="<?= h($foto); ?>" alt="<?= h($kegiatan['judul']); ?>">
                    <?php } else { ?>
                        <div class="image-placeholder-large">MASAGENA-ITH</div>
                    <?php } ?>
                </div>

                <div class="detail-content">
                    <p class="eyebrow">Detail Kegiatan</p>

                    <h2><?= h($kegiatan['judul']); ?></h2>

                    <p class="detail-description">
                        <?= nl2br(h($kegiatan['deskripsi'])); ?>
                    </p>

                    <div class="detail-info-grid">
                        <div>
                            <span>Tanggal Kegiatan</span>
                            <strong><?= h(rupiah_date($kegiatan['tanggal_kegiatan'])); ?></strong>
                        </div>

                        <div>
                            <span>Kategori</span>
                            <strong><?= h($kegiatan['kategori'] ?: '-'); ?></strong>
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
                            <a 
                                href="form_pendaftaran_kegiatan.php?id_konten=<?= (int) $kegiatan['id_konten']; ?>" 
                                class="btn-primary wide-small"
                            >
                                Daftar Kegiatan
                            </a>
                        <?php } ?>
                    </div>
                </div>
            </article>
        </section>
    <?php } ?>
</div>

<?php
require_once __DIR__ . '/../../include/footer.php';
?>
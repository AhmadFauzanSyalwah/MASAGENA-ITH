<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../include/components.php';
require_once __DIR__ . '/../../include/pendaftaran-helper.php';
require_once '../../config/session_check.php';

$id_konten = isset($_GET['id_konten']) ? (int) $_GET['id_konten'] : 0;
$defaultKuota = (int) pendaftaran_default_kuota();
$kegiatan = null;

if ($id_konten > 0) {
    try {
        $stmt = $pdo->prepare("
            SELECT
                k.id_konten, k.judul, k.deskripsi, k.tanggal_kegiatan, 
                k.kategori, k.lampiran, k.status_publikasi, k.created_at,
                COALESCE((SELECT MAX(NULLIF(p2.kuota_maks, 0)) FROM pendaftaran p2 WHERE p2.id_konten = k.id_konten), ?) AS kuota,
                (SELECT COUNT(*) FROM pendaftaran p WHERE p.id_konten = k.id_konten AND p.status_pendaftaran != 'ditolak') AS jumlah_peserta
            FROM konten_kegiatan k
            WHERE k.id_konten = ? AND k.status_publikasi = 'publish'
            LIMIT 1
        ");
        $stmt->execute([$defaultKuota, $id_konten]);
        $kegiatan = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die('Query detail kegiatan gagal: ' . $e->getMessage());
    }
}

require_once __DIR__ . '/../../include/header.php';
?>

<div class="main-container">
    <div class="main-content">
        
        <?php if (!$kegiatan) { ?>
            <div class="card" style="text-align: center; padding: 4rem 2rem;">
                <h2 style="color: var(--danger); margin-bottom: 1rem;">Kegiatan Tidak Ditemukan</h2>
                <p style="color: var(--text-muted); margin-bottom: 2rem;">Data kegiatan tidak ada, belum dipublikasi, atau mungkin sudah dihapus.</p>
                <a href="kegiatan.php" class="btn">Kembali ke Daftar Kegiatan</a>
            </div>
        <?php } else { ?>
            <?php
                $kuota = (int) $kegiatan['kuota'];
                $jumlah = (int) $kegiatan['jumlah_peserta'];
                $penuh = $kuota > 0 && $jumlah >= $kuota;
                $foto = pendaftaran_lampiran_src($kegiatan['lampiran']);
            ?>
            
            <article class="card" style="max-width: 900px; margin: 0 auto; padding: 2.5rem;">
                
                <div style="width: 100%; max-height: 400px; overflow: hidden; border-radius: var(--radius); background-color: var(--bg-body); display: flex; align-items: center; justify-content: center; margin-bottom: 2rem;">
                    <?php if ($foto !== '') { ?>
                        <img src="<?= h($foto); ?>" alt="<?= h($kegiatan['judul']); ?>" style="width: 100%; height: 100%; object-fit: cover; border-radius: var(--radius);">
                    <?php } else { ?>
                        <div style="padding: 4rem; color: var(--text-muted); font-weight: bold; letter-spacing: 2px;">MASAGENA-ITH</div>
                    <?php } ?>
                </div>

                <p style="margin: 0 0 8px; color: var(--accent-dark, #f9ac18); font-size: 13px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px;">Detail Kegiatan</p>
                <h2 style="font-size: 2rem; margin-bottom: 1.5rem; line-height: 1.3; border-left: none; padding-left: 0;"><?= h($kegiatan['judul']); ?></h2>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; margin-bottom: 2.5rem;">
                    <div style="background: var(--bg-body); padding: 1.2rem; border-radius: 10px; border: 1px solid var(--border);">
                        <span style="display: block; font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700; margin-bottom: 5px;">Tanggal</span>
                        <strong style="color: var(--text-dark); font-size: 1.1rem;"><i class="fa fa-calendar" style="color: var(--primary);"></i> <?= h(rupiah_date($kegiatan['tanggal_kegiatan'])); ?></strong>
                    </div>
                    
                    <div style="background: var(--bg-body); padding: 1.2rem; border-radius: 10px; border: 1px solid var(--border);">
                        <span style="display: block; font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700; margin-bottom: 5px;">Kategori</span>
                        <strong style="color: var(--text-dark); font-size: 1.1rem;"><?= h($kegiatan['kategori'] ?: '-'); ?></strong>
                    </div>

                    <div style="background: var(--bg-body); padding: 1.2rem; border-radius: 10px; border: 1px solid var(--border);">
                        <span style="display: block; font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700; margin-bottom: 5px;">Kuota</span>
                        <strong style="color: var(--text-dark); font-size: 1.1rem;"><?= $jumlah; ?> / <?= $kuota; ?></strong>
                    </div>

                    <div style="background: var(--bg-body); padding: 1.2rem; border-radius: 10px; border: 1px solid var(--border);">
                        <span style="display: block; font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700; margin-bottom: 5px;">Status</span>
                        <?php if ($penuh) { ?>
                            <strong style="color: var(--danger); font-size: 1.1rem;">Kuota Penuh</strong>
                        <?php } else { ?>
                            <strong style="color: var(--success); font-size: 1.1rem;">Pendaftaran Dibuka</strong>
                        <?php } ?>
                    </div>
                </div>

                <div style="color: var(--text-dark); line-height: 1.8; font-size: 1.05rem; margin-bottom: 2.5rem;">
                    <?= nl2br(h($kegiatan['deskripsi'])); ?>
                </div>

                <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                    <a href="kegiatan.php" class="btn-cancel" style="padding: 12px 24px;">&larr; Kembali</a>
                    
                    <?php if ($penuh) { ?>
                        <button class="btn" style="background-color: var(--border); color: var(--text-muted); cursor: not-allowed; padding: 12px 24px;" disabled>Kuota Penuh</button>
                    <?php } else { ?>
                        <a href="form_pendaftaran_kegiatan.php?id_konten=<?= (int) $kegiatan['id_konten']; ?>" class="btn" style="padding: 12px 24px;">Daftar Kegiatan</a>
                    <?php } ?>
                </div>

            </article>
        <?php } ?>
        
    </div>
</div>

<?php require_once __DIR__ . '/../../include/footer.php'; ?>
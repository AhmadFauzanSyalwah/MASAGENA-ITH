<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../include/components.php';
require_once __DIR__ . '/../../include/pendaftaran-helper.php';
require_once '../../config/session_check.php';

$defaultKuota = (int) pendaftaran_default_kuota();

try {
    $stmt = $pdo->query("
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
} catch (PDOException $e) {
    die('Query kegiatan gagal: ' . $e->getMessage());
}

require_once __DIR__ . '/../../include/header.php';
?>

<div class="dashboard-welcome">
    <h1>Daftar Kegiatan</h1>
    <p>Jelajahi dan daftarkan diri Anda pada berbagai acara, seminar, kompetisi, serta kegiatan kemahasiswaan terbaru di lingkungan kampus.</p>
</div>

<div class="main-content">
    
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem;">
        <h2 style="margin-bottom: 0; font-size: 1.3rem; border-left: 4px solid var(--accent); padding-left: 0.75rem;">Kegiatan Terbaru</h2>
        
        <div style="display: flex; gap: 8px;">
            <a href="kegiatan.php" class="btn-sm" style="background-color: var(--primary); color: var(--white);">Lihat Semua &rarr;</a>
            <a href="cek_status_pendaftaran.php" class="btn-sm" style="background-color: var(--success); color: var(--white);">Cek Status &rarr;</a>
        </div>
    </div>

    <?php if ($stmt->rowCount() === 0) { ?>
        <div class="alert">
            <h3>Belum ada kegiatan</h3>
            <p style="margin:0;">Tambahkan data kegiatan di tabel <strong>konten_kegiatan</strong> terlebih dahulu.</p>
        </div>
    <?php } else { ?>
        
        <div class="organisasi-grid kegiatan-list">
            <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                <?php
                    $kuota = (int) $row['kuota'];
                    $jumlah = (int) $row['jumlah_peserta'];
                    $penuh = $kuota > 0 && $jumlah >= $kuota;
                    $foto = pendaftaran_lampiran_src($row['lampiran']);
                ?>

                <article class="card" style="display: flex; flex-direction: column; height: 100%; min-height: 100%; <?= $penuh ? 'opacity: 0.65;' : ''; ?>">
                    
                    <div style="margin: -1.5rem -1.5rem 1rem -1.5rem; height: 180px; overflow: hidden; border-radius: var(--radius) var(--radius) 0 0; background-color: var(--bg-body); display: flex; align-items: center; justify-content: center;">
                        <?php if ($foto !== '') { ?>
                            <img src="<?= h($foto); ?>" alt="<?= h($row['judul']); ?>" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.onerror=null; this.src='<?= h(asset_url('img/logo-1.png')); ?>'; this.style.height='60px'; this.style.objectFit='contain';">
                        <?php } else { ?>
                            <img src="<?= h(asset_url('img/logo-1.png')); ?>" alt="MASAGENA-ITH" style="height: 60px; object-fit: contain;">
                        <?php } ?>
                    </div>

                    <h3 style="font-size: 1.1rem; line-height: 1.3; margin-bottom: 0.5rem;">
                        <?= h($row['judul']); ?>
                    </h3>
                    
                    <div class="meta" style="display: flex; justify-content: space-between; font-weight: 600; margin-bottom: 1rem;">
                        <span style="color: var(--primary);"><i class="fa fa-calendar"></i> <?= h(rupiah_date($row['tanggal_kegiatan'])); ?></span>
                        
                        <?php if ($penuh) { ?>
                            <span style="color: var(--danger);">KUOTA PENUH</span>
                        <?php } else { ?>
                            <span style="color: var(--accent-dark);">Kuota: <?= $jumlah; ?>/<?= $kuota; ?></span>
                        <?php } ?>
                    </div>

                    <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 1.5rem; line-height: 1.5; flex-grow: 1;">
                        <?= h(short_text($row['deskripsi'], 90)); ?>
                    </p>

                    <div class="card-actions" style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; width: 100%;">
                        <a href="detail_kegiatan.php?id_konten=<?= (int) $row['id_konten']; ?>" class="btn-cancel" style="margin:0; text-align:center; padding: 0.5rem;">Detail</a>

                        <?php if ($penuh) { ?>
                            <button class="btn" style="background-color: var(--border); color: var(--text-muted); cursor: not-allowed; padding: 0.5rem;" disabled>Daftar</button>
                        <?php } else { ?>
                            <a href="form_pendaftaran_kegiatan.php?id_konten=<?= (int) $row['id_konten']; ?>" class="btn" style="text-align:center; padding: 0.5rem;">Daftar</a>
                        <?php } ?>
                    </div>

                </article>
            <?php } ?>
        </div>
    <?php } ?>
</div>

<?php
require_once __DIR__ . '/../../include/footer.php';
?>
<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../include/components.php';
require_once '../../config/session_check.php';

// Pastikan menggunakan $pdo sebagai koneksi
$schemaReady = aspirasi_schema_ready($pdo);
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$data = null;
$komentar = [];

if ($schemaReady && $id > 0) {
    try {
        // Query detail aspirasi dengan PDO
        $stmt = $pdo->prepare("
            SELECT
                a.*,
                o.nama_organisasi,
                m.nama AS nama_mahasiswa,
                m.nim,
                m.email
            FROM aspirasi a
            LEFT JOIN organisasi o ON a.id_organisasi = o.id_organisasi
            LEFT JOIN tbmahasiswa m ON a.id_mahasiswa = m.id_mahasiswa
            WHERE a.id_aspirasi = ?
            LIMIT 1
        ");
        $stmt->execute([$id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            // Query komentar dengan PDO
            $stmtKomentar = $pdo->prepare("
                SELECT *
                FROM komentar_aspirasi
                WHERE id_aspirasi = ?
                ORDER BY created_at ASC
            ");
            $stmtKomentar->execute([$id]);
            $komentar = $stmtKomentar->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (PDOException $e) {
        die('Query detail aspirasi gagal: ' . $e->getMessage());
    }
}

require_once __DIR__ . '/../../include/header.php';
?>

<style>
    /* Tambahan style khusus menyesuaikan variabel root style.css */
    .meta-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
        background-color: var(--bg-body);
        padding: 1.5rem;
        border-radius: var(--radius-sm);
        margin-bottom: 1.5rem;
    }
    .meta-grid span {
        display: block;
        font-size: 0.8rem;
        color: var(--text-muted);
        margin-bottom: 0.3rem;
    }
    .meta-grid strong {
        color: var(--primary);
        font-size: 1rem;
    }
    .kode-aspirasi {
        font-size: 0.8rem;
        background-color: rgba(255, 160, 7, 0.15);
        color: var(--accent-dark);
        padding: 0.3rem 0.8rem;
        border-radius: 20px;
        font-weight: 700;
        display: inline-block;
        margin-bottom: 0.5rem;
    }
    .komentar-item.admin-comment {
        border-left: 4px solid var(--success);
    }
    .komentar-item {
        border-left: 4px solid var(--primary);
    }
</style>

<!-- HEADER / WELCOME AREA (Sejajar dengan main-content seperti di organisasi.php) -->
<div class="dashboard-welcome" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1>Detail Aspirasi</h1>
        <p>Pantau rincian aspirasi, status, dan tanggapan terbaru.</p>
    </div>
    <div style="display: flex; gap: 0.5rem;">
        <a href="aspirasi_saya.php" class="btn" style="background-color: rgba(255, 255, 255, 0.2); color: var(--white); box-shadow: none;">Aspirasi Saya</a>
        <a href="cek_status_aspirasi.php" class="btn">Cek Status</a>
    </div>
</div>

<!-- MAIN CONTENT -->
<div class="main-content">

    <?php if (!$schemaReady) { schema_warning(); } ?>

    <?php if ($schemaReady && !$data) { ?>
        <div class="error">Detail aspirasi tidak ditemukan.</div>
    <?php } ?>

    <?php if ($data) { ?>
        
        <!-- DETAIL KONTEN -->
        <div class="detail-kegiatan" style="box-shadow: var(--shadow-sm); margin-bottom: 2rem; border: 1px solid var(--border);">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 1px solid var(--border); padding-bottom: 1.5rem; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
                <div>
                    <span class="kode-aspirasi"><?= h($data['kode_aspirasi'] ?? '-'); ?></span>
                    <h2 style="color: var(--primary); font-size: 1.6rem; margin-top: 0.5rem; margin-bottom: 0;"><?= h($data['judul'] ?? '-'); ?></h2>
                </div>
                <div>
                    <?= status_aspirasi_badge($data['status'] ?? 'proses'); ?>
                </div>
            </div>

            <div class="meta-grid">
                <div><span>Kategori</span><strong><?= h($data['kategori'] ?? '-'); ?></strong></div>
                <div><span>Tujuan</span><strong><?= h($data['nama_organisasi'] ?: 'Umum'); ?></strong></div>
                <div><span>Pengirim</span><strong><?= ((int) ($data['is_anonim'] ?? 0) === 1) ? 'Anonim' : h($data['nama_mahasiswa'] ?: 'Mahasiswa'); ?></strong></div>
                <div><span>Tanggal</span><strong><?= h(tanggal_indo($data['created_at'] ?? '')); ?></strong></div>
            </div>

            <div class="deskripsi" style="font-size: 1.05rem; color: var(--text-dark);">
                <?= nl2br(h($data['isi_aspirasi'] ?? '')); ?>
            </div>
        </div>

        <!-- TANGGAPAN / DISKUSI -->
        <div class="card" style="border: 1px solid var(--border);">
            <h3 style="border-left: 4px solid var(--accent); padding-left: 0.75rem; margin-bottom: 1.5rem; color: var(--primary);">Tanggapan & Diskusi</h3>
            
            <div class="komentar-section" style="margin-top: 0;">
                <?php if (!empty($komentar)) { ?>
                    <?php foreach ($komentar as $row) { ?>
                        <div class="komentar-item <?= ($row['level_user'] ?? '') === 'admin' ? 'admin-comment' : ''; ?>" style="background-color: var(--bg-body); padding: 1rem; border-radius: var(--radius-sm); margin-bottom: 1rem;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                <strong><?= ($row['level_user'] ?? '') === 'admin' ? 'Pengurus/Admin' : 'Mahasiswa'; ?></strong>
                                <span style="font-size: 0.8rem; color: var(--text-muted);"><?= h(tanggal_indo($row['created_at'] ?? '')); ?></span>
                            </div>
                            <p style="margin: 0; font-size: 0.95rem; line-height: 1.6;"><?= nl2br(h($row['isi_komentar'] ?? '')); ?></p>
                        </div>
                    <?php } ?>
                <?php } else { ?>
                    <div style="background-color: var(--bg-body); padding: 1rem; border-radius: var(--radius-sm); border-left: 4px solid var(--border); color: var(--text-muted); margin-bottom: 1.5rem;">
                        Belum ada tanggapan untuk aspirasi ini.
                    </div>
                <?php } ?>
            </div>

            <hr style="border: none; border-top: 1px solid var(--border); margin: 2rem 0;">

            <!-- FORM TANGGAPAN -->
            <form class="aspirasi-form" action="proses_komentar_aspirasi.php" method="POST" style="max-width: 100%; padding: 0; margin: 0; background: transparent;">
                <input type="hidden" name="id_aspirasi" value="<?= (int) $data['id_aspirasi']; ?>">
                
                <div style="margin-bottom: 1rem;">
                    <label>Balas Sebagai</label>
                    <select name="level_user" required>
                        <option value="mahasiswa">Mahasiswa</option>
                    </select>
                </div>
                
                <div style="margin-bottom: 1.5rem;">
                    <label>Isi Tanggapan</label>
                    <textarea name="isi_komentar" rows="5" placeholder="Tulis tanggapan Anda di sini..." required></textarea>
                </div>
                
                <button type="submit" class="btn" style="margin-top: 0;">Kirim Tanggapan</button>
            </form>
        </div>
    <?php } ?>
</div>

<?php require_once __DIR__ . '/../../include/footer.php'; ?>
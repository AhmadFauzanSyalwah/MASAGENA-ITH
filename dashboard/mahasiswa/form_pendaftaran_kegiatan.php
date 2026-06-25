<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../include/components.php';
require_once __DIR__ . '/../../include/pendaftaran-helper.php';
require_once '../../config/session_check.php';

$id_konten = isset($_GET['id_konten']) ? (int) $_GET['id_konten'] : 0;
// Pastikan fungsi ini menerima $pdo jika menggunakan koneksi database di dalamnya
$mahasiswa = pendaftaran_current_mahasiswa($pdo);

if (!$mahasiswa) {
    try {
        $stmtMhs = $pdo->query("
            SELECT id_mahasiswa, nim, nama, prodi, kontak, email 
            FROM tbmahasiswa 
            ORDER BY id_mahasiswa ASC 
            LIMIT 1
        ");
        $mahasiswa = $stmtMhs->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die('Query mahasiswa gagal: ' . $e->getMessage());
    }
}
$defaultKuota = (int) pendaftaran_default_kuota();

$selected = null;

if ($id_konten > 0) {
    try {
        $stmt = $pdo->prepare("
            SELECT id_konten, judul, deskripsi, tanggal_kegiatan, kategori, lampiran
            FROM konten_kegiatan
            WHERE id_konten = ?
            AND status_publikasi = 'publish'
            LIMIT 1
        ");
        
        $stmt->execute([$id_konten]);
        $selected = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die('Prepare detail kegiatan gagal: ' . $e->getMessage());
    }
}

try {
    $kegiatanQuery = $pdo->query("
        SELECT
            k.id_konten,
            k.judul,
            k.tanggal_kegiatan,
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

<!-- HEADER / WELCOME AREA -->
<div class="dashboard-welcome" style="margin-bottom: 2rem;">
    <h1>Pendaftaran Kegiatan</h1>
    <p>Silakan pilih kegiatan dan lengkapi formulir pendaftaran di bawah ini.</p>
</div>

<!-- MAIN CONTENT -->
<div class="main-content">
    <div class="card" style="padding: 0; overflow: hidden; display: flex; flex-wrap: wrap; max-width: 1200px; margin: 0 auto; border: 1px solid var(--border); background-color: #ffffff;">
        
        <!-- Revisi Warna: Diubah ke tema terang (light) agar senada dengan detail kegiatan -->
        <aside style="flex: 1 1 350px; background-color: var(--bg-body, #f8f9fa); border-right: 1px solid var(--border); padding: 3rem 2.5rem; display: flex; flex-direction: column; justify-content: center;">
            <h2 style="font-size: 2.2rem; margin-bottom: 1rem; color: var(--primary);">Ayo Bergabung!</h2>
            
            <div style="margin: 1.5rem 0; width: 100%; height: 250px; border-radius: 12px; background: #e9ecef; overflow: hidden; display: flex; align-items: center; justify-content: center;">
                <img src="/masagena-ith/assets/img/form_pendaftaran.png" alt="Form Pendaftaran" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                <span style="display: none; font-weight: bold; color: #adb5bd; letter-spacing: 2px;">MASAGENA-ITH</span>
            </div>

            <h3 style="font-size: 1.4rem; font-weight: 800; margin-bottom: 1rem; line-height: 1.3; color: var(--text-dark);">
                <?= h($selected['judul'] ?? 'Pilih kegiatan yang ingin kamu ikuti'); ?>
            </h3>

            <p style="font-size: 1.05rem; line-height: 1.6; color: var(--text-muted); margin: 0;">
                <?= h($selected ? short_text($selected['deskripsi'], 130) : 'Pilih kegiatan pada formulir, lalu lengkapi dan kirim data pendaftaranmu.'); ?>
            </p>
        </aside>

        <section style="flex: 2 1 500px; padding: 3rem 2.5rem; background-color: #ffffff;">
            
            <?php if (isset($_GET['error'])) { ?>
                <div style="padding: 15px 20px; background-color: rgba(220, 53, 69, 0.1); color: var(--danger, #dc3545); border-radius: var(--radius); margin-bottom: 25px; font-weight: 700;">
                    <?= h($_GET['error']); ?>
                </div>
            <?php } ?>

            <form action="proses_pendaftaran_kegiatan.php" method="POST" style="width: 100%;">
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 1.5rem;">
                    <div>
                        <label style="display: block; margin-bottom: 8px; color: var(--text-muted); font-size: 0.85rem; font-weight: 700; text-transform: uppercase;">Nama Mahasiswa</label>
                        <input type="text" value="<?= h($mahasiswa['nama'] ?? ''); ?>" readonly style="width: 100%; padding: 12px 15px; border: 1px solid var(--border); border-radius: 8px; background: var(--bg-body); color: var(--text-dark); font-family: inherit;">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 8px; color: var(--text-muted); font-size: 0.85rem; font-weight: 700; text-transform: uppercase;">NIM</label>
                        <input type="text" name="nim" value="<?= h($mahasiswa['nim'] ?? ''); ?>" readonly style="width: 100%; padding: 12px 15px; border: 1px solid var(--border); border-radius: 8px; background: var(--bg-body); color: var(--text-dark); font-family: inherit;">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 1.5rem;">
                    <div>
                        <label style="display: block; margin-bottom: 8px; color: var(--text-muted); font-size: 0.85rem; font-weight: 700; text-transform: uppercase;">Program Studi</label>
                        <input type="text" value="<?= h($mahasiswa['prodi'] ?? ''); ?>" readonly style="width: 100%; padding: 12px 15px; border: 1px solid var(--border); border-radius: 8px; background: var(--bg-body); color: var(--text-dark); font-family: inherit;">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 8px; color: var(--text-muted); font-size: 0.85rem; font-weight: 700; text-transform: uppercase;">No HP / Kontak</label>
                        <input type="text" value="<?= h($mahasiswa['kontak'] ?? '-'); ?>" readonly style="width: 100%; padding: 12px 15px; border: 1px solid var(--border); border-radius: 8px; background: var(--bg-body); color: var(--text-dark); font-family: inherit;">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 1.5rem;">
                    <div>
                        <label style="display: block; margin-bottom: 8px; color: var(--text-muted); font-size: 0.85rem; font-weight: 700; text-transform: uppercase;">Email</label>
                        <input type="email" name="email" value="<?= h($mahasiswa['email'] ?? ''); ?>" readonly style="width: 100%; padding: 12px 15px; border: 1px solid var(--border); border-radius: 8px; background: var(--bg-body); color: var(--text-dark); font-family: inherit;">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 8px; color: var(--text-muted); font-size: 0.85rem; font-weight: 700; text-transform: uppercase;">Pilih Kegiatan</label>
                        <select name="id_konten" required style="width: 100%; padding: 12px 15px; border: 1px solid var(--border); border-radius: 8px; background: #fff; color: var(--text-dark); font-family: inherit; font-size: 1rem; cursor: pointer;">
                            <option value="">-- Pilih Kegiatan --</option>
                            <?php while ($row = $kegiatanQuery->fetch(PDO::FETCH_ASSOC)) { ?>
                                <?php
                                    $kuota = (int) $row['kuota'];
                                    $jumlah = (int) $row['jumlah_peserta'];
                                    $penuh = $kuota > 0 && $jumlah >= $kuota;
                                    $isSelected = $id_konten === (int) $row['id_konten'];
                                ?>
                                <option value="<?= (int) $row['id_konten']; ?>" <?= $isSelected ? 'selected' : ''; ?> <?= $penuh ? 'disabled' : ''; ?>>
                                    <?= h($row['judul']); ?> — <?= $jumlah; ?>/<?= $kuota; ?> <?= $penuh ? '(Kuota Penuh)' : ''; ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <div style="margin-bottom: 2rem;">
                    <label style="display: block; margin-bottom: 8px; color: var(--text-muted); font-size: 0.85rem; font-weight: 700; text-transform: uppercase;">Catatan / Alasan Mengikuti</label>
                    <textarea name="catatan" rows="5" placeholder="Tuliskan alasan kamu mengikuti kegiatan ini..." required style="width: 100%; padding: 15px; border: 1px solid var(--border); border-radius: 8px; background: #fff; color: var(--text-dark); font-family: inherit; font-size: 1rem; resize: vertical; min-height: 120px;"></textarea>
                </div>

                <!-- Tombol Kembali dibuat sama letak dan gayanya dengan yang di detail_kegiatan.php -->
                <div style="display: flex; gap: 15px; flex-wrap: wrap; margin-top: 1rem;">
                    <a href="javascript:history.back()" class="btn-cancel" style="padding: 12px 24px; text-decoration: none; display: inline-flex; align-items: center; justify-content: center;">
                        &larr; Kembali
                    </a>
                    <button type="submit" class="btn" style="flex: 1; padding: 12px 24px; font-size: 1.05rem; text-transform: uppercase; letter-spacing: 1px; border-radius: 8px;">
                        Submit Pendaftaran
                    </button>
                </div>

            </form>
        </section>

    </div>
</div>

<?php require_once __DIR__ . '/../../include/footer.php'; ?>
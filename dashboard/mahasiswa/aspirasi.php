<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../include/components.php';
require_once '../../config/session_check.php';

// Pastikan fungsi helper ini di dalam komponennya sudah mendukung PDO jika menggunakannya
$mahasiswa = active_mahasiswa($pdo);
$schemaReady = aspirasi_schema_ready($pdo);

try {
    // Menggunakan metode $pdo->query() untuk eksekusi query SELECT sederhana
    $organisasi = $pdo->query("
        SELECT id_organisasi, nama_organisasi 
        FROM organisasi 
        ORDER BY nama_organisasi ASC
    ");
} catch (PDOException $e) {
    // Menangkap error spesifik PDO jika query gagal
    die('Query organisasi gagal: ' . $e->getMessage());
}

require_once __DIR__ . '/../../include/header.php';
?>

<style>
    /* =============================================
       Layout Khusus Halaman Aspirasi (Menggunakan base style.css)
       ============================================= */
    .aspirasi-header-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    /* Override h2 agar margin selaras dalam flexbox */
    /* Override h2 agar margin selaras dalam flexbox dan tambahkan garis kuning */
    .aspirasi-header-actions h2 {
        margin-bottom: 0 !important;
        font-size: 1.3rem;
        border-left: 4px solid var(--accent); /* Garis kuning */
        padding-left: 0.75rem; /* Jarak teks dari garis */
    }

    .btn-group-actions {
        display: flex;
        gap: 0.5rem;
    }

    .aspirasi-grid {
        display: grid;
        grid-template-columns: 320px 1fr;
        gap: 2rem;
        align-items: start;
    }

    /* Custom Info Panel mirip Dashboard Welcome tapi bentuk Card */
    .info-panel-card {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        color: var(--white);
        border-radius: var(--radius);
        padding: 1.5rem;
        box-shadow: var(--shadow-md);
    }

    .info-icon {
        width: 48px;
        height: 48px;
        display: grid;
        place-items: center;
        border-radius: 50%;
        background: var(--accent);
        color: var(--primary);
        font-size: 24px;
        font-weight: 700;
        margin-bottom: 1rem;
    }

    .info-panel-card h3 {
        color: var(--accent);
        margin-bottom: 1rem;
        font-size: 1.2rem;
    }

    .info-panel-card p {
        color: rgba(255, 255, 255, 0.85);
        font-size: 0.9rem;
        line-height: 1.6;
        margin-bottom: 1rem;
    }

    .mini-profile {
        margin-top: 1.5rem;
        padding: 1rem;
        border-radius: var(--radius-sm);
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.15);
    }

    .mini-profile strong {
        display: block;
        margin: 0.3rem 0;
        font-size: 1.1rem;
    }

    .mini-profile.warning {
        background: rgba(220, 38, 38, 0.15);
        border-color: rgba(220, 38, 38, 0.3);
    }

    /* Penyesuaian form bawaan style.css untuk ditempatkan dalam grid */
    .aspirasi-form-wrapper {
        max-width: 100%;
        padding: 0;
        margin: 0;
    }

    .checkbox-row {
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 1rem 0 1.5rem 0;
        cursor: pointer;
        color: var(--text-dark);
        font-weight: 500;
    }

    .checkbox-row input {
        width: 16px;
        height: 16px;
        accent-color: var(--accent);
    }

    /* Responsif */
    @media (max-width: 900px) {
        .aspirasi-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="dashboard-welcome">
    <h1>Aspirasi dan Kritik</h1>
    <p>Sampaikan masukan, kritik, keluhan, atau saran kepada organisasi kampus.</p>
</div>

<div class="main-content">
    
    <div class="aspirasi-header-actions">
        <h2>Formulir Aspirasi</h2>
        
        <div class="btn-group-actions">
            <a href="cek_status_aspirasi.php" class="btn" style="background-color: var(--success); color: white;">Cek Status</a>
            <a href="aspirasi_saya.php" class="btn" style="background-color: var(--primary); color: white;">Aspirasi Saya</a>
        </div>
    </div>

    <?php if (!$schemaReady) { ?>
        <?php schema_warning(); ?>
    <?php } ?>

    <div class="aspirasi-grid">
        
        <div class="info-panel-card">
            <div class="info-icon">!</div>
            <h3>Catatan Alur</h3>
            <p>
                Status awal aspirasi adalah <strong>proses</strong>.
                Setelah pengurus/admin meninjau, status dapat berubah menjadi
                <strong>selesai</strong> atau <strong>ditolak</strong>.
            </p>
            <p>
                Jika memilih anonim, identitas pengirim tidak ditampilkan dan aspirasi
                dicek memakai kode aspirasi.
            </p>

            <?php if ($mahasiswa) { ?>
                <div class="mini-profile">
                    <span style="font-size: 0.75rem; color: var(--accent); font-weight: 600; text-transform: uppercase;">Mode Mahasiswa</span>
                    <strong><?= h($mahasiswa['nama']); ?></strong>
                    <small style="color: rgba(255,255,255,0.7);">
                        <?= h($mahasiswa['nim']); ?> · <?= h($mahasiswa['email']); ?>
                    </small>
                </div>
            <?php } else { ?>
                <div class="mini-profile warning">
                    <span style="font-size: 0.75rem; color: #fca5a5; font-weight: 600; text-transform: uppercase;">Data mahasiswa belum ada</span>
                    <strong style="color: white;">Isi tabel tbmahasiswa dulu</strong>
                    <small style="color: rgba(255,255,255,0.7);">Atau sambungkan dengan login mahasiswa.</small>
                </div>
            <?php } ?>
        </div>

        <div class="card">
            <form class="aspirasi-form aspirasi-form-wrapper" action="proses_aspirasi.php" method="POST">
                
                <label>Organisasi Tujuan</label>
                <select name="id_organisasi">
                    <option value="">Umum / Semua Organisasi</option>
                    <?php while ($org = $organisasi->fetch(PDO::FETCH_ASSOC)) { ?>
                        <option value="<?= (int) $org['id_organisasi']; ?>">
                            <?= h($org['nama_organisasi']); ?>
                        </option>
                    <?php } ?>
                </select>

                <label>Kategori</label>
                <select name="kategori" required>
                    <option value="">Pilih kategori</option>
                    <option value="Kritik">Kritik</option>
                    <option value="Saran">Saran</option>
                    <option value="Keluhan">Keluhan</option>
                    <option value="Apresiasi">Apresiasi</option>
                    <option value="Lainnya">Lainnya</option>
                </select>

                <label>Judul Aspirasi</label>
                <input type="text" name="judul" placeholder="Contoh: Perbaikan informasi kegiatan organisasi" maxlength="255" required>

                <label>Isi Aspirasi / Kritik</label>
                <textarea name="isi_aspirasi" rows="8" placeholder="Tulis aspirasi secara jelas dan spesifik..." required></textarea>

                <label class="checkbox-row">
                    <input type="checkbox" name="is_anonim" value="1">
                    <span>Kirim sebagai anonim</span>
                </label>

                <button type="submit" class="btn" style="width: 100%; margin-top: 10px;" <?= (!$schemaReady || !$mahasiswa) ? 'disabled' : ''; ?>>
                    Kirim Aspirasi
                </button>
                
            </form>
        </div>

    </div>
</div>

<?php
require_once __DIR__ . '/../../include/footer.php';
?>
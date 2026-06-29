<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../include/components.php';
require_once __DIR__ . '/../../config/session_check.php';

$mahasiswa = active_mahasiswa($pdo);
$schemaReady = aspirasi_schema_ready($pdo);

try {
    // Tarik data organisasi beserta jenisnya dari database
    $organisasi = $pdo->query("
        SELECT id_organisasi, nama_organisasi, COALESCE(jenis, 'Lainnya') as jenis
        FROM organisasi 
        ORDER BY jenis ASC, nama_organisasi ASC
    ");
} catch (PDOException $e) {
    die('Query organisasi gagal: ' . $e->getMessage());
}

// Kelompokkan data untuk dimasukkan ke dalam elemen formulir
$list_bem = [];
$list_ukm = [];
$list_himpunan = [];
$list_lainnya = [];

while ($org = $organisasi->fetch(PDO::FETCH_ASSOC)) {
    $jenis = strtoupper($org['jenis']);
    if (strpos($jenis, 'BEM') !== false) {
        $list_bem[] = $org;
    } elseif (strpos($jenis, 'UKM') !== false || strpos($jenis, 'UNIT KEGIATAN MAHASISWA') !== false) {
        $list_ukm[] = $org;
    } elseif (strpos($jenis, 'HIMPUNAN') !== false || strpos($jenis, 'HMJ') !== false || strpos($jenis, 'HIMA') !== false) {
        $list_himpunan[] = $org;
    } else {
        $list_lainnya[] = $org;
    }
}

require_once __DIR__ . '/../../include/header.php';
?>

<style>
    /* =============================================
       Trik CSS untuk Memaksa HANYA Form yang Melebar
       ============================================= */
    .aspirasi-form {
        width: 92vw !important; 
        max-width: 1400px !important;
        position: relative;
        left: 50%;
        transform: translateX(-50%);
    }

    /* =============================================
       Layout Sejajar Beranda (Kiri: Info, Kanan: Form Atas)
       ============================================= */
    .asp-layout-grid {
        display: grid;
        grid-template-columns: 340px 1fr;
        gap: 2rem;
        align-items: start;
        width: 100%;
    }

    /* Wadah Kolom Kiri */
    .left-column-wrapper {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    /* Panel Informasi Alur (Sisi Kiri) */
    .info-panel-card {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        color: var(--white);
        border-radius: var(--radius);
        padding: 1.5rem;
        box-shadow: var(--shadow-md);
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .info-panel-top {
        display: flex;
        align-items: center;
        gap: 1.25rem;
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
        flex-shrink: 0;
    }

    .info-panel-card h3 {
        color: var(--accent);
        margin-bottom: 0.25rem;
        font-size: 1.2rem;
    }

    .info-panel-card p {
        color: rgba(255, 255, 255, 0.85);
        font-size: 0.9rem;
        line-height: 1.6;
        margin-bottom: 0;
    }

    /* Kotak Identitas Mahasiswa */
    .mini-profile {
        margin-top: 0.5rem;
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

    /* Area Formulir Utama & Bawah */
    .aspirasi-form-wrapper,
    .aspirasi-bottom-card {
        width: 100%;
        padding: 1.5rem;
        background: var(--white);
        border-radius: var(--radius);
        box-shadow: var(--shadow-sm);
        box-sizing: border-box;
    }

    .aspirasi-bottom-card {
        margin-top: 2rem;
    }

    .aspirasi-header-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
        margin-bottom: 1.5rem;
        border-bottom: 1px solid var(--border);
        padding-bottom: 1rem;
    }

    .aspirasi-header-actions h2 {
        margin-bottom: 0 !important;
        font-size: 1.3rem;
        border-left: 4px solid var(--accent);
        padding-left: 0.75rem;
    }

    /* Modifikasi ditambahkan di sini: justify-content: center */
    .btn-group-actions {
        display: flex;
        justify-content: center; /* Membuat tombol rata tengah */
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .btn-sub {
        padding: 0.4rem 0.85rem;
        font-size: 0.8rem;
        border-radius: var(--radius-sm);
        text-decoration: none;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s ease;
        box-shadow: var(--shadow-sm);
    }

    .btn-sub:hover {
        opacity: 0.9;
        transform: translateY(-1px);
    }

    /* Penyesuaian Form Input */
    .asp-group {
        margin-bottom: 1.25rem;
        width: 100%;
    }

    .asp-group label {
        display: block;
        font-size: 0.95rem;
        font-weight: 600;
        color: var(--primary);
        margin-bottom: 0.35rem;
    }

    .asp-group select,
    .asp-group input[type="text"],
    .asp-group textarea {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 1px solid var(--border);
        background: var(--bg-body);
        border-radius: var(--radius-sm);
        font-family: inherit;
        font-size: 0.95rem;
        color: var(--text-dark);
        outline: none;
        box-sizing: border-box;
        transition: border-color 0.2s ease, background 0.2s ease;
    }

    .asp-group select:focus,
    .asp-group input[type="text"]:focus,
    .asp-group textarea:focus {
        border-color: var(--accent);
        background: var(--white);
    }

    /* Kontainer Bawah (Sejajarkan Checkbox & Button) */
    .bottom-actions-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
        margin-top: 1rem;
    }

    .checkbox-row {
        display: flex;
        align-items: center;
        gap: 10px;
        cursor: pointer;
        color: var(--text-dark);
        font-weight: 500;
        user-select: none;
        margin: 0;
    }

    .checkbox-row input {
        width: 16px;
        height: 16px;
        accent-color: var(--accent);
        cursor: pointer;
    }

    /* Tombol Kirim Diubah Lebih Kecil */
    .btn-kirim-asp {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 0.65rem 1.5rem;
        background: var(--accent);
        color: var(--primary);
        border: none;
        border-radius: var(--radius-sm);
        font-size: 0.95rem;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-kirim-asp:hover:not(:disabled) {
        background: var(--primary);
        color: var(--white);
    }

    .btn-kirim-asp:active:not(:disabled) {
        background: var(--accent); 
        color: var(--primary);
    }

    .btn-kirim-asp:disabled {
        background: var(--text-light);
        color: var(--text-muted);
        cursor: not-allowed;
    }

    /* Responsif */
    @media (max-width: 900px) {
        .asp-layout-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="main-container">
    <div class="dashboard-welcome" style="margin-bottom: 1.5rem;">
        <h1>Aspirasi dan Kritik Kampus</h1>
        <p>Salurkan masukan, kritik, keluhan, maupun apresiasi Anda langsung ke lembaga atau organisasi kemahasiswaan.</p>
    </div>

    <form class="aspirasi-form" action="proses_aspirasi.php" method="POST">
        
        <div class="asp-layout-grid">
            
            <div class="left-column-wrapper">
                
                <div class="btn-group-actions">
                    <a href="cek_status_aspirasi.php" class="btn-sub" style="background-color: var(--success); color: white;">
                        <i class="fa fa-search"></i> Cek Status
                    </a>
                    <a href="aspirasi_saya.php" class="btn-sub" style="background-color: var(--primary); color: white;">
                        <i class="fa fa-list"></i> Riwayat Saya
                    </a>
                </div>

                <div class="info-panel-card">
                    <div class="info-panel-top">
                        <div class="info-icon"><i class="fa fa-bullhorn"></i></div>
                        <div>
                            <h3>Alur Penanganan</h3>
                            <p>
                                Status awal aspirasi yang masuk adalah <strong>Proses</strong>. Setelah ditinjau oleh pengurus organisasi terkait, status akan diperbarui menjadi <strong>Selesai</strong> atau <strong>Ditolak</strong>.
                            </p>
                        </div>
                    </div>
                    <p style="padding-left: 0.25rem;">
                        Jika Anda memilih opsi kirim sebagai anonim, identitas Anda akan disembunyikan dan pengecekan aspirasi dilakukan lewat kode unik.
                    </p>

                    <?php if ($mahasiswa) { ?>
                        <div class="mini-profile">
                            <span style="font-size: 0.75rem; color: var(--accent); font-weight: 600; text-transform: uppercase;">Identitas Pengirim</span>
                            <strong><?= h($mahasiswa['nama']); ?></strong>
                            <small style="color: rgba(255,255,255,0.7);">
                                <?= h($mahasiswa['nim']); ?> · <?= h($mahasiswa['email']); ?>
                            </small>
                        </div>
                    <?php } else { ?>
                        <div class="mini-profile warning">
                            <span style="font-size: 0.75rem; color: #fca5a5; font-weight: 600; text-transform: uppercase;">Data mahasiswa belum terekam</span>
                            <strong style="color: white;">Lengkapi tabel database mahasiswa</strong>
                            <small style="color: rgba(255,255,255,0.7);">Pastikan akun terhubung dengan login mahasiswa.</small>
                        </div>
                    <?php } ?>
                </div>

            </div> <div class="aspirasi-form-wrapper">
                <div class="aspirasi-header-actions">
                    <h2>Formulir Pengaduan</h2>
                </div>

                <?php if (!$schemaReady) { ?>
                    <div style="margin-bottom: 1rem;">
                        <?php schema_warning(); ?>
                    </div>
                <?php } ?>

                <div class="asp-group">
                    <label>Jenis Organisasi Tujuan</label>
                    <select id="filter_jenis" class="form-control" style="margin-bottom: 10px;">
                        <option value="all">-- Tampilkan Semua Organisasi --</option>
                        <option value="bem">Badan Eksekutif Mahasiswa (BEM)</option>
                        <option value="ukm">Unit Kegiatan Mahasiswa (UKM)</option>
                        <option value="himpunan">Himpunan Mahasiswa Jurusan (HMJ / HIMA)</option>
                        <option value="lainnya">Lembaga / Umum Lainnya</option>
                    </select>
                </div>

                <div class="asp-group">
                    <label>Detail Organisasi Tujuan</label>
                    <select name="id_organisasi_tujuan" id="select_tujuan" required>
                        <option value="">-- Pilih Organisasi yang Dituju --</option>
                        
                        <?php if (!empty($list_bem)) { ?>
                            <optgroup label="Badan Eksekutif Mahasiswa (BEM)" data-jenis="bem">
                                <?php foreach ($list_bem as $org) { ?>
                                    <option value="<?= (int)$org['id_organisasi']; ?>"><?= h($org['nama_organisasi']); ?></option>
                                <?php } ?>
                            </optgroup>
                        <?php } ?>

                        <?php if (!empty($list_ukm)) { ?>
                            <optgroup label="Unit Kegiatan Mahasiswa (UKM)" data-jenis="ukm">
                                <?php foreach ($list_ukm as $org) { ?>
                                    <option value="<?= (int)$org['id_organisasi']; ?>"><?= h($org['nama_organisasi']); ?></option>
                                <?php } ?>
                            </optgroup>
                        <?php } ?>

                        <?php if (!empty($list_himpunan)) { ?>
                            <optgroup label="Himpunan Mahasiswa Jurusan (HMJ / HIMA)" data-jenis="himpunan">
                                <?php foreach ($list_himpunan as $org) { ?>
                                    <option value="<?= (int)$org['id_organisasi']; ?>"><?= h($org['nama_organisasi']); ?></option>
                                <?php } ?>
                            </optgroup>
                        <?php } ?>

                        <?php if (!empty($list_lainnya)) { ?>
                            <optgroup label="Lembaga / Umum Lainnya" data-jenis="lainnya">
                                <?php foreach ($list_lainnya as $org) { ?>
                                    <option value="<?= (int)$org['id_organisasi']; ?>"><?= h($org['nama_organisasi']); ?></option>
                                <?php } ?>
                            </optgroup>
                        <?php } ?>

                    </select>
                </div>

                <div class="asp-group">
                    <label>Kategori Masukan</label>
                    <select name="kategori" required>
                        <option value="">Pilih kategori</option>
                        <option value="Kritik">Kritik</option>
                        <option value="Saran">Saran</option>
                        <option value="Keluhan">Keluhan</option>
                        <option value="Apresiasi">Apresiasi</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                </div>

                <div class="asp-group">
                    <label>Judul Aspirasi</label>
                    <input type="text" name="judul" placeholder="Contoh: Perbaikan fasilitas ruang ormawa" maxlength="255" required>
                </div>
            </div>

        </div> 
        
        <div class="aspirasi-bottom-card">
            
            <div class="asp-group">
                <label>Isi Aspirasi / Kritik</label>
                <textarea name="isi_aspirasi" rows="8" placeholder="Tuliskan keluhan atau saran secara transparan dan membangun..." required></textarea>
            </div>

            <input type="hidden" name="nim" value="<?= $mahasiswa ? h($mahasiswa['nim']) : ''; ?>">

            <div class="bottom-actions-row">
                <label class="checkbox-row">
                    <input type="checkbox" name="is_anonim" value="1">
                    <span>Kirim sebagai anonim</span>
                </label>

                <button type="submit" class="btn-kirim-asp" <?= (!$schemaReady || !$mahasiswa) ? 'disabled' : ''; ?>>
                    <i class="fa fa-paper-plane"></i> Kirim Aspirasi
                </button>
            </div>
            
        </div>

    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const filterJenis = document.getElementById('filter_jenis');
        const optgroups = document.querySelectorAll('#select_tujuan optgroup');
        const selectTujuan = document.getElementById('select_tujuan');

        filterJenis.addEventListener('change', function() {
            const selectedVal = this.value;
            selectTujuan.value = ""; 

            optgroups.forEach(group => {
                const dataJenis = group.getAttribute('data-jenis');
                
                if (selectedVal === 'all' || dataJenis === selectedVal) {
                    group.disabled = false;
                    group.style.display = "";
                    
                    group.querySelectorAll('option').forEach(opt => {
                        opt.disabled = false;
                        opt.style.display = "";
                    });
                } else {
                    group.disabled = true;
                    group.style.display = "none";
                    
                    group.querySelectorAll('option').forEach(opt => {
                        opt.disabled = true;
                        opt.style.display = "none";
                    });
                }
            });
        });
    });
</script>

<?php
require_once __DIR__ . '/../../include/footer.php';
?>  
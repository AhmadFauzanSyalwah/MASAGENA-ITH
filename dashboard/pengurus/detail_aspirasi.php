<?php
// dashboard/pengurus/detail_aspirasi.php
session_start();

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['peran'], ['pengurus', 'admin'])) {
    header('Location: ../../auth/login.php');
    exit;
}

require_once '../../config/database.php';
require_once '../../include/pendaftaran-helper.php';

// ============================================================
// AMBIL DATA DETAIL ASPIRASI
// ============================================================
$id_aspirasi = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_aspirasi <= 0) {
    header('Location: kelola_aspirasi.php');
    exit;
}

$sql = "SELECT a.*, 
               o.nama_organisasi,
               m.nama AS nama_mahasiswa,
               m.nim
        FROM aspirasi a
        LEFT JOIN organisasi o ON a.id_organisasi_tujuan = o.id_organisasi
        LEFT JOIN tbmahasiswa m ON a.id_mahasiswa = m.id_mahasiswa
        WHERE a.id_aspirasi = :id 
        LIMIT 1";

$stmt = $pdo->prepare($sql);
$stmt->execute([':id' => $id_aspirasi]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

// Jika data tidak ditemukan
if (!$row) {
    echo "<script>alert('Data aspirasi tidak ditemukan!'); window.location.href='kelola_aspirasi.php';</script>";
    exit;
}

include '../../include/header.php';
?>

<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/profil.css?v=<?= time() ?>">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">



<style>
/* ============================================
   DETAIL ASPIRASI - LAYOUT BARU
   ============================================ */
.aspirasi-container {
    max-width: 100%;
    margin: 0;
    padding: 0 1rem;
    box-sizing: border-box;
}

/* Header */
.aspirasi-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 1.5rem;
}
.aspirasi-header .title-group h2 {
    font-size: 1.8rem;
    font-weight: 700;
    color: #071C34;
    margin: 0;
}
.aspirasi-header .title-group .subtitle {
    font-size: 0.85rem;
    color: #64748b;
    margin: 0;
}

/* Tombol Kembali */
.btn-kembali {
    background: transparent;
    color: #071C34;
    padding: 0.4rem 1.5rem;
    border-radius: 50px;
    text-decoration: none;
    font-weight: 600;
    border: 2px solid #071C34;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.2s;
}
.btn-kembali:hover {
    background: #071C34;
    color: white;
}

/* ===== CARD STYLE ===== */
.detail-card {
    background: #ffffff;
    border-radius: 16px;
    border: 1px solid #e9ecef;
    padding: 1.5rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    margin-bottom: 1.5rem;
}

.detail-card h3 {
    font-size: 1.2rem;
    font-weight: 700;
    color: #071C34;
    margin-top: 0;
    margin-bottom: 1rem;
    border-bottom: 2px solid #f1f5f9;
    padding-bottom: 0.5rem;
}

/* Content Text (Bagian Atas) */
.aspirasi-judul-detail {
    font-size: 1.4rem;
    font-weight: 700;
    color: #071C34;
    margin-bottom: 0.5rem;
}
.aspirasi-isi-detail {
    font-size: 0.95rem;
    color: #334155;
    line-height: 1.6;
    white-space: pre-wrap;
    background: #f8fafc;
    padding: 1rem;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
}

/* ===== GRID BAWAH (SEJAJAR) ===== */
.bottom-grid {
    display: grid;
    grid-template-columns: 1fr 1fr; /* Membagi jadi 2 kolom sama besar */
    gap: 1.5rem;
    align-items: start;
}

/* Meta List (Informasi Data) */
.meta-list {
    list-style: none;
    padding: 0;
    margin: 0;
}
.meta-list li {
    display: flex;
    justify-content: space-between;
    padding: 0.75rem 0;
    border-bottom: 1px solid #f1f5f9;
    font-size: 0.9rem;
}
.meta-list li:last-child {
    border-bottom: none;
}
.meta-list .label {
    font-weight: 600;
    color: #64748b;
}
.meta-list .value {
    font-weight: 700;
    color: #071C34;
    text-align: right;
}

/* Status Badge & Chip */
.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.25rem 1rem;
    border-radius: 50px;
    font-size: 0.75rem;
    font-weight: 700;
    color: #ffffff;
    white-space: nowrap;
}
.status-badge.proses { background: #f59e0b; }
.status-badge.selesai { background: #22c55e; }
.status-badge.ditolak { background: #dc2626; }

.anon-chip {
    background: #e2e8f0;
    color: #475569;
    padding: 0.15rem 0.6rem;
    border-radius: 50px;
    font-size: 0.7rem;
    font-weight: 600;
}

/* Tindak Lanjut Button (Horizontal Sejajar) */
.action-inline {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.action-inline a {
    flex: 1; /* Membuat tombol mengisi ruang dengan lebar seimbang */
    padding: 0.5rem 1rem;
    border-radius: 50px;
    font-size: 0.8rem;
    font-weight: 700;
    text-decoration: none;
    display: inline-flex;
    justify-content: center;
    align-items: center;
    gap: 0.4rem;
    border: 2px solid;
    background: transparent;
    transition: all 0.2s ease-in-out;
}

.action-inline a.proses { color: #f59e0b; border-color: #f59e0b; }
.action-inline a.proses:hover { background: #f59e0b; color: #ffffff; }

.action-inline a.selesai { color: #22c55e; border-color: #22c55e; }
.action-inline a.selesai:hover { background: #22c55e; color: #ffffff; }

.action-inline a.tolak { color: #dc2626; border-color: #dc2626; }
.action-inline a.tolak:hover { background: #dc2626; color: #ffffff; }

/* Responsive Mobile */
@media (max-width: 768px) {
    .aspirasi-header {
        flex-direction: column;
        align-items: stretch;
        text-align: center;
    }
    .bottom-grid {
        grid-template-columns: 1fr; /* Di HP akan turun ke bawah secara otomatis */
    }
    .action-inline {
        flex-direction: column;
    }
    .action-inline a {
        width: 100%;
    }
}
</style>

<div class="aspirasi-container">

    <div class="aspirasi-header">
        <div class="title-group">
            <h2>Detail Aspirasi</h2>
            <p class="subtitle">
                <i class="fas fa-info-circle"></i> 
                Melihat rincian pesan aspirasi mahasiswa secara lengkap
            </p>
        </div>
        <a href="kelola_aspirasi.php" class="btn-kembali">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="detail-card">
        <div style="display: flex; gap: 0.5rem; margin-bottom: 0.8rem; flex-wrap: wrap;">
            <span style="background:#f1f5f9; padding:0.2rem 0.8rem; border-radius:50px; font-size:0.75rem; font-weight: 600; color:#071C34;">
                Kode: <?= htmlspecialchars($row['kode_aspirasi'] ?? '-') ?>
            </span>
            <span style="background:#071C34; padding:0.2rem 0.8rem; border-radius:50px; font-size:0.75rem; font-weight: 600; color:#ffffff;">
                Kategori: <?= htmlspecialchars($row['kategori'] ?? '-') ?>
            </span>
        </div>
        
        <h1 class="aspirasi-judul-detail"><?= htmlspecialchars($row['judul'] ?? '-') ?></h1>
        
        <div style="font-size: 0.8rem; color:#64748b; margin-bottom: 1.5rem;">
            <i class="fa-regular fa-calendar"></i> Dikirim pada: <?= date('d M Y H:i', strtotime($row['created_at'] ?? $row['tanggal'] ?? 'now')) ?> WIB
        </div>
        
        <div class="aspirasi-isi-detail"><?= htmlspecialchars($row['isi_aspirasi'] ?? '') ?></div>
    </div>


    <div class="bottom-grid">
        
        <div class="detail-card" style="margin-bottom: 0;">
            <h3>Informasi Data</h3>
            <ul class="meta-list">
                <li>
                    <span class="label">Status</span>
                    <span class="value">
                        <span class="status-badge <?= $row['status'] ?? 'proses' ?>">
                            <i class="fas <?= ($row['status'] ?? 'proses') == 'proses' ? 'fa-clock' : (($row['status'] ?? 'proses') == 'selesai' ? 'fa-check' : 'fa-times') ?>"></i>
                            <?= ucfirst($row['status'] ?? 'Proses') ?>
                        </span>
                    </span>
                </li>
                <li>
                    <span class="label">Tujuan</span>
                    <span class="value"><?= htmlspecialchars($row['nama_organisasi'] ?? 'Umum') ?></span>
                </li>
                <li>
                    <span class="label">Pengirim</span>
                    <span class="value">
                        <?php if ((int)($row['is_anonim'] ?? 0) === 1): ?>
                            <span class="anon-chip"><i class="fas fa-user-secret"></i> Anonim</span>
                        <?php else: ?>
                            <div><?= htmlspecialchars($row['nama_mahasiswa'] ?? 'Mahasiswa') ?></div>
                            <div style="font-size: 0.75rem; color: #94a3b8; font-weight: normal;">NIM: <?= htmlspecialchars($row['nim'] ?? '-') ?></div>
                        <?php endif; ?>
                    </span>
                </li>
            </ul>
        </div>

        <div class="detail-card" style="margin-bottom: 0;">
            <h3>Tindak Lanjut</h3>
            <p style="font-size: 0.8rem; color: #64748b; margin-top: 0; margin-bottom: 1rem;">
                Perbarui status penanganan untuk aspirasi ini:
            </p>
            
            <div class="action-inline">
                <a href="update_status_aspirasi.php?id=<?= (int)$row['id_aspirasi'] ?>&status=proses" class="proses" title="Set Proses">
                    <i class="fas fa-clock"></i> Proses
                </a>
                <a href="update_status_aspirasi.php?id=<?= (int)$row['id_aspirasi'] ?>&status=selesai" class="selesai" title="Set Selesai">
                    <i class="fas fa-check"></i> Selesai
                </a>
                <a href="update_status_aspirasi.php?id=<?= (int)$row['id_aspirasi'] ?>&status=ditolak" class="tolak" onclick="return confirm('Yakin ingin menolak aspirasi ini?')" title="Set Tolak">
                    <i class="fas fa-times"></i> Tolak
                </a>
            </div>
        </div>

    </div>

</div>

<?php include '../../include/footer.php'; ?>
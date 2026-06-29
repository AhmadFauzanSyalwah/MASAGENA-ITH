<?php
// dashboard/pengurus/profil_organisasi.php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['peran'] != 'pengurus') {
    header('Location: ../../auth/login.php');
    exit;
}

require_once '../../config/database.php';
require_once '../../include/pendaftaran-helper.php';

$id_user = $_SESSION['user_id'];
$level = $_SESSION['level'] ?? 'biasa';
$is_inti = ($level === 'Pengurus Inti' || $level === 'inti');

// ============================================================
// AMBIL ID ORGANISASI PENGURUS
// ============================================================
$stmtOrg = $pdo->prepare("SELECT id_organisasi FROM pengurus_organisasi WHERE id_pengurus = ?");
$stmtOrg->execute([$id_user]);
$pengurus = $stmtOrg->fetch(PDO::FETCH_ASSOC);

if (!$pengurus) {
    $_SESSION['error'] = 'Anda belum terdaftar di organisasi manapun.';
    header('Location: index.php');
    exit;
}

$id_organisasi = $pengurus['id_organisasi'];

// ============================================================
// AMBIL DATA ORGANISASI
// ============================================================
$stmt = $pdo->prepare("SELECT * FROM organisasi WHERE id_organisasi = ?");
$stmt->execute([$id_organisasi]);
$organisasi = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$organisasi) {
    $_SESSION['error'] = 'Organisasi tidak ditemukan.';
    header('Location: index.php');
    exit;
}

// ============================================================
// AMBIL SEMUA PENGURUS
// ============================================================
$stmtPengurus = $pdo->prepare("SELECT * FROM pengurus_organisasi WHERE id_organisasi = ? ORDER BY 
    CASE 
        WHEN jabatan LIKE '%Ketua%' THEN 1
        WHEN jabatan LIKE '%Sekretaris%' THEN 2
        WHEN jabatan LIKE '%Bendahara%' THEN 3
        WHEN level = 'Pengurus Inti' THEN 4
        ELSE 5
    END, nama_pengurus ASC");
$stmtPengurus->execute([$id_organisasi]);
$semua_pengurus = $stmtPengurus->fetchAll();

// ============================================================
// AMBIL PESAN SESSION (FLASH MESSAGE) DAN HAPUS
// ============================================================
$success = $_SESSION['success'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);

// ============================================================
// SET KONTEKS UNTUK HEADER
// ============================================================
$page_context = 'profil_organisasi';

include '../../include/header.php';
?>

<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/profil_organisasi.css?v=<?= time() ?>">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

<?php if ($success): ?>
    <div class="alert alert-success" style="max-width:1200px; margin:0 auto 1rem auto; padding:0.8rem 1.5rem; border-radius:12px; background:#dcfce7; color:#166534; border-left:4px solid #22c55e;">
        <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?>
    </div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger" style="max-width:1200px; margin:0 auto 1rem auto; padding:0.8rem 1.5rem; border-radius:12px; background:#fee2e2; color:#991b1b; border-left:4px solid #dc2626;">
        <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<style>
/* ============================================
   PROFIL ORGANISASI - VIEW MODE
   ============================================ */

.profil-org-container {
    max-width: 100%;
    margin: 0;
    padding: 0 1.5rem;
    box-sizing: border-box;
}

/* HEADER - FLEX DENGAN BADGE DI KANAN */
.profil-org-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 1.5rem;
}
.profil-org-header .title-group h2 {
    font-size: 1.8rem;
    font-weight: 700;
    color: #071C34;
    margin: 0;
}
.profil-org-header .title-group h2 i {
    color: #FFA007;
    margin-right: 0.5rem;
}
.profil-org-header .title-group .subtitle {
    font-size: 1rem;
    color: #64748b;
    margin: 0;
}

/* BADGE MODE LIHAT - SEJAJAR DENGAN MANAJEMEN PENGURUS */
.profil-org-header .badge-mode-view {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.25rem 1rem;
    border-radius: 50px;
    font-size: 0.75rem;
    font-weight: 600;
    background: #f1f5f9;
    color: #64748b;
    white-space: nowrap;
    flex-shrink: 0;
}
.profil-org-header .badge-mode-view i {
    color: #64748b;
    font-size: 0.8rem;
}

/* CARD */
.profil-org-outer-card {
    border: none;
    border-radius: 20px;
    padding: 20px;
    background: #f8fafc;
    box-shadow: 0 4px 20px rgba(10, 42, 74, 0.08);
}
.profil-org-inner-card {
    background: #ffffff;
    border-radius: 14px;
    padding: 28px 32px;
    border: 1px solid #e9ecef;
}

/* ===== TOP: FOTO + NAMA + DESKRIPSI ===== */
.profil-org-top {
    display: flex;
    gap: 2rem;
    align-items: flex-start;
    margin-bottom: 1.5rem;
    padding-bottom: 1.5rem;
    border-bottom: 2px solid #f1f5f9;
}

/* Avatar */
.profil-org-avatar {
    flex-shrink: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
}
.profil-org-avatar .avatar-wrapper {
    width: 140px;
    height: 140px;
    border-radius: 50%;
    overflow: hidden;
    border: 3px solid #FFA007;
    box-shadow: 0 4px 15px rgba(10, 42, 74, 0.12);
    background: #f1f5f9;
    display: flex;
    align-items: center;
    justify-content: center;
}
.profil-org-avatar .avatar-wrapper img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.profil-org-avatar .avatar-wrapper .no-logo {
    font-size: 4rem;
    color: #94a3b8;
}

/* Nama & Deskripsi */
.profil-org-info {
    flex: 1;
    min-width: 0;
}
.profil-org-info .nama-org {
    font-size: 1.8rem;
    font-weight: 700;
    color: #071C34;
    display: flex;
    align-items: center;
    gap: 0.8rem;
    flex-wrap: wrap;
    margin-bottom: 0.1rem;
}
.profil-org-info .nama-org .status-badge-org {
    display: inline-block;
    background: #FFA007;
    color: #071C34;
    padding: 0.1rem 1.2rem;
    border-radius: 50px;
    font-size: 0.75rem;
    font-weight: 700;
}
.profil-org-info .deskripsi-org {
    font-size: 0.95rem;
    color: #1e293b;
    line-height: 1.7;
    margin-top: 0.3rem;
    padding-right: 1rem;
}

/* ===== INFO GRID ===== */
.profil-org-grid {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 1.5rem 2rem;
    margin-bottom: 1.5rem;
    padding-bottom: 1.5rem;
    border-bottom: 2px solid #f1f5f9;
}
.profil-org-grid .grid-item label {
    font-size: 0.65rem;
    font-weight: 700;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    display: block;
    margin-bottom: 0.1rem;
}
.profil-org-grid .grid-item label i {
    color: #FFA007;
    margin-right: 0.3rem;
    font-size: 0.7rem;
}
.profil-org-grid .grid-item .value {
    font-size: 1rem;
    font-weight: 600;
    color: #071C34;
    margin: 0;
}

/* ===== VISI & MISI (2 KOLOM) ===== */
.profil-org-vm {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
    margin-bottom: 1.5rem;
    padding-bottom: 1.5rem;
    border-bottom: 2px solid #f1f5f9;
}
.profil-org-vm .vm-item label {
    font-size: 0.65rem;
    font-weight: 700;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    display: block;
    margin-bottom: 0.1rem;
}
.profil-org-vm .vm-item label i {
    color: #FFA007;
    margin-right: 0.3rem;
}
.profil-org-vm .vm-item .value {
    font-size: 0.95rem;
    color: #1e293b;
    line-height: 1.6;
    margin: 0;
}

/* ===== STRUKTUR KEPENGURUSAN ===== */
.profil-org-struktur {
    margin-top: 0.5rem;
}
.profil-org-struktur .section-title {
    font-size: 1.1rem;
    font-weight: 700;
    color: #071C34;
    margin-bottom: 0.3rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.profil-org-struktur .section-title i {
    color: #FFA007;
}
.profil-org-struktur .sub-title {
    font-size: 0.85rem;
    color: #94a3b8;
    margin-bottom: 1rem;
}
.pengurus-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 0.8rem;
}
.pengurus-card {
    background: #f8fafc;
    border-radius: 10px;
    padding: 0.8rem 1rem;
    border-left: 4px solid #FFA007;
    transition: 0.2s;
}
.pengurus-card:hover {
    background: #f1f5f9;
}
.pengurus-card .p-nama {
    font-weight: 700;
    color: #071C34;
}
.pengurus-card .p-nama i {
    color: #FFA007;
    margin-right: 0.3rem;
}
.pengurus-card .p-jabatan {
    font-size: 0.75rem;
    color: #FFA007;
    font-weight: 600;
}
.pengurus-card .p-jabatan i {
    margin-right: 0.2rem;
}
.pengurus-card .p-level {
    font-size: 0.6rem;
    padding: 0.1rem 0.6rem;
    border-radius: 50px;
    font-weight: 600;
}
.pengurus-card .p-level.inti {
    background: #fef9c3;
    color: #ca8a04;
}
.pengurus-card .p-level.departemen {
    background: #dbeafe;
    color: #1e40af;
}
.pengurus-card .p-phone {
    font-size: 0.65rem;
    color: #94a3b8;
}
.pengurus-card .p-phone i {
    color: #94a3b8;
}

/* ===== TOMBOL EDIT ===== */
.profil-org-actions {
    display: flex;
    justify-content: flex-end;
    margin-top: 1.5rem;
    padding-top: 1rem;
    border-top: 2px solid #f1f5f9;
}
.profil-org-actions .btn-edit {
    background: #071C34;
    color: #ffffff;
    border: 2px solid #071C34;
    border-radius: 50px;
    padding: 0.5rem 2.5rem;
    font-weight: 700;
    font-size: 0.95rem;
    transition: 0.3s;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}
.profil-org-actions .btn-edit:hover {
    background: #FFA007;
    color: #ffffff;
    border-color: #FFA007;
}
.profil-org-actions .btn-edit:disabled {
    background: #e2e8f0;
    color: #94a3b8;
    border-color: #e2e8f0;
    cursor: not-allowed;
    pointer-events: none;
}
.profil-org-actions .btn-edit i {
    color: inherit;
}

/* ===== RESPONSIVE ===== */
@media (max-width: 992px) {
    .profil-org-top {
        flex-direction: column;
        align-items: center;
        text-align: center;
    }
    .profil-org-grid {
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }
    .profil-org-vm {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    .pengurus-grid {
        grid-template-columns: 1fr 1fr;
    }
    .profil-org-actions {
        justify-content: center;
    }
}
@media (max-width: 576px) {
    .profil-org-container {
        padding: 0 0.5rem;
    }
    .profil-org-inner-card {
        padding: 16px;
    }
    .profil-org-grid {
        grid-template-columns: 1fr;
    }
    .pengurus-grid {
        grid-template-columns: 1fr;
    }
    .profil-org-info .nama-org {
        font-size: 1.4rem;
        justify-content: center;
    }
    .profil-org-header {
        flex-direction: column;
        align-items: stretch;
        text-align: center;
    }
    .profil-org-header .badge-mode-view {
        justify-content: center;
    }
}
</style>

<div class="profil-org-container">

    <!-- HEADER: Judul di kiri, Badge "Mode Lihat" di kanan -->
    <div class="profil-org-header">
        <div class="title-group">
            <h2>
                <i class="fas fa-building"></i> Profil Organisasi
            </h2>
            <p class="subtitle">Informasi lengkap organisasi Anda</p>
        </div>

        <!-- Badge Mode Lihat - SEJAJAR DENGAN MANAJEMEN PENGURUS -->
        <span class="badge-mode-view">
            <i class="fas fa-eye"></i> Mode Lihat
        </span>
    </div>

    <div class="profil-org-outer-card">
        <div class="profil-org-inner-card">

            <!-- ===== TOP: FOTO + NAMA + DESKRIPSI ===== -->
            <div class="profil-org-top">

                <!-- Avatar -->
                <div class="profil-org-avatar">
                    <div class="avatar-wrapper">
                        <?php 
                        $logoPath = '';
                        if (!empty($organisasi['logo']) && file_exists('../../uploads/logo/' . $organisasi['logo'])) {
                            $logoPath = BASE_URL . '/uploads/logo/' . $organisasi['logo'];
                        }
                        ?>
                        <?php if ($logoPath): ?>
                            <img src="<?= $logoPath ?>" alt="Logo <?= htmlspecialchars($organisasi['nama_organisasi']) ?>">
                        <?php else: ?>
                            <span class="no-logo"><i class="fas fa-building"></i></span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Nama & Deskripsi -->
                <div class="profil-org-info">
                    <div class="nama-org">
                        <?= htmlspecialchars($organisasi['nama_organisasi']) ?>
                        <span class="status-badge-org"><?= htmlspecialchars($organisasi['jenis']) ?></span>
                    </div>
                    <div class="deskripsi-org">
                        <?= nl2br(htmlspecialchars($organisasi['deskripsi'] ?? '')) ?>
                    </div>
                </div>

            </div>

            <!-- ===== INFO GRID ===== -->
            <div class="profil-org-grid">
                <div class="grid-item">
                    <label><i class="fas fa-id-card"></i> ID Organisasi</label>
                    <div class="value"><?= $organisasi['id_organisasi'] ?></div>
                </div>
                <div class="grid-item">
                    <label><i class="fas fa-calendar-alt"></i> Tanggal Bergabung</label>
                    <div class="value"><?= date('d M Y', strtotime($organisasi['created_at'] ?? date('Y-m-d'))) ?></div>
                </div>
                <div class="grid-item">
                    <label><i class="fas fa-user-tie"></i> Dosen Pembina</label>
                    <div class="value"><?= htmlspecialchars($organisasi['pembina'] ?? '-') ?></div>
                </div>
            </div>

            <!-- ===== VISI & MISI ===== -->
            <div class="profil-org-vm">
                <div class="vm-item">
                    <label><i class="fas fa-bullseye"></i> Visi</label>
                    <div class="value"><?= nl2br(htmlspecialchars($organisasi['visi'] ?? '')) ?></div>
                </div>
                <div class="vm-item">
                    <label><i class="fas fa-list-check"></i> Misi</label>
                    <div class="value"><?= nl2br(htmlspecialchars($organisasi['misi'] ?? '')) ?></div>
                </div>
            </div>

            <!-- ===== STRUKTUR KEPENGURUSAN ===== -->
            <div class="profil-org-struktur">
                <div class="section-title"><i class="fas fa-users"></i> Struktur Kepengurusan</div>
                <div class="sub-title">Daftar pengurus organisasi <?= htmlspecialchars($organisasi['nama_organisasi']) ?></div>

                <?php if (count($semua_pengurus) > 0): ?>
                    <div class="pengurus-grid">
                        <?php foreach ($semua_pengurus as $p): ?>
                            <div class="pengurus-card">
                                <div class="p-nama"><i class="fas fa-user-circle"></i> <?= htmlspecialchars($p['nama_pengurus']) ?></div>
                                <div class="p-jabatan"><i class="fas fa-briefcase"></i> <?= htmlspecialchars($p['jabatan']) ?></div>
                                <div style="margin-top:0.2rem; display:flex; gap:0.5rem; align-items:center; flex-wrap:wrap;">
                                    <span class="p-level <?= ($p['level'] === 'Pengurus Inti' || $p['level'] === 'inti') ? 'inti' : 'departemen' ?>">
                                        <?= ($p['level'] === 'Pengurus Inti' || $p['level'] === 'inti') ? 'Inti' : 'Departemen' ?>
                                    </span>
                                    <?php if (!empty($p['no_hp'])): ?>
                                        <span class="p-phone"><i class="fas fa-phone"></i> <?= htmlspecialchars($p['no_hp']) ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p style="color:#94a3b8; padding:0.5rem 0;"><i class="fas fa-users-slash" style="margin-right:0.5rem;"></i> Belum ada pengurus terdaftar.</p>
                <?php endif; ?>
            </div>

            <!-- ===== TOMBOL EDIT ===== -->
            <div class="profil-org-actions">
                <?php if ($is_inti): ?>
                    <a href="edit_profil_organisasi.php" class="btn-edit">
                        <i class="fas fa-pen"></i> Edit Profil
                    </a>
                <?php else: ?>
                    <span class="btn-edit" disabled>
                        <i class="fas fa-lock"></i> Hanya Pengurus Inti
                    </span>
                <?php endif; ?>
            </div>

        </div><!-- end inner -->
    </div><!-- end outer -->

</div>

<?php include '../../include/footer.php'; ?>
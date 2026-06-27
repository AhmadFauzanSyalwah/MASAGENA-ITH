<?php
<<<<<<< HEAD
// dashboard/mahasiswa/organisasi_detail.php
session_start();

// ===== CEK LOGIN & ROLE =====
if (!isset($_SESSION['user_id']) || $_SESSION['peran'] != 'mahasiswa') {
    header('Location: ../../auth/login.php');
    exit;
}

require_once '../../config/database.php';
require_once '../../include/pendaftaran-helper.php';

$id_organisasi = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id_organisasi <= 0) {
    header('Location: organisasi.php');
    exit;
}

// ============================================================
// AMBIL DATA ORGANISASI
// ============================================================
$sqlOrg = "SELECT * FROM organisasi WHERE id_organisasi = :id";
$stmtOrg = $pdo->prepare($sqlOrg);
$stmtOrg->execute([':id' => $id_organisasi]);
$organisasi = $stmtOrg->fetch(PDO::FETCH_ASSOC);

if (!$organisasi) {
    header('Location: organisasi.php');
    exit;
}

// ============================================================
// AMBIL DATA PENGURUS
// ============================================================
$sqlPengurus = "SELECT * FROM pengurus_organisasi 
                WHERE id_organisasi = :id_org 
                ORDER BY 
                    CASE 
                        WHEN jabatan IN ('Ketua', 'Wakil Ketua') THEN 1
                        WHEN jabatan IN ('Sekretaris', 'Bendahara') THEN 2
                        ELSE 3
                    END,
                    nama_pengurus ASC";
$stmtPengurus = $pdo->prepare($sqlPengurus);
$stmtPengurus->execute([':id_org' => $id_organisasi]);
$pengurus = $stmtPengurus->fetchAll();

// ============================================================
// AMBIL DATA KEGIATAN ORGANISASI
// ============================================================
$sqlKegiatan = "SELECT * FROM konten_kegiatan 
                WHERE id_organisasi = :id_org 
                AND status_publikasi = 'publik' 
                ORDER BY tanggal_kegiatan DESC 
                LIMIT 5";
$stmtKegiatan = $pdo->prepare($sqlKegiatan);
$stmtKegiatan->execute([':id_org' => $id_organisasi]);
$kegiatan = $stmtKegiatan->fetchAll();

include '../../include/header.php';
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

<style>
.detail-container {
    max-width: 1000px;
    margin: 2rem auto;
    padding: 0 1rem;
}
/* Back button */
.btn-back {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.4rem 1.2rem;
    border-radius: 50px;
    background: #f1f5f9;
    color: #071C34;
    text-decoration: none;
    font-weight: 600;
    border: 1px solid #e2e8f0;
    transition: 0.2s;
    margin-bottom: 1.5rem;
}
.btn-back:hover {
    background: #FFA007;
    color: #071C34;
    border-color: #FFA007;
}
.profil-card {
    background: #fff;
    border-radius: 20px;
    padding: 2rem;
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
    border: 1px solid #e9ecef;
    margin-bottom: 2rem;
}
.profil-card .header {
    display: flex;
    align-items: center;
    gap: 2rem;
    flex-wrap: wrap;
}
.profil-card .header .logo {
    width: 120px;
    height: 120px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f8fafc;
    border-radius: 16px;
    font-size: 4rem;
    color: #071C34;
    border: 1px solid #e9ecef;
    flex-shrink: 0;
}
.profil-card .header .logo img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
    padding: 0.5rem;
}
.profil-card .header .info h1 {
    font-size: 2rem;
    font-weight: 700;
    color: #071C34;
    margin: 0 0 0.2rem 0;
}
.profil-card .header .info .jenis {
    display: inline-block;
    background: #FFA007;
    color: #071C34;
    padding: 0.2rem 1rem;
    border-radius: 50px;
    font-size: 0.8rem;
    font-weight: 600;
}
.profil-card .header .info .deskripsi {
    color: #475569;
    font-size: 1rem;
    line-height: 1.6;
    margin-top: 0.8rem;
}
.section-title {
    font-size: 1.3rem;
    font-weight: 700;
    color: #071C34;
    border-bottom: 2px solid #FFA007;
    padding-bottom: 0.5rem;
    margin-bottom: 1.5rem;
    margin-top: 2rem;
}
.pengurus-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 1rem;
}
.pengurus-item {
    background: #f8fafc;
    border-radius: 12px;
    padding: 1rem 1.2rem;
    border-left: 3px solid #FFA007;
    transition: 0.2s;
}
.pengurus-item:hover {
    background: #f1f5f9;
}
.pengurus-item .nama {
    font-weight: 700;
    color: #071C34;
    font-size: 1rem;
}
.pengurus-item .jabatan {
    font-size: 0.8rem;
    color: #64748b;
    margin-top: 0.2rem;
}
.pengurus-item .no-hp {
    font-size: 0.75rem;
    color: #94a3b8;
    margin-top: 0.2rem;
}
.pengurus-item .no-hp i {
    color: #FFA007;
    margin-right: 0.3rem;
}
.kegiatan-list {
    display: flex;
    flex-direction: column;
    gap: 0.6rem;
}
.kegiatan-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 0.5rem;
    padding: 0.6rem 1rem;
    background: #f8fafc;
    border-radius: 12px;
    transition: 0.2s;
}
.kegiatan-item:hover {
    background: #f1f5f9;
}
.kegiatan-item .info {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
}
.kegiatan-item .info .judul {
    font-weight: 600;
    color: #071C34;
}
.kegiatan-item .info .judul a {
    color: #071C34;
    text-decoration: none;
}
.kegiatan-item .info .judul a:hover {
    color: #FFA007;
}
.kegiatan-item .info .meta {
    font-size: 0.8rem;
    color: #94a3b8;
}
.kegiatan-item .info .meta i {
    color: #FFA007;
    margin-right: 0.2rem;
}
.kegiatan-item .btn-detail {
    background: transparent;
    color: #071C34;
    border: 2px solid #071C34;
    border-radius: 50px;
    padding: 0.15rem 0.8rem;
    font-size: 0.7rem;
    font-weight: 600;
    transition: 0.3s;
    text-decoration: none;
}
.kegiatan-item .btn-detail:hover {
    background: #071C34;
    color: #fff;
}
.empty-state {
    text-align: center;
    padding: 2rem;
    color: #94a3b8;
}
.empty-state i {
    font-size: 2.5rem;
    color: #cbd5e0;
    margin-bottom: 0.5rem;
}
@media (max-width: 768px) {
    .profil-card .header {
        flex-direction: column;
        text-align: center;
    }
    .pengurus-grid {
        grid-template-columns: 1fr 1fr;
    }
}
@media (max-width: 480px) {
    .pengurus-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="detail-container">

    <!-- Tombol Kembali -->
    <a href="organisasi.php" class="btn-back"><i class="fas fa-arrow-left"></i> Kembali ke Daftar Organisasi</a>

    <!-- Profil Organisasi -->
    <div class="profil-card">
        <div class="header">
            <div class="logo">
                <?php if (!empty($organisasi['logo']) && file_exists('../../uploads/logo/' . $organisasi['logo'])): ?>
                    <img src="<?= BASE_URL ?>/uploads/logo/<?= $organisasi['logo'] ?>" alt="Logo <?= htmlspecialchars($organisasi['nama_organisasi']) ?>">
                <?php else: ?>
                    <i class="fas fa-building"></i>
                <?php endif; ?>
            </div>
            <div class="info">
                <h1><?= htmlspecialchars($organisasi['nama_organisasi']) ?></h1>
                <span class="jenis"><?= htmlspecialchars($organisasi['jenis']) ?></span>
                <div class="deskripsi"><?= nl2br(htmlspecialchars($organisasi['deskripsi'] ?? '')) ?></div>
            </div>
        </div>
    </div>

    <!-- Daftar Pengurus -->
    <h3 class="section-title"><i class="fa-regular fa-user" style="color:#FFA007;"></i> Pengurus</h3>
    <?php if (count($pengurus) > 0): ?>
        <div class="pengurus-grid">
            <?php foreach ($pengurus as $p): ?>
                <div class="pengurus-item">
                    <div class="nama"><?= htmlspecialchars($p['nama_pengurus']) ?></div>
                    <div class="jabatan"><?= htmlspecialchars($p['jabatan'] ?? 'Anggota') ?></div>
                    <?php if (!empty($p['no_hp'])): ?>
                        <div class="no-hp"><i class="fas fa-phone"></i> <?= htmlspecialchars($p['no_hp']) ?></div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <i class="fa-regular fa-user"></i>
            <p>Belum ada pengurus yang terdaftar untuk organisasi ini.</p>
        </div>
    <?php endif; ?>

    <!-- Daftar Kegiatan -->
    <h3 class="section-title"><i class="fa-regular fa-calendar" style="color:#FFA007;"></i> Kegiatan</h3>
    <?php if (count($kegiatan) > 0): ?>
        <div class="kegiatan-list">
            <?php foreach ($kegiatan as $k): ?>
                <div class="kegiatan-item">
                    <div class="info">
                        <span class="judul">
                            <a href="detail_kegiatan.php?id=<?= $k['id_konten'] ?>&back=<?= urlencode('/MASAGENA-ITH/dashboard/mahasiswa/organisasi_detail.php?id=' . $id_organisasi) ?>">
                                <?= htmlspecialchars($k['judul']) ?>
                            </a>
                        </span>
                        <span class="meta">
                            <i class="fa-regular fa-clock"></i> <?= date('d M Y', strtotime($k['tanggal_kegiatan'])) ?>
                        </span>
                        <?php if (!empty($k['kategori'])): ?>
                            <span class="meta" style="background:#FFA007; color:#071C34; padding:0.1rem 0.5rem; border-radius:50px; font-size:0.7rem; font-weight:600;">
                                <?= htmlspecialchars($k['kategori']) ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    <a href="detail_kegiatan.php?id=<?= $k['id_konten'] ?>&back=<?= urlencode('/MASAGENA-ITH/dashboard/mahasiswa/organisasi_detail.php?id=' . $id_organisasi) ?>" class="btn-detail">Detail</a>
                </div>
            <?php endforeach; ?>
        </div>
        <?php if (count($kegiatan) >= 5): ?>
            <div style="text-align:center; margin-top:1rem;">
                <a href="kegiatan.php?filter_organisasi=<?= $id_organisasi ?>" class="btn-back" style="background:#FFA007; border-color:#FFA007;">
                    Lihat Semua Kegiatan <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="empty-state">
            <i class="fa-regular fa-calendar-xmark"></i>
            <p>Belum ada kegiatan yang diselenggarakan oleh organisasi ini.</p>
        </div>
    <?php endif; ?>

</div>

<?php include '../../include/footer.php'; ?>
=======
// detail_organisasi.php
// Pastikan letak file database sesuai dengan struktur folder Anda
require_once '../../config/database.php'; 

// Ambil ID organisasi dari parameter URL
$id_organisasi = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

if (!$id_organisasi) {
    // Redirect kembali ke halaman organisasi jika ID tidak valid
    header("Location: /MASAGENA-ITH/organisasi.php");
    exit;
}

// Ambil data organisasi dari database berdasarkan ID
try {
    $stmt = $pdo->prepare("SELECT * FROM organisasi WHERE id_organisasi = :id");
    $stmt->execute([':id' => $id_organisasi]);
    $org = $stmt->fetch(PDO::FETCH_ASSOC);

    // Jika organisasi tidak ditemukan, lempar kembali
    if (!$org) {
        header("Location: /MASAGENA-ITH/organisasi.php");
        exit;
    }

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

// Sertakan header halaman dari folder include root web
include '../../include/header.php';
?>

<style>
    /* Styling khusus untuk Detail Organisasi menyesuaikan tema MASAGENA-ITH */
    .detail-organisasi {
        background: var(--white);
        border-radius: var(--radius);
        padding: 2rem;
        box-shadow: var(--shadow-sm);
        margin-top: 1rem;
    }

    .detail-organisasi .org-badge {
        display: inline-block;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        background-color: rgba(255, 160, 7, 0.15);
        color: var(--accent-dark);
        padding: 0.3rem 0.8rem;
        border-radius: 20px;
        font-weight: 700;
        margin-bottom: 1rem;
    }

    .detail-organisasi h1 {
        color: var(--primary);
        font-size: 2rem;
        margin-bottom: 0.5rem;
    }

    .detail-organisasi .meta-info {
        color: var(--text-muted);
        font-size: 0.9rem;
        margin-bottom: 1.5rem;
        display: flex;
        flex-wrap: wrap;
        gap: 1.5rem;
        border-bottom: 1px solid var(--border);
        padding-bottom: 1rem;
    }

    .detail-organisasi .meta-info i {
        color: var(--accent);
        width: 20px;
    }

    .deskripsi-org {
        line-height: 1.8;
        color: var(--text-dark);
        margin-bottom: 2rem;
    }

    .visi-misi-section {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 2rem;
        margin-top: 2rem;
        background-color: var(--bg-body);
        padding: 1.5rem;
        border-radius: var(--radius);
    }

    @media (max-width: 768px) {
        .visi-misi-section {
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }
    }

    .visi-misi-box h3 {
        color: var(--primary);
        border-left: 4px solid var(--accent);
        padding-left: 0.5rem;
        margin-bottom: 0.75rem;
        font-size: 1.2rem;
    }

    .visi-misi-box p, .visi-misi-box ul {
        font-size: 0.9rem;
        color: var(--text-dark);
    }
    
    .action-group {
        margin-top: 2rem;
        display: flex;
        gap: 1rem;
    }
</style>

<div class="dashboard-welcome">
    <h1>Profil Lembaga Kemahasiswaan</h1>
    <p>Informasi detail, visi, misi, dan kepengurusan dari <strong><?= htmlspecialchars($org['nama_organisasi']) ?></strong>.</p>
</div>

<div class="detail-organisasi">
    <span class="org-badge"><?= htmlspecialchars($org['kategori'] ?? 'Organisasi Mahasiswa') ?></span>
    
    <h1><?= htmlspecialchars($org['nama_organisasi']) ?> (<?= htmlspecialchars($org['singkatan'] ?? '') ?>)</h1>
    
    <div class="meta-info">
        <span><i class="fas fa-users"></i> Anggota Aktif: <strong><?= htmlspecialchars($org['jumlah_anggota'] ?? 'Belum diset') ?> Orang</strong></span>
        <span><i class="fas fa-user-tie"></i> Ketua Umum: <strong><?= htmlspecialchars($org['ketua_umum'] ?? '-') ?></strong></span>
        <span><i class="fas fa-door-open"></i> Sekretariat: <strong><?= htmlspecialchars($org['sekretariat'] ?? '-') ?></strong></span>
    </div>

    <div class="deskripsi-org">
        <h3>Tentang Organisasi</h3>
        <p><?= nl2br(htmlspecialchars($org['deskripsi'] ?? 'Deskripsi belum ditambahkan.')) ?></p>
    </div>

    <?php if (!empty($org['visi']) || (!empty($org['misi']))): ?>
    <div class="visi-misi-section">
        <div class="visi-misi-box">
            <h3>Visi</h3>
            <p><?= nl2br(htmlspecialchars($org['visi'] ?? '-')) ?></p>
        </div>
        <div class="visi-misi-box">
            <h3>Misi</h3>
            <?php if (!empty($org['misi'])): ?>
                <?php 
                // Asumsi Misi berupa teks per baris (menggunakan enter) atau list HTML
                echo nl2br(htmlspecialchars($org['misi'])); 
                ?>
            <?php else: ?>
                <p>-</p>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <div class="action-group">
        <a href="/MASAGENA-ITH/organisasi.php" class="btn-cancel"><i class="fas fa-arrow-left"></i> Kembali</a>
        <a href="pendaftaran_anggota.php?id=<?= $org['id_organisasi'] ?>" class="btn-daftar">Gabung Organisasi</a>
    </div>

</div>

<?php 
// Sertakan footer halaman dari folder include root web
include '../../include/footer.php'; 
?>
>>>>>>> 9e4b9b789696603edaa30fd5aeb277ddc8239c7c

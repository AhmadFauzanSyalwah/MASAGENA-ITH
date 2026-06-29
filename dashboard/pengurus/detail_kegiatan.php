<?php
// dashboard/pengurus/detail_kegiatan.php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['peran'] != 'pengurus') {
    header('Location: ../../auth/login.php');
    exit;
}

require_once '../../config/database.php';

$id_konten = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id_konten) {
    header('Location: kelola_konten.php');
    exit;
}

// ============================================================
// AMBIL DATA KEGIATAN
// ============================================================
$stmt = $pdo->prepare("SELECT k.*, o.id_organisasi, o.nama_organisasi, o.jenis
                       FROM konten_kegiatan k
                       JOIN organisasi o ON k.id_organisasi = o.id_organisasi
                       WHERE k.id_konten = ?");
$stmt->execute([$id_konten]);
$kegiatan = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$kegiatan) {
    $_SESSION['error'] = 'Kegiatan tidak ditemukan.';
    header('Location: kelola_konten.php');
    exit;
}

// ============================================================
// CEK AKSES
// ============================================================
$id_pengurus = $_SESSION['user_id'];
$stmtCek = $pdo->prepare("SELECT id_organisasi FROM pengurus_organisasi WHERE id_pengurus = ?");
$stmtCek->execute([$id_pengurus]);
$org_pengurus = $stmtCek->fetchColumn();

if ($org_pengurus != $kegiatan['id_organisasi']) {
    $_SESSION['error'] = 'Anda tidak memiliki akses untuk melihat detail kegiatan ini.';
    header('Location: kelola_konten.php');
    exit;
}

// ============================================================
// AMBIL DAFTAR PESERTA
// ============================================================
$stmtPeserta = $pdo->prepare("
    SELECT p.*, m.nama, m.nim, m.prodi, m.kontak, m.email
    FROM pendaftaran p
    JOIN tbmahasiswa m ON p.id_mahasiswa = m.id_mahasiswa
    WHERE p.id_konten = ?
    ORDER BY 
        CASE p.status_pendaftaran
            WHEN 'menunggu' THEN 1
            WHEN 'diterima' THEN 2
            WHEN 'ditolak' THEN 3
            ELSE 4
        END,
        p.tanggal_daftar ASC
");
$stmtPeserta->execute([$id_konten]);
$peserta = $stmtPeserta->fetchAll();

// ============================================================
// HITUNG STATISTIK
// ============================================================
$total_menunggu = 0;
$total_diterima = 0;
$total_ditolak = 0;
foreach ($peserta as $p) {
    if ($p['status_pendaftaran'] == 'menunggu') $total_menunggu++;
    elseif ($p['status_pendaftaran'] == 'diterima') $total_diterima++;
    elseif ($p['status_pendaftaran'] == 'ditolak') $total_ditolak++;
}

$page_context = 'kelola_kegiatan';
include '../../include/header.php';
?>

<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/profil.css?v=<?= time() ?>">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

<style>
/* ============================================================
   DETAIL KEGIATAN - KONSISTEN DENGAN KELOLA KONTEN
   ============================================================ */
.detail-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 1.5rem;
}

/* ===== HEADER ===== */
.detail-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 1.5rem;
}
.detail-header .title-group h2 {
    font-size: 1.8rem;
    font-weight: 700;
    color: #071C34;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.detail-header .title-group h2 i {
    color: #FFA007;
}
.detail-header .title-group .subtitle {
    font-size: 0.85rem;
    color: #64748b;
    margin: 0;
}
.detail-header .title-group .subtitle i {
    color: #FFA007;
}

/* ===== TOMBOL KEMBALI & EDIT ===== */
.btn-back, .btn-edit {
    display: inline-flex;
    align-items: center;
    gap: 0.6rem;
    border: none;
    border-radius: 50px;
    padding: 0.5rem 1.8rem;
    font-weight: 600;
    font-size: 0.85rem;
    text-decoration: none;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
.btn-back {
    background: #071C34;
    color: #fff;
}
.btn-back:hover {
    background: #FFA007;
    color: #071C34;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(255,160,7,0.3);
}
.btn-edit {
    background: #FFA007;
    color: #071C34;
}
.btn-edit:hover {
    background: #071C34;
    color: #fff;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(7,28,52,0.3);
}

/* ===== DETAIL CARD ===== */
.detail-card {
    background: #ffffff;
    border-radius: 16px;
    border: 1px solid #e9ecef;
    padding: 1.5rem 2rem;
    margin-bottom: 2rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}
.detail-card .judul {
    font-size: 1.8rem;
    font-weight: 700;
    color: #071C34;
    margin: 0 0 0.5rem 0;
}
.detail-card .meta-row {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem 1.5rem;
    margin-bottom: 0.8rem;
    padding-bottom: 0.8rem;
    border-bottom: 1px solid #f1f5f9;
}
.detail-card .meta-row .meta-item {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.85rem;
    color: #64748b;
}
.detail-card .meta-row .meta-item i {
    color: #FFA007;
    width: 18px;
}
.detail-card .meta-row .meta-item strong {
    color: #071C34;
}
.detail-card .deskripsi {
    background: #f8fafc;
    padding: 1rem 1.2rem;
    border-radius: 10px;
    border-left: 4px solid #FFA007;
    color: #1e293b;
    line-height: 1.8;
    white-space: pre-wrap;
    margin: 1rem 0;
}
.detail-card .info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
    gap: 1rem;
    padding-top: 1rem;
    border-top: 1px solid #e9ecef;
}
.detail-card .info-grid .item {
    padding: 0.5rem 0.8rem;
    background: #f8fafc;
    border-radius: 10px;
}
.detail-card .info-grid .item label {
    display: block;
    font-size: 0.65rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #94a3b8;
    font-weight: 700;
}
.detail-card .info-grid .item .value {
    font-weight: 600;
    color: #071C34;
    font-size: 0.95rem;
    margin-top: 0.1rem;
}
.detail-card .info-grid .item .value a {
    color: #3b82f6;
    text-decoration: none;
}
.detail-card .info-grid .item .value a:hover {
    text-decoration: underline;
}
.detail-card .status-badge-large {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.3rem 1.2rem;
    border-radius: 50px;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    background: #dcfce7;
    color: #166534;
}
.detail-card .status-badge-large.draft {
    background: #fef3c7;
    color: #92400e;
}

/* ===== STATISTIK BOX ===== */
.statistik-box {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 1.5rem;
}
.statistik-box .stat-item {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    padding: 0.5rem 1.5rem;
    border-radius: 50px;
    font-size: 0.85rem;
    font-weight: 600;
    color: #071C34;
    background: #ffffff;
    border: 1px solid #e9ecef;
    transition: all 0.2s;
}
.statistik-box .stat-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.06);
}
.statistik-box .stat-item .number {
    font-weight: 700;
    font-size: 1.1rem;
}
.statistik-box .stat-item.menunggu {
    border-color: #f59e0b;
    background: #fffbeb;
}
.statistik-box .stat-item.menunggu i {
    color: #f59e0b;
}
.statistik-box .stat-item.diterima {
    border-color: #22c55e;
    background: #f0fdf4;
}
.statistik-box .stat-item.diterima i {
    color: #22c55e;
}
.statistik-box .stat-item.ditolak {
    border-color: #dc2626;
    background: #fef2f2;
}
.statistik-box .stat-item.ditolak i {
    color: #dc2626;
}
.statistik-box .stat-item.total {
    border-color: #071C34;
    background: #f8fafc;
}
.statistik-box .stat-item.total i {
    color: #071C34;
}

/* ===== TABLE WRAPPER ===== */
.table-wrapper {
    background: #ffffff;
    border-radius: 16px;
    border: 1px solid #e9ecef;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}
.table-wrapper .table-header {
    padding: 1rem 1.5rem;
    background: #f8fafc;
    border-bottom: 1px solid #e9ecef;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.5rem;
}
.table-wrapper .table-header h3 {
    margin: 0;
    font-size: 1rem;
    font-weight: 700;
    color: #071C34;
}
.table-wrapper .table-header h3 i {
    color: #FFA007;
    margin-right: 0.4rem;
}
.table-wrapper .table-header .badge-count {
    background: #071C34;
    color: #fff;
    padding: 0.15rem 0.8rem;
    border-radius: 50px;
    font-size: 0.7rem;
    font-weight: 700;
}

.table-wrapper table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.85rem;
}
.table-wrapper thead {
    background: #f8fafc;
}
.table-wrapper th {
    padding: 0.6rem 1rem;
    text-align: left;
    font-weight: 700;
    font-size: 0.65rem;
    text-transform: uppercase;
    color: #64748b;
    letter-spacing: 0.5px;
    border-bottom: 2px solid #e9ecef;
}
.table-wrapper td {
    padding: 0.6rem 1rem;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: middle;
}
.table-wrapper tbody tr:hover {
    background: #f8fafc;
}
.table-wrapper tbody tr:last-child td {
    border-bottom: none;
}

/* ===== STATUS BADGE DI TABEL ===== */
.status-badge-sm {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    padding: 0.15rem 0.8rem;
    border-radius: 50px;
    font-size: 0.65rem;
    font-weight: 700;
    color: #ffffff;
}
.status-badge-sm.menunggu {
    background: #f59e0b;
}
.status-badge-sm.diterima {
    background: #22c55e;
}
.status-badge-sm.ditolak {
    background: #dc2626;
}

/* ===== EMPTY STATE ===== */
.empty-state {
    text-align: center;
    padding: 3rem 1rem;
    color: #94a3b8;
}
.empty-state i {
    font-size: 2.5rem;
    color: #cbd5e0;
    display: block;
    margin-bottom: 0.5rem;
}
.empty-state p {
    margin: 0;
    font-weight: 500;
}
.empty-state .sub {
    font-size: 0.8rem;
    color: #cbd5e0;
    margin-top: 0.2rem;
}

/* ===== RESPONSIVE ===== */
@media (max-width: 992px) {
    .detail-container {
        padding: 0 1rem;
    }
    .detail-card {
        padding: 1.5rem;
    }
    .detail-card .judul {
        font-size: 1.5rem;
    }
}
@media (max-width: 768px) {
    .detail-container {
        padding: 0 0.5rem;
    }
    .detail-header {
        flex-direction: column;
        align-items: stretch;
        text-align: center;
    }
    .detail-header .title-group h2 {
        font-size: 1.4rem;
        justify-content: center;
    }
    .btn-back, .btn-edit {
        justify-content: center;
    }
    .detail-card {
        padding: 1.2rem;
    }
    .detail-card .judul {
        font-size: 1.3rem;
    }
    .statistik-box .stat-item {
        flex: 1;
        justify-content: center;
        min-width: 120px;
    }
    .table-wrapper {
        overflow-x: auto;
    }
    .table-wrapper table {
        min-width: 700px;
        font-size: 0.8rem;
    }
}
@media (max-width: 480px) {
    .detail-card .info-grid {
        grid-template-columns: 1fr;
    }
    .detail-card .meta-row {
        flex-direction: column;
        gap: 0.2rem;
    }
}
</style>

<div class="detail-container">

    <!-- ===== HEADER ===== -->
    <div class="detail-header">
        <div class="title-group">
            <h2><i class="fas fa-info-circle"></i> Detail Kegiatan</h2>
            <p class="subtitle">
                <i class="fas fa-calendar"></i> Informasi lengkap kegiatan dan daftar peserta
            </p>
        </div>
        <div style="display:flex; gap:0.8rem; flex-wrap:wrap;">
            <a href="kelola_konten.php" class="btn-back">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
            <a href="edit_konten.php?id=<?= $id_konten ?>" class="btn-edit">
                <i class="fas fa-edit"></i> Edit
            </a>
        </div>
    </div>

    <!-- ===== DETAIL KEGIATAN ===== -->
    <div class="detail-card">
        <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:0.5rem;">
            <h1 class="judul"><?= htmlspecialchars($kegiatan['judul']) ?></h1>
            <span class="status-badge-large <?= $kegiatan['status_publikasi'] ?? 'draft' ?>">
                <i class="fas <?= ($kegiatan['status_publikasi'] ?? 'draft') == 'publik' ? 'fa-globe' : 'fa-eye-slash' ?>"></i>
                <?= ucfirst($kegiatan['status_publikasi'] ?? 'Draft') ?>
            </span>
        </div>

        <div class="meta-row">
            <span class="meta-item">
                <i class="fas fa-building"></i> <strong><?= htmlspecialchars($kegiatan['nama_organisasi']) ?></strong>
            </span>
            <span class="meta-item">
                <i class="fas fa-tag"></i> <?= htmlspecialchars($kegiatan['jenis']) ?>
            </span>
            <span class="meta-item">
                <i class="fas fa-calendar"></i> <?= date('d M Y', strtotime($kegiatan['tanggal_kegiatan'])) ?>
            </span>
            <?php if (!empty($kegiatan['kategori'])): ?>
                <span class="meta-item">
                    <i class="fas fa-folder"></i> <?= htmlspecialchars($kegiatan['kategori']) ?>
                </span>
            <?php endif; ?>
        </div>

        <div class="deskripsi">
            <?= nl2br(htmlspecialchars($kegiatan['deskripsi'])) ?>
        </div>

        <div class="info-grid">
            <div class="item">
                <label><i class="fas fa-users"></i> Kuota</label>
                <div class="value"><?= $kegiatan['kuota_maks'] ?? 50 ?> peserta</div>
            </div>
            <?php if (!empty($kegiatan['lampiran'])): ?>
                <div class="item">
                    <label><i class="fas fa-paperclip"></i> Lampiran</label>
                    <div class="value">
                        <a href="/MASAGENA-ITH/uploads/kegiatan/<?= htmlspecialchars($kegiatan['lampiran']) ?>" target="_blank">
                            <i class="fas fa-file"></i> <?= htmlspecialchars($kegiatan['lampiran']) ?>
                        </a>
                    </div>
                </div>
            <?php endif; ?>
            <div class="item">
                <label><i class="fas fa-calendar-plus"></i> Dibuat</label>
                <div class="value"><?= date('d M Y H:i', strtotime($kegiatan['created_at'])) ?></div>
            </div>
        </div>
    </div>

    <!-- ===== STATISTIK ===== -->
    <div class="statistik-box">
        <span class="stat-item menunggu">
            <i class="fas fa-clock"></i>
            <span>Menunggu: <span class="number"><?= $total_menunggu ?></span></span>
        </span>
        <span class="stat-item diterima">
            <i class="fas fa-check-circle"></i>
            <span>Diterima: <span class="number"><?= $total_diterima ?></span></span>
        </span>
        <span class="stat-item ditolak">
            <i class="fas fa-times-circle"></i>
            <span>Ditolak: <span class="number"><?= $total_ditolak ?></span></span>
        </span>
        <span class="stat-item total">
            <i class="fas fa-users"></i>
            <span>Total: <span class="number"><?= count($peserta) ?></span></span>
        </span>
    </div>

    <!-- ===== DAFTAR PESERTA ===== -->
    <div class="table-wrapper">
        <div class="table-header">
            <h3><i class="fas fa-list"></i> Daftar Peserta</h3>
            <span class="badge-count"><?= count($peserta) ?> peserta</span>
        </div>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>NIM</th>
                    <th>Nama</th>
                    <th>Prodi</th>
                    <th>Kontak</th>
                    <th>Tanggal Daftar</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($peserta) > 0): ?>
                    <?php $no = 1; foreach ($peserta as $p): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= htmlspecialchars($p['nim'] ?? '-') ?></td>
                            <td><strong><?= htmlspecialchars($p['nama']) ?></strong></td>
                            <td><?= htmlspecialchars($p['prodi'] ?? '-') ?></td>
                            <td>
                                <?php if (!empty($p['kontak'])): ?>
                                    <a href="tel:<?= htmlspecialchars($p['kontak']) ?>" style="color:#3b82f6; text-decoration:none; font-weight:600;">
                                        <i class="fas fa-phone"></i> <?= htmlspecialchars($p['kontak']) ?>
                                    </a>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td style="font-size:0.8rem; color:#64748b;">
                                <?= date('d M Y', strtotime($p['tanggal_daftar'])) ?>
                                <br><small style="color:#94a3b8;"><?= date('H:i', strtotime($p['tanggal_daftar'])) ?></small>
                            </td>
                            <td>
                                <span class="status-badge-sm <?= $p['status_pendaftaran'] ?>">
                                    <i class="fas <?= $p['status_pendaftaran'] == 'diterima' ? 'fa-check' : ($p['status_pendaftaran'] == 'ditolak' ? 'fa-times' : 'fa-clock') ?>"></i>
                                    <?= ucfirst($p['status_pendaftaran']) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7">
                            <div class="empty-state">
                                <i class="fas fa-users-slash"></i>
                                <p>Belum ada peserta yang mendaftar</p>
                                <div class="sub">Belum ada pendaftaran untuk kegiatan ini</div>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

<?php include '../../include/footer.php'; ?>
<?php
// dashboard/pengurus/detail_kegiatan.php
session_start();

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['peran'], ['pengurus', 'admin'])) {
    header('Location: ../../auth/login.php');
    exit;
}

require_once '../../config/database.php';

$id_konten = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id_konten) {
    $_SESSION['error'] = 'ID kegiatan tidak valid.';
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
// CEK AKSES (jika bukan admin)
// ============================================================
$id_user = $_SESSION['user_id'];
$is_admin = ($_SESSION['peran'] === 'admin');

if (!$is_admin) {
    $stmtOrg = $pdo->prepare("SELECT id_organisasi FROM pengurus_organisasi WHERE id_pengurus = ?");
    $stmtOrg->execute([$id_user]);
    $org_pengurus = $stmtOrg->fetchColumn();
    if ($org_pengurus != $kegiatan['id_organisasi']) {
        $_SESSION['error'] = 'Anda tidak memiliki akses untuk melihat detail kegiatan ini.';
        header('Location: kelola_konten.php');
        exit;
    }
}

// ============================================================
// HITUNG STATISTIK SEMUA PESERTA (TANPA FILTER)
// ============================================================
$stmtAll = $pdo->prepare("SELECT status_pendaftaran FROM pendaftaran WHERE id_konten = ?");
$stmtAll->execute([$id_konten]);
$allPesertaData = $stmtAll->fetchAll(PDO::FETCH_ASSOC);
$total_semua = count($allPesertaData);
$total_menunggu = 0;
$total_diterima = 0;
$total_ditolak = 0;
foreach ($allPesertaData as $ap) {
    if ($ap['status_pendaftaran'] == 'menunggu') $total_menunggu++;
    elseif ($ap['status_pendaftaran'] == 'diterima') $total_diterima++;
    elseif ($ap['status_pendaftaran'] == 'ditolak') $total_ditolak++;
}

// ============================================================
// FILTER STATUS
// ============================================================
$status = $_GET['status'] ?? 'semua';
$allowedStatus = ['semua', 'menunggu', 'diterima', 'ditolak'];
if (!in_array($status, $allowedStatus, true)) {
    $status = 'semua';
}

// ============================================================
// AMBIL DAFTAR PESERTA DENGAN FILTER
// ============================================================
$where = "p.id_konten = ?";
$params = [$id_konten];

if ($status !== 'semua') {
    $where .= " AND p.status_pendaftaran = ?";
    $params[] = $status;
}

$sql = "SELECT
            p.id_pendaftaran,
            p.tanggal_daftar,
            p.status_pendaftaran,
            m.nama,
            m.nim,
            m.prodi,
            m.kontak,
            m.email
        FROM pendaftaran p
        JOIN tbmahasiswa m ON m.id_mahasiswa = p.id_mahasiswa
        WHERE $where
        ORDER BY 
            CASE p.status_pendaftaran
                WHEN 'menunggu' THEN 1
                WHEN 'diterima' THEN 2
                WHEN 'ditolak' THEN 3
                ELSE 4
            END,
            p.tanggal_daftar ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$peserta = $stmt->fetchAll();

$page_context = 'kelola_kegiatan';
include '../../include/header.php';
?>

<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/profil.css?v=<?= time() ?>">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

<?php
// ============================================================
// CEK PARAMETER UNTUK POPUP NOTIFIKASI
// ============================================================
if (isset($_GET['msg']) && isset($_GET['text'])) {
    $msg_text = htmlspecialchars($_GET['text']);
    echo '<script>
        alert("' . $msg_text . '");
        if (window.history && window.history.replaceState) {
            var url = window.location.href.split("?")[0];
            window.history.replaceState({}, document.title, url);
        }
    </script>';
}
?>

<style>
/* ============================================
   DETAIL KEGIATAN - KONSISTEN DENGAN PENDAFTARAN
   ============================================ */
.detail-container {
    max-width: 100%;
    margin: 0;
    padding: 0 1rem;
    box-sizing: border-box;
}

/* Header */
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
}
.detail-header .title-group .subtitle {
    font-size: 0.85rem;
    color: #64748b;
    margin: 0;
}
.detail-header .btn-kembali {
    background: #FFA007;
    color: #071C34;
    padding: 0.4rem 1.5rem;
    border-radius: 50px;
    font-weight: 600;
    text-decoration: none;
    font-size: 0.9rem;
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    border: 2px solid #FFA007;
}
.detail-header .btn-kembali:hover {
    background: #071C34;
    color: #ffffff;
    border-color: #071C34;
}

/* ===== INFO CARD ===== */
.info-card {
    background: #ffffff;
    border-radius: 16px;
    border: 1px solid #e9ecef;
    padding: 1.5rem 2rem;
    margin-bottom: 2rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}
.info-card .judul {
    font-size: 1.6rem;
    font-weight: 700;
    color: #071C34;
    margin: 0 0 0.3rem 0;
}
.info-card .meta-row {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem 1.5rem;
    margin-bottom: 0.5rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid #f1f5f9;
}
.info-card .meta-row .meta-item {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.85rem;
    color: #64748b;
}
.info-card .meta-row .meta-item i {
    color: #FFA007;
    width: 18px;
}
.info-card .meta-row .meta-item strong {
    color: #071C34;
}
.info-card .deskripsi {
    background: #f8fafc;
    padding: 1rem 1.2rem;
    border-radius: 10px;
    border-left: 4px solid #FFA007;
    color: #1e293b;
    line-height: 1.8;
    white-space: pre-wrap;
    margin: 0.5rem 0 0 0;
}
.status-badge-large {
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
.status-badge-large.draft {
    background: #fef3c7;
    color: #92400e;
}

/* ===== FILTER TABS DENGAN STATISTIK ===== */
.filter-tabs {
    display: flex;
    flex-wrap: wrap;
    gap: 0.3rem;
    margin-bottom: 1.5rem;
    background: #f8fafc;
    border-radius: 50px;
    padding: 0.2rem;
    border: 1px solid #e9ecef;
}
.filter-tabs form {
    display: contents;
}
.filter-tabs button {
    background: transparent;
    border: none;
    padding: 0.3rem 1.2rem;
    border-radius: 50px;
    font-weight: 600;
    font-size: 0.85rem;
    color: #64748b;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
}
.filter-tabs button:hover {
    color: #071C34;
}
.filter-tabs button.active {
    background: #071C34;
    color: #fff;
}
.filter-tabs .badge-filter {
    background: rgba(255,255,255,0.2);
    padding: 0.1rem 0.6rem;
    border-radius: 20px;
    font-size: 0.7rem;
    font-weight: 700;
    margin-left: 0.2rem;
    color: #64748b;
}
.filter-tabs button.active .badge-filter {
    background: rgba(255,255,255,0.25);
    color: #fff;
}

/* ===== TABLE CARD ===== */
.table-card {
    background: #ffffff;
    border-radius: 16px;
    border: 1px solid #e9ecef;
    overflow-x: auto;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}
.table-card table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.9rem;
    min-width: 700px;
}
.table-card thead {
    background: #f8fafc;
    border-bottom: 2px solid #e9ecef;
}
.table-card th {
    padding: 0.7rem 1rem;
    text-align: left;
    font-weight: 700;
    font-size: 0.7rem;
    text-transform: uppercase;
    color: #64748b;
    letter-spacing: 0.3px;
    white-space: nowrap;
}
.table-card th.status-header,
.table-card th.aksi-header {
    text-align: center;
}
.table-card td.status-cell,
.table-card td.aksi-cell {
    text-align: center;
    vertical-align: middle;
}
.table-card td {
    padding: 0.7rem 1rem;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: middle;
}
.table-card tbody tr:hover {
    background: #f8fafc;
}
.table-card .text-center {
    text-align: center;
    color: #94a3b8;
    padding: 2.5rem 0;
}
.table-card .text-center i {
    font-size: 2.5rem;
    margin-bottom: 0.5rem;
    display: block;
    color: #cbd5e0;
}

/* ===== STATUS BADGE ===== */
.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.25rem 1rem;
    border-radius: 50px;
    font-size: 0.75rem;
    font-weight: 700;
    color: #ffffff;
    letter-spacing: 0.3px;
    white-space: nowrap;
}
.status-badge i {
    font-size: 0.7rem;
}
.status-badge.menunggu {
    background: #FFA007;
}
.status-badge.diterima {
    background: #071C34;
}
.status-badge.ditolak {
    background: #dc2626;
}

/* ===== TOMBOL AKSI ===== */
.action-inline {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
    flex-wrap: nowrap;
}
.action-inline form {
    display: inline;
}

.mini-btn {
    padding: 0.25rem 1.2rem;
    border-radius: 50px;
    font-size: 0.75rem;
    font-weight: 700;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    font-family: inherit;
    white-space: nowrap;
    min-width: 70px;
    justify-content: center;
    border-width: 2px;
    border-style: solid;
}

/* Terima */
.mini-btn.accept {
    background: transparent;
    color: #071C34;
    border-color: #071C34;
}
.mini-btn.accept:hover {
    background: #071C34;
    color: #ffffff;
    border-color: #071C34;
}

/* Tolak */
.mini-btn.reject {
    background: transparent;
    color: #dc2626;
    border-color: #dc2626;
}
.mini-btn.reject:hover {
    background: #dc2626;
    color: #ffffff;
    border-color: #dc2626;
}

/* Reset */
.mini-btn.reset {
    background: transparent;
    color: #FFA007;
    border-color: #FFA007;
}
.mini-btn.reset:hover {
    background: #FFA007;
    color: #ffffff;
    border-color: #FFA007;
}

/* ============================================================
   RESPONSIVE
   ============================================================ */
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
    }
    .detail-header .btn-kembali {
        justify-content: center;
    }
    .info-card {
        padding: 1.2rem;
    }
    .info-card .judul {
        font-size: 1.3rem;
    }
    .filter-tabs {
        justify-content: center;
        border-radius: 12px;
        padding: 0.5rem;
        gap: 0.2rem;
        flex-wrap: wrap;
    }
    .filter-tabs button {
        font-size: 0.75rem;
        padding: 0.2rem 0.8rem;
    }
    .table-card {
        overflow-x: auto;
    }
    .table-card table {
        font-size: 0.8rem;
        min-width: 700px;
    }
    .table-card th, .table-card td {
        padding: 0.4rem 0.6rem;
    }
    .action-inline {
        flex-direction: column;
        gap: 0.3rem;
    }
    .action-inline form {
        display: block;
    }
    .mini-btn {
        width: 100%;
        justify-content: center;
        padding: 0.3rem 0.5rem;
        min-width: unset;
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
        <a href="kelola_konten.php" class="btn-kembali">
            <i class="fas fa-arrow-left"></i> Kembali ke Kelola Konten
        </a>
    </div>

    <!-- ===== INFO KEGIATAN ===== -->
    <div class="info-card">
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
            <span class="meta-item">
                <i class="fas fa-users"></i> Kuota: <?= $kegiatan['kuota_maks'] ?? 50 ?>
            </span>
        </div>

        <div class="deskripsi">
            <?= nl2br(htmlspecialchars($kegiatan['deskripsi'])) ?>
        </div>

        <?php if (!empty($kegiatan['lampiran'])): ?>
            <div style="margin-top:0.5rem; font-size:0.85rem;">
                <i class="fas fa-paperclip" style="color:#FFA007;"></i>
                <a href="/MASAGENA-ITH/uploads/kegiatan/<?= htmlspecialchars($kegiatan['lampiran']) ?>" target="_blank" style="color:#3b82f6; text-decoration:none; font-weight:600;">
                    <?= htmlspecialchars($kegiatan['lampiran']) ?>
                </a>
            </div>
        <?php endif; ?>
    </div>

    <!-- ===== FILTER TABS DENGAN STATISTIK ===== -->
    <div class="filter-tabs">
        <form method="GET" action="<?= $_SERVER['PHP_SELF'] ?>">
            <input type="hidden" name="id" value="<?= $id_konten ?>">
            <button type="submit" name="status" value="semua" class="<?= $status === 'semua' ? 'active' : '' ?>">
                <i class="fas fa-list"></i> Semua <span class="badge-filter"><?= $total_semua ?></span>
            </button>
            <button type="submit" name="status" value="menunggu" class="<?= $status === 'menunggu' ? 'active' : '' ?>">
                <i class="fas fa-clock"></i> Menunggu <span class="badge-filter"><?= $total_menunggu ?></span>
            </button>
            <button type="submit" name="status" value="diterima" class="<?= $status === 'diterima' ? 'active' : '' ?>">
                <i class="fas fa-check-circle"></i> Diterima <span class="badge-filter"><?= $total_diterima ?></span>
            </button>
            <button type="submit" name="status" value="ditolak" class="<?= $status === 'ditolak' ? 'active' : '' ?>">
                <i class="fas fa-times-circle"></i> Ditolak <span class="badge-filter"><?= $total_ditolak ?></span>
            </button>
        </form>
    </div>

    <!-- ===== TABLE PESERTA ===== -->
    <div class="table-card">
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Mahasiswa</th>
                    <th>Kontak</th>
                    <th>Prodi</th>
                    <th>Tanggal Daftar</th>
                    <th class="status-header">Status</th>
                    <th class="aksi-header">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($peserta)): ?>
                    <tr>
                        <td colspan="7">
                            <div class="text-center">
                                <i class="fa-regular fa-users-slash"></i>
                                <p style="margin:0; color:#94a3b8; font-weight:500;">Belum ada peserta yang mendaftar</p>
                                <p style="margin:0; font-size:0.8rem; color:#cbd5e0;">
                                    <?php if ($status !== 'semua'): ?>
                                        Tidak ada peserta dengan status "<?= ucfirst($status) ?>"
                                    <?php else: ?>
                                        Belum ada pendaftaran untuk kegiatan ini
                                    <?php endif; ?>
                                </p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>

                <?php $no = 1; foreach ($peserta as $p): ?>
                    <tr>
                        <td style="font-weight:600; color:#071C34;"><?= $no++ ?></td>
                        <td>
                            <div style="font-weight:600; color:#071C34;"><?= htmlspecialchars($p['nama']) ?></div>
                            <div style="font-size:0.75rem; color:#94a3b8;">NIM: <?= htmlspecialchars($p['nim']) ?></div>
                        </td>
                        <td>
                            <div style="font-size:0.8rem; color:#64748b;"><?= htmlspecialchars($p['kontak'] ?? '-') ?></div>
                            <div style="font-size:0.7rem; color:#94a3b8;"><?= htmlspecialchars($p['email']) ?></div>
                        </td>
                        <td><?= htmlspecialchars($p['prodi'] ?? '-') ?></td>
                        <td style="font-size:0.85rem; color:#64748b;">
                            <i class="fa-regular fa-clock"></i> <?= date('d M Y', strtotime($p['tanggal_daftar'])) ?>
                            <br><small style="color:#94a3b8;"><?= date('H:i', strtotime($p['tanggal_daftar'])) ?></small>
                        </td>
                        <td class="status-cell">
                            <span class="status-badge <?= $p['status_pendaftaran'] ?>">
                                <i class="fas <?= $p['status_pendaftaran'] == 'menunggu' ? 'fa-clock' : ($p['status_pendaftaran'] == 'diterima' ? 'fa-check' : 'fa-times') ?>"></i>
                                <?= ucfirst($p['status_pendaftaran']) ?>
                            </span>
                        </td>
                        <td class="aksi-cell">
                            <div class="action-inline">
                                <!-- Terima -->
                                <?php if ($p['status_pendaftaran'] != 'diterima'): ?>
                                <form action="update_status_pendaftaran.php" method="POST" onsubmit="return confirm('Yakin ingin menerima pendaftaran dari <?= htmlspecialchars($p['nama']) ?>?')">
                                    <input type="hidden" name="id" value="<?= (int) $p['id_pendaftaran'] ?>">
                                    <input type="hidden" name="status" value="diterima">
                                    <button type="submit" class="mini-btn accept" title="Terima pendaftaran">
                                        <i class="fas fa-check"></i> Terima
                                    </button>
                                </form>
                                <?php endif; ?>

                                <!-- Tolak -->
                                <?php if ($p['status_pendaftaran'] != 'ditolak'): ?>
                                <form action="update_status_pendaftaran.php" method="POST" onsubmit="return confirm('Yakin ingin menolak pendaftaran dari <?= htmlspecialchars($p['nama']) ?>?')">
                                    <input type="hidden" name="id" value="<?= (int) $p['id_pendaftaran'] ?>">
                                    <input type="hidden" name="status" value="ditolak">
                                    <button type="submit" class="mini-btn reject" title="Tolak pendaftaran">
                                        <i class="fas fa-times"></i> Tolak
                                    </button>
                                </form>
                                <?php endif; ?>

                                <!-- Reset -->
                                <?php if ($p['status_pendaftaran'] != 'menunggu'): ?>
                                <form action="update_status_pendaftaran.php" method="POST">
                                    <input type="hidden" name="id" value="<?= (int) $p['id_pendaftaran'] ?>">
                                    <input type="hidden" name="status" value="menunggu">
                                    <button type="submit" class="mini-btn reset" title="Reset ke menunggu">
                                        <i class="fas fa-undo"></i> Reset
                                    </button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</div>

<?php include '../../include/footer.php'; ?>
<?php
// dashboard/pengurus/aspirasi_masuk.php
session_start();

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['peran'], ['pengurus', 'admin'])) {
    header('Location: ../../auth/login.php');
    exit;
}

require_once '../../config/database.php';
require_once '../../include/pendaftaran-helper.php';

// ============================================================
// FILTER & PARAMETER
// ============================================================
$statusFilter = isset($_GET['status']) ? $_GET['status'] : '';
$kategoriFilter = isset($_GET['kategori']) ? $_GET['kategori'] : '';

$statusValid = ['proses', 'selesai', 'ditolak'];
$kategoriValid = ['Kritik', 'Saran', 'Keluhan', 'Apresiasi', 'Lainnya'];

// ============================================================
// BANGUN QUERY DENGAN FILTER (PDO)
// ============================================================
$where = "1=1";
$params = [];

if ($statusFilter !== '' && in_array($statusFilter, $statusValid, true)) {
    $where .= " AND a.status = :status";
    $params[':status'] = $statusFilter;
}

if ($kategoriFilter !== '' && in_array($kategoriFilter, $kategoriValid, true)) {
    $where .= " AND a.kategori = :kategori";
    $params[':kategori'] = $kategoriFilter;
}

// ============================================================
// AMBIL DATA ASPIRASI
// ============================================================
$sql = "SELECT a.*, 
               o.nama_organisasi,
               m.nama AS nama_mahasiswa,
               m.nim
        FROM aspirasi a
        LEFT JOIN organisasi o ON a.id_organisasi_tujuan = o.id_organisasi
        LEFT JOIN tbmahasiswa m ON a.id_mahasiswa = m.id_mahasiswa
        WHERE $where
        ORDER BY a.created_at DESC";

$stmt = $pdo->prepare($sql);
foreach ($params as $key => $val) {
    $stmt->bindValue($key, $val);
}
$stmt->execute();
$aspirasi = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ============================================================
// STATISTIK STATUS
// ============================================================
$stat = [
    'proses' => 0,
    'selesai' => 0,
    'ditolak' => 0,
];

$statStmt = $pdo->query("SELECT status, COUNT(*) as total FROM aspirasi GROUP BY status");
while ($row = $statStmt->fetch(PDO::FETCH_ASSOC)) {
    if (isset($stat[$row['status']])) {
        $stat[$row['status']] = (int) $row['total'];
    }
}

include '../../include/header.php';
?>

<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/profil.css?v=<?= time() ?>">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

<style>
/* ============================================
   ASPIRASI MASUK - KONSISTEN
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

/* ===== STATS GRID ===== */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1rem;
    margin-bottom: 1.5rem;
}
.stat-card {
    background: #ffffff;
    border-radius: 12px;
    padding: 1rem 1.2rem;
    border: 1px solid #e9ecef;
    text-align: center;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}
.stat-card span {
    font-size: 0.75rem;
    font-weight: 600;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    display: block;
}
.stat-card strong {
    font-size: 2rem;
    font-weight: 700;
    color: #071C34;
    margin-top: 0.2rem;
    display: block;
}
.stat-card.proses strong { color: #f59e0b; }
.stat-card.selesai strong { color: #22c55e; }
.stat-card.ditolak strong { color: #dc2626; }

/* ===== FILTER ===== */
.filter-card {
    background: #f8fafc;
    border-radius: 50px;
    padding: 0.3rem 0.5rem;
    border: 1px solid #e9ecef;
    display: inline-flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.3rem;
    margin-bottom: 1.5rem;
}
.filter-card select {
    background: transparent;
    border: none;
    padding: 0.3rem 0.8rem;
    border-radius: 50px;
    font-weight: 600;
    font-size: 0.85rem;
    color: #071C34;
    cursor: pointer;
}
.filter-card select:focus {
    outline: none;
}
.filter-card button {
    background: #FFA007;
    color: #071C34;
    border: none;
    padding: 0.2rem 1.2rem;
    border-radius: 50px;
    font-weight: 700;
    font-size: 0.8rem;
    cursor: pointer;
}
.filter-card a {
    background: transparent;
    color: #64748b;
    border: none;
    padding: 0.2rem 0.8rem;
    border-radius: 50px;
    font-weight: 600;
    font-size: 0.8rem;
    text-decoration: none;
}
.filter-card a:hover {
    color: #071C34;
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
    text-align: center; /* Ubah baris ini dari left menjadi center */
    font-weight: 700;
    font-size: 0.7rem;
    text-transform: uppercase;
    color: #64748b;
    letter-spacing: 0.3px;
    white-space: nowrap;
}

.table-card td {
    padding: 0.7rem 1rem;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: middle;
    text-align: center; /* Tambahkan baris ini */
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
.table-card .empty-cell {
    text-align: center;
    padding: 2.5rem 0;
    color: #94a3b8;
}

/* ============================================================
   STATUS BADGE - KONSISTEN
   ============================================================ */
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
.status-badge.proses {
    background: #f59e0b;
}
.status-badge.selesai {
    background: #22c55e;
}
.status-badge.ditolak {
    background: #dc2626;
}

/* ============================================================
   ANONIM CHIP
   ============================================================ */
.anon-chip {
    background: #e2e8f0;
    color: #475569;
    padding: 0.1rem 0.6rem;
    border-radius: 50px;
    font-size: 0.65rem;
    font-weight: 600;
}

/* ============================================================
   TOMBOL AKSI - KONSISTEN
   ============================================================ */
.action-inline {
    display: flex;
    align-items: center;
    gap: 0.3rem;
    flex-wrap: nowrap;
}
.action-inline a {
    padding: 0.15rem 0.7rem;
    border-radius: 50px;
    font-size: 0.65rem;
    font-weight: 700;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.2rem;
    border: 2px solid;
    background: transparent;
    white-space: nowrap;
}
.action-inline a.proses {
    color: #071C34;
    border-color: #071C34;
}
.action-inline a.proses:hover {
    background: #071C34;
    color: #ffffff;
}
.action-inline a.detail {
    color: #f59e0b;
    border-color: #f59e0b;
}
.action-inline a.detail:hover {
    background: #f59e0b;
    color: #ffffff;
}
.action-inline a.selesai {
    color: #22c55e;
    border-color: #22c55e;
}
.action-inline a.selesai:hover {
    background: #22c55e;
    color: #ffffff;
}
.action-inline a.tolak {
    color: #dc2626;
    border-color: #dc2626;
}
.action-inline a.tolak:hover {
    background: #dc2626;
    color: #ffffff;
}

/* ============================================================
   RESPONSIVE
   ============================================================ */
@media (max-width: 768px) {
    .aspirasi-container {
        padding: 0 0.5rem;
    }
    .aspirasi-header {
        flex-direction: column;
        align-items: stretch;
        text-align: center;
    }
    .aspirasi-header .title-group h2 {
        font-size: 1.4rem;
    }
    .stats-grid {
        grid-template-columns: repeat(3, 1fr);
        gap: 0.5rem;
    }
    .stat-card strong {
        font-size: 1.5rem;
    }
    .filter-card {
        flex-wrap: wrap;
        border-radius: 12px;
        padding: 0.5rem;
        justify-content: center;
    }
    .filter-card select {
        font-size: 0.75rem;
        padding: 0.2rem 0.5rem;
    }
    .table-card {
        overflow-x: auto;
    }
    .table-card table {
        font-size: 0.8rem;
        min-width: 650px;
    }
    .table-card th, .table-card td {
        padding: 0.4rem 0.6rem;
    }
    .action-inline {
        flex-direction: column;
        gap: 0.2rem;
    }
    .action-inline a {
        width: 100%;
        justify-content: center;
        padding: 0.2rem 0.5rem;
    }
}
</style>

<div class="aspirasi-container">

    <!-- HEADER -->
    <div class="aspirasi-header">
        <div class="title-group">
            <h2>Kelola Aspirasi</h2>
            <p class="subtitle">
                <i class="fas fa-message"></i> 
                Pantau aspirasi mahasiswa dan ubah status tindak lanjut
            </p>
        </div>
    </div>

    <!-- STATS -->
    <div class="stats-grid">
        <div class="stat-card proses">
            <span><i class="fas fa-clock"></i> Proses</span>
            <strong><?= $stat['proses'] ?></strong>
        </div>
        <div class="stat-card selesai">
            <span><i class="fas fa-check-circle"></i> Selesai</span>
            <strong><?= $stat['selesai'] ?></strong>
        </div>
        <div class="stat-card ditolak">
            <span><i class="fas fa-times-circle"></i> Ditolak</span>
            <strong><?= $stat['ditolak'] ?></strong>
        </div>
    </div>

    <!-- FILTER -->
    <div class="filter-card">
        <form method="GET" action="<?= $_SERVER['PHP_SELF'] ?>" style="display:contents;">
            <select name="status">
                <option value="">Semua Status</option>
                <option value="proses" <?= $statusFilter === 'proses' ? 'selected' : '' ?>>Proses</option>
                <option value="selesai" <?= $statusFilter === 'selesai' ? 'selected' : '' ?>>Selesai</option>
                <option value="ditolak" <?= $statusFilter === 'ditolak' ? 'selected' : '' ?>>Ditolak</option>
            </select>

            <select name="kategori">
                <option value="">Semua Kategori</option>
                <?php foreach ($kategoriValid as $kat): ?>
                    <option value="<?= htmlspecialchars($kat) ?>" <?= $kategoriFilter === $kat ? 'selected' : '' ?>>
                        <?= htmlspecialchars($kat) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit"><i class="fas fa-filter"></i> Filter</button>
            <a href="<?= $_SERVER['PHP_SELF'] ?>"><i class="fas fa-undo"></i> Reset</a>
        </form>
    </div>

    <!-- TABLE -->
    <div class="table-card">
        <table>
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Judul</th>
                    <th>Kategori</th>
                    <th>Tujuan</th>
                    <th>Pengirim</th>
                    <th>Tanggal</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($aspirasi) > 0): ?>
                    <?php foreach ($aspirasi as $row): ?>
                        <tr>
                            <td>
                                <span style="background:#f1f5f9; padding:0.1rem 0.6rem; border-radius:50px; font-size:0.7rem; color:#071C34;">
                                    <?= htmlspecialchars($row['kode_aspirasi'] ?? '-') ?>
                                </span>
                            </td>
                            <td>
                                <strong style="color:#071C34;"><?= htmlspecialchars($row['judul'] ?? '-') ?></strong>
                                <div style="font-size:0.75rem; color:#64748b;">
                                    <?= htmlspecialchars(substr($row['isi_aspirasi'] ?? '', 0, 80)) ?>...
                                </div>
                            </td>
                            <td><span style="background:#f1f5f9; padding:0.1rem 0.6rem; border-radius:50px; font-size:0.7rem; color:#071C34;"><?= htmlspecialchars($row['kategori'] ?? '-') ?></span></td>
                            <td><?= htmlspecialchars($row['nama_organisasi'] ?? 'Umum') ?></td>
                            <td>
                                <?php if ((int)($row['is_anonim'] ?? 0) === 1): ?>
                                    <span class="anon-chip"><i class="fas fa-user-secret"></i> Anonim</span>
                                <?php else: ?>
                                    <strong><?= htmlspecialchars($row['nama_mahasiswa'] ?? 'Mahasiswa') ?></strong>
                                    <div style="font-size:0.7rem; color:#94a3b8;">NIM: <?= htmlspecialchars($row['nim'] ?? '-') ?></div>
                                <?php endif; ?>
                            </td>
                            <td style="font-size:0.85rem; color:#64748b;">
                                <i class="fa-regular fa-calendar"></i> <?= date('d M Y', strtotime($row['created_at'] ?? $row['tanggal'] ?? 'now')) ?>
                            </td>
                            <td>
                                <span class="status-badge <?= $row['status'] ?? 'proses' ?>">
                                    <i class="fas <?= ($row['status'] ?? 'proses') == 'proses' ? 'fa-clock' : (($row['status'] ?? 'proses') == 'selesai' ? 'fa-check' : 'fa-times') ?>"></i>
                                    <?= ucfirst($row['status'] ?? 'Proses') ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-inline">
                                    <a href="detail_aspirasi.php?id=<?= (int)$row['id_aspirasi'] ?>" class="detail" title="Detail">
                                        <i class="fas fa-eye"></i> Detail
                                    </a>
                                    <a href="update_status_aspirasi.php?id=<?= (int)$row['id_aspirasi'] ?>&status=proses" class="proses" title="Proses">
                                        <i class="fas fa-clock"></i> Proses
                                    </a>
                                    <a href="update_status_aspirasi.php?id=<?= (int)$row['id_aspirasi'] ?>&status=selesai" class="selesai" title="Selesai">
                                        <i class="fas fa-check"></i> Selesai
                                    </a>
                                    <a href="update_status_aspirasi.php?id=<?= (int)$row['id_aspirasi'] ?>&status=ditolak" class="tolak" onclick="return confirm('Yakin ingin menolak aspirasi ini?')" title="Tolak">
                                        <i class="fas fa-times"></i> Tolak
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="empty-cell">
                            <i class="fa-regular fa-message" style="font-size:2rem; display:block; color:#cbd5e0; margin-bottom:0.5rem;"></i>
                            Belum ada aspirasi yang masuk.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

<?php include '../../include/footer.php'; ?>
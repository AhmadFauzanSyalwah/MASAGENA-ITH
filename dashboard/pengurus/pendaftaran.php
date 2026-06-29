<?php
// dashboard/pengurus/pendaftaran.php
session_start();

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['peran'], ['pengurus', 'admin'])) {
    header('Location: ../../auth/login.php');
    exit;
}

require_once '../../config/database.php';
require_once '../../include/pendaftaran-helper.php';

$status = $_GET['status'] ?? 'semua';

$allowedStatus = ['semua', 'menunggu', 'diterima', 'ditolak'];
if (!in_array($status, $allowedStatus, true)) {
    $status = 'semua';
}

// ============================================================
// AMBIL ID ORGANISASI PENGURUS (jika bukan admin)
// ============================================================
$id_user = $_SESSION['user_id'];
$id_organisasi_pengurus = null;

if ($_SESSION['peran'] !== 'admin') {
    $stmtOrg = $pdo->prepare("SELECT id_organisasi FROM pengurus_organisasi WHERE id_pengurus = ?");
    $stmtOrg->execute([$id_user]);
    $pengurusOrg = $stmtOrg->fetch(PDO::FETCH_ASSOC);
    if ($pengurusOrg) {
        $id_organisasi_pengurus = $pengurusOrg['id_organisasi'];
    }
}

// ============================================================
// BANGUN QUERY
// ============================================================
$where = "1=1";
$params = [];

if ($status !== 'semua') {
    $where .= " AND p.status_pendaftaran = :status";
    $params[':status'] = $status;
}

if ($_SESSION['peran'] !== 'admin' && $id_organisasi_pengurus) {
    $where .= " AND k.id_organisasi = :id_org";
    $params[':id_org'] = $id_organisasi_pengurus;
}

$sql = "SELECT
            p.id_pendaftaran,
            p.tanggal_daftar,
            p.status_pendaftaran,
            m.nama,
            m.nim,
            m.prodi,
            m.kontak,
            m.email,
            k.judul,
            k.tanggal_kegiatan,
            o.nama_organisasi
        FROM pendaftaran p
        JOIN tbmahasiswa m ON m.id_mahasiswa = p.id_mahasiswa
        JOIN konten_kegiatan k ON k.id_konten = p.id_konten
        JOIN organisasi o ON o.id_organisasi = k.id_organisasi
        WHERE $where
        ORDER BY p.tanggal_daftar DESC";

$stmt = $pdo->prepare($sql);
foreach ($params as $key => $val) {
    $stmt->bindValue($key, $val);
}
$stmt->execute();
$pendaftaran = $stmt->fetchAll();

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
   PENDAFTARAN - FULL WIDTH
   ============================================ */
.pendaftaran-container {
    max-width: 100%;
    margin: 0;
    padding: 0 1rem;
    box-sizing: border-box;
}

/* Header */
.pendaftaran-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 1.5rem;
}
.pendaftaran-header .title-group h2 {
    font-size: 1.8rem;
    font-weight: 700;
    color: #071C34;
    margin: 0;
}

/* ===== TOMBOL HEADER ===== */
.pendaftaran-header .btn-lihat {
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
.pendaftaran-header .btn-lihat:hover {
    background: #071C34;
    color: #ffffff;
    border-color: #071C34;
}

/* Filter Tabs */
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
.filter-tabs button {
    background: transparent;
    border: none;
    padding: 0.3rem 1.2rem;
    border-radius: 50px;
    font-weight: 600;
    font-size: 0.85rem;
    color: #64748b;
    cursor: pointer;
}
.filter-tabs button:hover {
    color: #071C34;
}
.filter-tabs button.active {
    background: #071C34;
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

/* ============================================================
   STATUS BADGE - SATU BARIS (ikon + teks)
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
.status-badge.menunggu {
    background: #FFA007;
}
.status-badge.diterima {
    background: #071C34;
}
.status-badge.ditolak {
    background: #dc2626;
}

/* ============================================================
   TOMBOL AKSI - DIPERBESAR, TETAP BERSAMPINGAN
   ============================================================ */
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
    .pendaftaran-container {
        padding: 0 0.5rem;
    }
    .pendaftaran-header {
        flex-direction: column;
        align-items: stretch;
        text-align: center;
    }
    .pendaftaran-header .title-group h2 {
        font-size: 1.4rem;
    }
    .pendaftaran-header .btn-lihat {
        justify-content: center;
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

<div class="pendaftaran-container">

    <!-- HEADER -->
    <div class="pendaftaran-header">
        <div class="title-group">
            <h2>Kelola Pendaftaran Kegiatan</h2>
        </div>
        <a href="<?= BASE_URL ?>/dashboard/pengurus/kelola_konten.php" class="btn-lihat">
            <i class="fas fa-calendar-alt"></i> Lihat Kegiatan
        </a>
    </div>

    <!-- FILTER TABS -->
    <form method="GET" action="<?= $_SERVER['PHP_SELF'] ?>" class="filter-tabs">
        <button type="submit" name="status" value="semua" class="<?= $status === 'semua' ? 'active' : '' ?>">
            <i class="fas fa-list"></i> Semua
        </button>
        <button type="submit" name="status" value="menunggu" class="<?= $status === 'menunggu' ? 'active' : '' ?>">
            <i class="fas fa-clock"></i> Menunggu
        </button>
        <button type="submit" name="status" value="diterima" class="<?= $status === 'diterima' ? 'active' : '' ?>">
            <i class="fas fa-check-circle"></i> Diterima
        </button>
        <button type="submit" name="status" value="ditolak" class="<?= $status === 'ditolak' ? 'active' : '' ?>">
            <i class="fas fa-times-circle"></i> Ditolak
        </button>
    </form>

    <!-- TABLE -->
    <div class="table-card">
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Mahasiswa</th>
                    <th>Kontak</th>
                    <th>Kegiatan</th>
                    <th>Prodi</th>
                    <th>Tanggal Daftar</th>
                    <th class="status-header">Status</th>
                    <th class="aksi-header">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($pendaftaran)): ?>
                    <tr>
                        <td colspan="8">
                            <div class="text-center">
                                <i class="fa-regular fa-file-lines"></i>
                                <p style="margin:0; color:#94a3b8; font-weight:500;">Belum ada data pendaftaran</p>
                                <p style="margin:0; font-size:0.8rem; color:#cbd5e0;">
                                    <?php if ($_SESSION['peran'] === 'admin'): ?>
                                        Belum ada mahasiswa yang mendaftar kegiatan
                                    <?php else: ?>
                                        Belum ada pendaftaran untuk organisasi Anda
                                    <?php endif; ?>
                                </p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>

                <?php $no = 1; foreach ($pendaftaran as $row): ?>
                    <tr>
                        <td style="font-weight:600; color:#071C34;"><?= $no++ ?></td>
                        <td>
                            <div style="font-weight:600; color:#071C34;"><?= htmlspecialchars($row['nama']) ?></div>
                            <div style="font-size:0.75rem; color:#94a3b8;">NIM: <?= htmlspecialchars($row['nim']) ?></div>
                        </td>
                        <td>
                            <div style="font-size:0.8rem; color:#64748b;"><?= htmlspecialchars($row['kontak'] ?? '-') ?></div>
                            <div style="font-size:0.7rem; color:#94a3b8;"><?= htmlspecialchars($row['email']) ?></div>
                        </td>
                        <td>
                            <strong style="color:#071C34;"><?= htmlspecialchars($row['judul']) ?></strong>
                            <div style="font-size:0.7rem; color:#64748b;">
                                <i class="fa-regular fa-building"></i> <?= htmlspecialchars($row['nama_organisasi'] ?? '-') ?>
                            </div>
                            <div style="font-size:0.7rem; color:#94a3b8;">
                                <i class="fa-regular fa-calendar"></i> <?= date('d M Y', strtotime($row['tanggal_kegiatan'] ?? $row['tanggal_daftar'])) ?>
                            </div>
                        </td>
                        <td><?= htmlspecialchars($row['prodi'] ?? '-') ?></td>
                        <td style="font-size:0.85rem; color:#64748b;">
                            <i class="fa-regular fa-clock"></i> <?= date('d M Y', strtotime($row['tanggal_daftar'])) ?>
                        </td>
                        <td class="status-cell">
                            <span class="status-badge <?= $row['status_pendaftaran'] ?>">
                                <i class="fas <?= $row['status_pendaftaran'] == 'menunggu' ? 'fa-clock' : ($row['status_pendaftaran'] == 'diterima' ? 'fa-check' : 'fa-times') ?>"></i>
                                <?= ucfirst($row['status_pendaftaran']) ?>
                            </span>
                        </td>
                        <td class="aksi-cell">
                            <div class="action-inline">
                                <!-- Terima -->
                                <?php if ($row['status_pendaftaran'] != 'diterima'): ?>
                                <form action="update_status_pendaftaran.php" method="POST" onsubmit="return confirm('Yakin ingin menerima pendaftaran dari <?= htmlspecialchars($row['nama']) ?>?')">
                                    <input type="hidden" name="id" value="<?= (int) $row['id_pendaftaran'] ?>">
                                    <input type="hidden" name="status" value="diterima">
                                    <button type="submit" class="mini-btn accept" title="Terima pendaftaran">
                                        <i class="fas fa-check"></i> Terima
                                    </button>
                                </form>
                                <?php endif; ?>

                                <!-- Tolak -->
                                <?php if ($row['status_pendaftaran'] != 'ditolak'): ?>
                                <form action="update_status_pendaftaran.php" method="POST" onsubmit="return confirm('Yakin ingin menolak pendaftaran dari <?= htmlspecialchars($row['nama']) ?>?')">
                                    <input type="hidden" name="id" value="<?= (int) $row['id_pendaftaran'] ?>">
                                    <input type="hidden" name="status" value="ditolak">
                                    <button type="submit" class="mini-btn reject" title="Tolak pendaftaran">
                                        <i class="fas fa-times"></i> Tolak
                                    </button>
                                </form>
                                <?php endif; ?>

                                <!-- Reset -->
                                <?php if ($row['status_pendaftaran'] != 'menunggu'): ?>
                                <form action="update_status_pendaftaran.php" method="POST">
                                    <input type="hidden" name="id" value="<?= (int) $row['id_pendaftaran'] ?>">
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
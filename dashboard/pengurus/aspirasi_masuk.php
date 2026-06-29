<?php
// Memulai sesi sistem 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Pemanggilan jalur konfigurasi disesuaikan dengan posisi file di dashboard/pengurus/
require_once '../../config/session_check.php';
require_once '../../config/database.php';
require_once '../../include/components.php';

// Proteksi akses halaman, pastikan hanya peran pengurus yang dapat membuka
if ($_SESSION['peran'] != 'pengurus') {
    header("Location: ../" . $_SESSION['peran'] . "/index.php");
    exit();
}

// Mengambil variabel sesi organisasi dan pastikan bentuknya integer
$id_organisasi = isset($_SESSION['id_organisasi']) ? (int)$_SESSION['id_organisasi'] : 0;
$level = $_SESSION['level'] ?? 'biasa';

if ($id_organisasi === 0) {
    die("Sesi organisasi tidak valid.");
}

// 1. Ambil data nama organisasi untuk judul header halaman
$stmt_org = $pdo->prepare("SELECT nama_organisasi FROM organisasi WHERE id_organisasi = ?");
$stmt_org->execute([$id_organisasi]);
$data_org = $stmt_org->fetch(PDO::FETCH_ASSOC);
$nama_organisasi = $data_org ? $data_org['nama_organisasi'] : 'Organisasi Anda';

// 2. Tarik daftar aspirasi
$stmt_asp = $pdo->prepare("
    SELECT a.*, m.nama as nama_mahasiswa 
    FROM aspirasi a 
    LEFT JOIN tbmahasiswa m ON a.id_mahasiswa = m.id_mahasiswa 
    WHERE a.id_organisasi_tujuan = ? 
    ORDER BY a.created_at DESC
");
$stmt_asp->execute([$id_organisasi]);
$aspirasi_list = $stmt_asp->fetchAll(PDO::FETCH_ASSOC);

// Memanggil header template dari folder include
include '../../include/header.php';
?>

    <div class="dashboard-welcome">
        <h1>Aspirasi Masuk</h1>
        <p>Daftar aspirasi, kritik, dan saran yang masuk dan ditujukan ke <strong><?= htmlspecialchars($nama_organisasi) ?></strong>.</p>
    </div>

    <main class="main-container" style="margin-top: 1.5rem; padding: 0;">
        
        <div class="main-content">
            <h2>Data Aspirasi Masuk</h2>
            
            <div style="overflow-x:auto; margin-top: 1rem;">
                <table class="aspirasi-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Pengirim</th>
                            <th>Kategori</th>
                            <th>Judul Aspirasi</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($aspirasi_list) > 0) {
                            $no = 1;
                            foreach ($aspirasi_list as $asp) { ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td><?= date('d M Y H:i', strtotime($asp['created_at'])); ?></td>
                                    <td>
                                        <?= ($asp['is_anonim'] == 1) ? '<em>Anonim</em>' : htmlspecialchars($asp['nama_mahasiswa']); ?>
                                    </td>
                                    <td><?= htmlspecialchars($asp['kategori']); ?></td>
                                    <td><strong><?= htmlspecialchars($asp['judul']); ?></strong></td>
                                    <td>
                                        <?php
                                            $status = strtolower($asp['status']);
                                            if ($status == 'selesai') {
                                                echo '<span style="color: var(--success); font-weight: 600;">Selesai</span>';
                                            } elseif ($status == 'ditolak') {
                                                echo '<span style="color: var(--danger); font-weight: 600;">Ditolak</span>';
                                            } else {
                                                echo '<span style="color: var(--accent); font-weight: 600;">Proses</span>';
                                            }
                                        ?>
                                    </td>
                                </tr>
                            <?php }
                        } else { ?>
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 2rem;">Belum ada aspirasi yang masuk untuk organisasi Anda.</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

<?php 
include '../../include/footer.php';
?>
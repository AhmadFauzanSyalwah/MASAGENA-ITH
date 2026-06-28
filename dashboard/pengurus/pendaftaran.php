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
    header("Location: ../" . $_SESSION['peran'] / "/index.php");
    exit();
}

// Mengambil variabel sesi organisasi dan pastikan bentuknya integer
$id_organisasi = isset($_SESSION['id_organisasi']) ? (int)$_SESSION['id_organisasi'] : 0;

if ($id_organisasi === 0) {
    die("Sesi organisasi tidak valid.");
}

// Ambil data pendaftaran yang masuk pada kegiatan yang diselenggarakan oleh organisasi ini
$stmt = $pdo->prepare("
    SELECT p.*, k.judul as judul_kegiatan, m.nama as nama_mahasiswa 
    FROM pendaftaran p 
    JOIN konten_kegiatan k ON p.id_konten = k.id_konten 
    LEFT JOIN tbmahasiswa m ON p.nim = m.nim 
    WHERE k.id_organisasi = ? 
    ORDER BY p.created_at DESC
");
$pendaftaran_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Memanggil header template dari folder include
include '../../include/header.php';
?>

    <div class="dashboard-welcome">
        <h1>Riwayat Pendaftaran</h1>
        <p>Daftar mahasiswa yang telah mendaftar pada kegiatan/acara yang diselenggarakan oleh organisasi Anda.</p>
    </div>

    <main class="main-container" style="margin-top: 1.5rem; padding: 0;">
        
        <div class="main-content">
            <h2>Data Pendaftaran Kegiatan</h2>
            
            <div style="overflow-x:auto; margin-top: 1rem;">
                <table class="aspirasi-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tanggal Daftar</th>
                            <th>Nama Kegiatan</th>
                            <th>Pendaftar</th>
                            <th>Status Pendaftaran</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($pendaftaran_list) > 0) {
                            $no = 1;
                            foreach ($pendaftaran_list as $daftar) { ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td><?= date('d M Y H:i', strtotime($daftar['created_at'])); ?></td>
                                    <td><strong><?= htmlspecialchars($daftar['judul_kegiatan']); ?></strong></td>
                                    <td><?= htmlspecialchars($daftar['nama_mahasiswa']); ?></td>
                                    <td>
                                        <?php
                                            $status = strtolower($daftar['status_pendaftaran']);
                                            if ($status == 'diterima') {
                                                echo '<span style="color: var(--success); font-weight: 600;">Diterima</span>';
                                            } elseif ($status == 'ditolak') {
                                                echo '<span style="color: var(--danger); font-weight: 600;">Ditolak</span>';
                                            } else {
                                                echo '<span style="color: var(--accent); font-weight: 600; text-transform: capitalize;">' . htmlspecialchars($status) . '</span>';
                                            }
                                        ?>
                                    </td>
                                    <td>
                                        <div style="display: flex; gap: 4px; flex-wrap: wrap;">
                                            <a href="update_status_pendaftaran.php?id=<?= (int)$daftar['id_pendaftaran']; ?>&status=diterima" class="btn-sm" style="background-color: var(--success); color: var(--white); padding: 4px 8px; font-size: 0.75rem;">
                                                <i class="fa fa-check"></i> Terima
                                            </a>
                                            <a href="update_status_pendaftaran.php?id=<?= (int)$daftar['id_pendaftaran']; ?>&status=ditolak" class="btn-sm" style="background-color: var(--danger); color: var(--white); padding: 4px 8px; font-size: 0.75rem;">
                                                <i class="fa fa-times"></i> Tolak
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php }
                        } else { ?>
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 2rem;">Belum ada pendaftaran kegiatan untuk organisasi Anda.</td>
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
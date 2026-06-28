<?php
/**
 * dashboard/mahasiswa/profil.php
 * Halaman profil mahasiswa - tampilan data diri
 * Foto profil di atas, tombol warna kuning & biru
 */
session_start();
require_once '../../config/database.php';
require_once '../../config/session_check.php';

// Cek login sebagai mahasiswa
if (!isset($_SESSION['peran']) || $_SESSION['peran'] !== 'mahasiswa') {
    header('Location: ../../auth/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Ambil data mahasiswa
$query = "SELECT * FROM tbmahasiswa WHERE id_mahasiswa = :id";
$stmt = $pdo->prepare($query);
$stmt->execute([':id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die('Data mahasiswa tidak ditemukan.');
}

// Ambil organisasi yang diikuti (unique) - status pendaftaran 'diterima'
$queryOrg = "SELECT DISTINCT o.id_organisasi, o.nama_organisasi, o.jenis 
             FROM pendaftaran p
             JOIN konten_kegiatan k ON p.id_konten = k.id_konten
             JOIN organisasi o ON k.id_organisasi = o.id_organisasi
             WHERE p.id_mahasiswa = :id_user AND p.status_pendaftaran = 'diterima'";
$stmtOrg = $pdo->prepare($queryOrg);
$stmtOrg->execute([':id_user' => $user_id]);
$organisasi = $stmtOrg->fetchAll();

// Ambil 5 kegiatan terakhir yang diikuti (dengan status)
$queryKeg = "SELECT p.*, k.judul, k.tanggal_kegiatan, k.deskripsi, o.nama_organisasi 
             FROM pendaftaran p
             JOIN konten_kegiatan k ON p.id_konten = k.id_konten
             JOIN organisasi o ON k.id_organisasi = o.id_organisasi
             WHERE p.id_mahasiswa = :id_user
             ORDER BY p.tanggal_daftar DESC
             LIMIT 5";
$stmtKeg = $pdo->prepare($queryKeg);
$stmtKeg->execute([':id_user' => $user_id]);
$kegiatan = $stmtKeg->fetchAll();

include '../../include/header.php';
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/profil.css?v=<?php echo time(); ?>">

<div class="container mt-4 mb-5">
    <div class="row">
        <div class="col-lg-8 mx-auto">

            <!-- Judul Data Diri -->
            <h4 class="profil-title">Data Diri</h4>

            <!-- Outer Card dengan border #0a2a4a -->
            <div class="profil-outer-card">

                <!-- Inner Card putih -->
                <div class="profil-inner-card">

                    <!-- ===== FOTO PROFIL DI ATAS ===== -->
                    <div class="profil-foto-top">
                        <?php if (file_exists('../../uploads/profil/' . $user['id_mahasiswa'] . '.jpg')): ?>
                            <img src="<?php echo BASE_URL; ?>/uploads/profil/<?php echo $user['id_mahasiswa']; ?>.jpg" alt="Foto Profil" class="foto-profil-img">
                        <?php else: ?>
                            <i class="fas fa-user-circle"></i>
                        <?php endif; ?>
                    </div>

                    <!-- ===== DATA DIRI ===== -->
                    <div class="data-diri">
                        <div class="data-item">
                            <label>Nama Lengkap</label>
                            <p><?php echo htmlspecialchars($user['nama']); ?></p>
                        </div>
                        <div class="data-item">
                            <label>NIM</label>
                            <p><?php echo htmlspecialchars($user['nim']); ?></p>
                        </div>
                        <div class="data-item">
                            <label>Email</label>
                            <p><?php echo htmlspecialchars($user['email']); ?></p>
                        </div>
                        <div class="data-item">
                            <label>Status Verifikasi</label>
                            <p>
                                <?php echo $user['verification_token'] 
                                    ? '<span class="badge bg-success">Terverifikasi</span>' 
                                    : '<span class="badge bg-warning">Belum Verifikasi</span>'; ?>
                            </p>
                        </div>
                        <div class="data-item">
                            <label>Tanggal Bergabung</label>
                            <p><?php echo date('d M Y', strtotime($user['created_at'] ?? date('Y-m-d'))); ?></p>
                        </div>
                    </div>

                    <!-- ===== ORGANISASI ===== -->
                    <div class="profil-organisasi">
                        <h6><i class="fa-regular fa-building me-1"></i> Organisasi</h6>
                        <?php if (count($organisasi) > 0): ?>
                            <div class="org-list">
                                <?php foreach ($organisasi as $org): ?>
                                    <span class="org-badge">
                                        <?php echo htmlspecialchars($org['nama_organisasi']); ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="org-empty">Belum memiliki organisasi</p>
                        <?php endif; ?>
                    </div>

                    <!-- ===== 5 KEGIATAN TERAKHIR ===== -->
                    <div class="profil-kegiatan mt-3">
                        <h6><i class="fa-regular fa-calendar me-1"></i> Kegiatan Terakhir</h6>
                        <?php if (count($kegiatan) > 0): ?>
                            <ul class="kegiatan-list">
                                <?php foreach ($kegiatan as $k): ?>
                                    <li>
                                        <span class="keg-nama"><?php echo htmlspecialchars($k['judul']); ?></span>
                                        <span class="keg-org"><?php echo htmlspecialchars($k['nama_organisasi']); ?></span>
                                        <span class="keg-status">
                                            <?php 
                                                $status = $k['status_pendaftaran'] ?? $k['status'];
                                                if ($status == 'menunggu') echo '<span class="badge bg-warning">Menunggu</span>';
                                                elseif ($status == 'diterima') echo '<span class="badge bg-success">Diterima</span>';
                                                elseif ($status == 'ditolak') echo '<span class="badge bg-danger">Ditolak</span>';
                                                else echo '<span class="badge bg-secondary">' . $status . '</span>';
                                            ?>
                                        </span>
                                        <span class="keg-tgl"><?php echo date('d M Y', strtotime($k['tanggal_kegiatan'])); ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                            <?php if (count($kegiatan) >= 5): ?>
                                <a href="<?php echo BASE_URL; ?>/dashboard/mahasiswa/pendaftaran.php" class="btn btn-sm btn-outline-primary mt-1">Lihat semua</a>
                            <?php endif; ?>
                        <?php else: ?>
                            <p class="org-empty">Belum mengikuti kegiatan apapun</p>
                        <?php endif; ?>
                    </div>

                    <!-- ===== TOMBOL BAWAH ===== -->
                    <div class="profil-actions">
                        <a href="<?php echo BASE_URL; ?>/dashboard/mahasiswa/" class="btn btn-kembali">
                            <i class="fas fa-arrow-left me-1"></i> Kembali
                        </a>
                        <a href="<?php echo BASE_URL; ?>/dashboard/mahasiswa/edit_profil.php" class="btn btn-edit">
                            <i class="fas fa-edit me-1"></i> Edit Profil
                        </a>
                    </div>

                </div><!-- end profil-inner-card -->
            </div><!-- end profil-outer-card -->

        </div>
    </div>
</div>

<?php include '../../include/footer.php'; ?>
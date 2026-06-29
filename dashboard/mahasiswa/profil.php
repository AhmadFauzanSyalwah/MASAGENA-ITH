<?php
/**
 * dashboard/mahasiswa/profil.php
 * Halaman profil mahasiswa - foto kiri, info kanan
 */
session_start();
require_once '../../config/database.php';
require_once '../../config/session_check.php';

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

// Organisasi yang diikuti
$queryOrg = "SELECT DISTINCT o.id_organisasi, o.nama_organisasi 
             FROM pendaftaran p
             JOIN konten_kegiatan k ON p.id_konten = k.id_konten
             JOIN organisasi o ON k.id_organisasi = o.id_organisasi
             WHERE p.id_mahasiswa = :id_user AND p.status_pendaftaran = 'diterima'";
$stmtOrg = $pdo->prepare($queryOrg);
$stmtOrg->execute([':id_user' => $user_id]);
$organisasi = $stmtOrg->fetchAll();

// 5 kegiatan terakhir
$queryKeg = "SELECT p.*, k.judul, k.tanggal_kegiatan, o.nama_organisasi 
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

            <h4 class="profil-title">Data Diri</h4>

            <div class="profil-outer-card">
                <div class="profil-inner-card">

                    <!-- HEADER: FOTO + INFO -->
                    <div class="profil-header">

                        <!-- Foto -->
                        <div class="profil-avatar">
                            <?php 
                            $fotoPath = '';
                            if (!empty($user['foto_profil']) && file_exists('../../' . $user['foto_profil'])) {
                                $fotoPath = BASE_URL . '/' . $user['foto_profil'];
                            } elseif (file_exists('../../uploads/profil/' . $user['id_mahasiswa'] . '.jpg')) {
                                $fotoPath = BASE_URL . '/uploads/profil/' . $user['id_mahasiswa'] . '.jpg';
                            } elseif (file_exists('../../uploads/profil/' . $user['id_mahasiswa'] . '.png')) {
                                $fotoPath = BASE_URL . '/uploads/profil/' . $user['id_mahasiswa'] . '.png';
                            }
                            ?>
                            <?php if ($fotoPath): ?>
                                <img src="<?php echo $fotoPath; ?>" alt="Foto Profil" class="foto-profil-img">
                            <?php else: ?>
                                <i class="fas fa-user-circle"></i>
                            <?php endif; ?>
                        </div>

                        <!-- Info -->
                        <div class="profil-info">
                            <div class="nama"><?php echo htmlspecialchars($user['nama']); ?></div>
                            <div class="info-grid">
                                <div class="info-item">
                                    <label>NIM</label>
                                    <p><?php echo htmlspecialchars($user['nim']); ?></p>
                                </div>
                                <div class="info-item">
                                    <label>Program Studi</label>
                                    <p><?php echo htmlspecialchars($user['prodi'] ?? '-'); ?></p>
                                </div>
                                <div class="info-item">
                                    <label>Email</label>
                                    <p><?php echo htmlspecialchars($user['email']); ?></p>
                                </div>
                                <div class="info-item">
                                    <label>Status</label>
                                    <p>
                                        <?php echo $user['verification_token'] 
                                            ? '<span class="badge bg-success">Terverifikasi</span>' 
                                            : '<span class="badge bg-warning">Belum Verifikasi</span>'; ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- BERGABUNG -->
                    <div class="profil-bergabung">
                        <span class="label">Bergabung</span>
                        <span class="value"><?php echo date('d M Y', strtotime($user['created_at'] ?? date('Y-m-d'))); ?></span>
                    </div>

                    <!-- ORGANISASI -->
                    <div class="profil-organisasi">
                        <div class="profil-section-title">
                            Organisasi <span class="count"><?php echo count($organisasi); ?></span>
                        </div>
                        <?php if (count($organisasi) > 0): ?>
                            <div class="org-list">
                                <?php foreach ($organisasi as $org): ?>
                                    <span class="org-badge"><?php echo htmlspecialchars($org['nama_organisasi']); ?></span>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="org-empty">Belum memiliki organisasi</p>
                        <?php endif; ?>
                    </div>

                    <!-- KEGIATAN TERAKHIR -->
                    <div class="profil-kegiatan">
                        <div class="profil-section-title">
                            Kegiatan Terakhir <span class="count"><?php echo count($kegiatan); ?></span>
                        </div>
                        <?php if (count($kegiatan) > 0): ?>
                            <ul class="kegiatan-list">
                                <?php foreach ($kegiatan as $k): ?>
                                    <li class="kegiatan-item">
                                        <span class="keg-nama"><?php echo htmlspecialchars($k['judul']); ?></span>
                                        <span class="keg-org"><?php echo htmlspecialchars($k['nama_organisasi']); ?></span>
                                        <span class="keg-status">
                                            <?php 
                                                $status = $k['status_pendaftaran'] ?? $k['status'] ?? 'menunggu';
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

                    <!-- TOMBOL -->
                    <div class="profil-actions">
                        <a href="<?php echo BASE_URL; ?>/dashboard/mahasiswa/" class="btn btn-kembali">
                            <i class="fas fa-arrow-left me-1"></i> Kembali
                        </a>
                        <a href="<?php echo BASE_URL; ?>/dashboard/mahasiswa/edit_profil.php" class="btn btn-edit">
                            <i class="fas fa-edit me-1"></i> Edit Profil
                        </a>
                    </div>

                </div><!-- end inner -->
            </div><!-- end outer -->

        </div>
    </div>
</div>

<?php include '../../include/footer.php'; ?>
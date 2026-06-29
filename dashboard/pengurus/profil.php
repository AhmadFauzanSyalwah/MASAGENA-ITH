<?php
/**
 * dashboard/pengurus/profil.php
 * Halaman profil pengurus - foto kiri, info kanan
 */
session_start();
require_once '../../config/database.php';
require_once '../../config/session_check.php';

if (!isset($_SESSION['peran']) || $_SESSION['peran'] !== 'pengurus') {
    header('Location: ../../auth/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Ambil data pengurus
$query = "SELECT p.*, o.nama_organisasi 
          FROM pengurus_organisasi p
          LEFT JOIN organisasi o ON p.id_organisasi = o.id_organisasi
          WHERE p.id_pengurus = :id";
$stmt = $pdo->prepare($query);
$stmt->execute([':id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die('Data pengurus tidak ditemukan.');
}

// 5 kegiatan terbaru dari organisasi pengurus
$queryKeg = "SELECT k.judul, k.tanggal_kegiatan, k.status_publikasi
             FROM konten_kegiatan k
             WHERE k.id_organisasi = :id_org
             ORDER BY k.created_at DESC
             LIMIT 5";
$stmtKeg = $pdo->prepare($queryKeg);
$stmtKeg->execute([':id_org' => $user['id_organisasi']]);
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
                            } elseif (file_exists('../../uploads/profil/' . $user['id_pengurus'] . '.jpg')) {
                                $fotoPath = BASE_URL . '/uploads/profil/' . $user['id_pengurus'] . '.jpg';
                            } elseif (file_exists('../../uploads/profil/' . $user['id_pengurus'] . '.png')) {
                                $fotoPath = BASE_URL . '/uploads/profil/' . $user['id_pengurus'] . '.png';
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
                            <div class="nama"><?php echo htmlspecialchars($user['nama_pengurus']); ?></div>
                            <div class="info-grid">
                                <div class="info-item">
                                    <label>Email</label>
                                    <p><?php echo htmlspecialchars($user['email_pengurus'] ?? '-'); ?></p>
                                </div>
                                <div class="info-item">
                                    <label>No. HP</label>
                                    <p><?php echo htmlspecialchars($user['no_hp'] ?? '-'); ?></p>
                                </div>
                                <div class="info-item">
                                    <label>Level</label>
                                    <p><?php echo $user['level'] == 'inti' ? 'Pengurus Inti' : 'Pengurus Biasa'; ?></p>
                                </div>
                                <div class="info-item">
                                    <label>Organisasi</label>
                                    <p><?php echo htmlspecialchars($user['nama_organisasi'] ?? '-'); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- BERGABUNG -->
                    <div class="profil-bergabung">
                        <span class="label">Bergabung</span>
                        <span class="value"><?php echo date('d M Y', strtotime($user['created_at'] ?? date('Y-m-d'))); ?></span>
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
                                        <span class="keg-status">
                                            <?php 
                                                $status = $k['status_publikasi'] ?? 'draft';
                                                if ($status == 'publik') echo '<span class="badge bg-success">Publik</span>';
                                                elseif ($status == 'draft') echo '<span class="badge bg-warning">Draft</span>';
                                                else echo '<span class="badge bg-secondary">' . $status . '</span>';
                                            ?>
                                        </span>
                                        <span class="keg-tgl"><?php echo date('d M Y', strtotime($k['tanggal_kegiatan'])); ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                            <?php if (count($kegiatan) >= 5): ?>
                                <a href="<?php echo BASE_URL; ?>/dashboard/pengurus/kelola_konten.php" class="btn btn-sm btn-outline-primary mt-1">Lihat semua</a>
                            <?php endif; ?>
                        <?php else: ?>
                            <p class="org-empty">Belum ada kegiatan yang dikelola</p>
                        <?php endif; ?>
                    </div>

                    <!-- TOMBOL -->
                    <div class="profil-actions">
                        <a href="<?php echo BASE_URL; ?>/dashboard/pengurus/" class="btn btn-kembali">
                            <i class="fas fa-arrow-left me-1"></i> Kembali
                        </a>
                        <a href="<?php echo BASE_URL; ?>/dashboard/pengurus/edit_profil_pengurus.php" class="btn btn-edit">
                            <i class="fas fa-edit me-1"></i> Edit Profil
                        </a>
                    </div>

                </div><!-- end inner -->
            </div><!-- end outer -->

        </div>
    </div>
</div>

<?php include '../../include/footer.php'; ?>
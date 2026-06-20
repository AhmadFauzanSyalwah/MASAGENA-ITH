<?php
require_once '../../config/session_check.php';
require_once '../../config/database.php';

if ($_SESSION['peran'] != 'admin') {
    header("Location: ../" . $_SESSION['peran'] . "/index.php");
    exit();
}

// Menentukan tab aktif (default: admin)
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'admin';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$message = isset($_GET['msg']) ? $_GET['msg'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';

$list_data = [];

try {
    if ($tab === 'mahasiswa') {
        // AMBIL DATA DARI TABEL MAHASISWA
        if (!empty($search)) {
            $stmt = $pdo->prepare("SELECT id_mahasiswa, nim, nama, email, prodi, kontak, is_verified FROM mahasiswa WHERE nama LIKE ? OR nim LIKE ? ORDER BY id_mahasiswa DESC");
            $stmt->execute(["%$search%", "%$search%"]);
        } else {
            $stmt = $pdo->query("SELECT id_mahasiswa, nim, nama, email, prodi, kontak, is_verified FROM mahasiswa ORDER BY id_mahasiswa DESC");
        }
        $list_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        // AMBIL DATA DARI TABEL ADMINISTRATOR
        if (!empty($search)) {
            $stmt = $pdo->prepare("SELECT id_admin, username, nama_lengkap, no_hp, status_verifikasi FROM administrator WHERE nama_lengkap LIKE ? OR username LIKE ? ORDER BY id_admin DESC");
            $stmt->execute(["%$search%", "%$search%"]);
        } else {
            $stmt = $pdo->query("SELECT id_admin, username, nama_lengkap, no_hp, status_verifikasi FROM administrator ORDER BY id_admin DESC");
        }
        $list_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    die("Gagal mengambil data database: " . $e->getMessage());
}

include '../../include/header.php';
?>

<div class="container" style="padding: 2rem; background: white; border-radius: 12px; margin-top: 1rem; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
    
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem;">
        <div>
            <h2 style="margin: 0;">👥 Manajemen Data Pengguna</h2>
            <p style="margin: 0.5rem 0 0 0; color: #6c757d;">Kelola semua data pengguna berdasarkan kategori peran masing-masing.</p>
        </div>
    </div>

    <div style="display: flex; gap: 0.5rem; margin-bottom: 1.5rem; border-bottom: 2px solid #dee2e6; padding-bottom: 1px;">
        <a href="kelola_user.php?tab=admin" style="padding: 0.6rem 1.2rem; text-decoration: none; font-weight: bold; border-radius: 6px 6px 0 0; <?= $tab === 'admin' ? 'background: #007bff; color: white;' : 'color: #495057; background: #f8f9fa;' ?>">
            🛡️ Admin / Administrator
        </a>
        <a href="kelola_user.php?tab=mahasiswa" style="padding: 0.6rem 1.2rem; text-decoration: none; font-weight: bold; border-radius: 6px 6px 0 0; <?= $tab === 'mahasiswa' ? 'background: #007bff; color: white;' : 'color: #495057; background: #f8f9fa;' ?>">
            🎓 Mahasiswa
        </a>
    </div>

    <form method="GET" action="" style="display: flex; gap: 0.5rem; margin-bottom: 1.5rem;">
        <input type="hidden" name="tab" value="<?= htmlspecialchars($tab) ?>">
        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="<?= $tab === 'mahasiswa' ? 'Cari NIM atau Nama Mahasiswa...' : 'Cari Username atau Nama Admin...' ?>" style="flex: 1; padding: 0.6rem 1rem; border: 1px solid #ced4da; border-radius: 6px;">
        <button type="submit" style="background: #6c757d; color: white; border: none; padding: 0.6rem 1.2rem; border-radius: 6px; cursor: pointer;">Cari</button>
        <?php if (!empty($search)): ?>
            <a href="kelola_user.php?tab=<?= $tab ?>" style="background: #e2e3e5; color: #383d41; padding: 0.6rem 1.2rem; border-radius: 6px; text-decoration: none; display: inline-flex; align-items: center;">Reset</a>
        <?php endif; ?>
    </form>

    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            
            <?php if ($tab === 'mahasiswa'): ?>
                <thead>
                    <tr style="background-color: #f8f9fa; border-bottom: 2px solid #dee2e6;">
                        <th style="padding: 0.75rem;">NIM</th>
                        <th style="padding: 0.75rem;">Nama</th>
                        <th style="padding: 0.75rem;">Email</th>
                        <th style="padding: 0.75rem;">Prodi</th>
                        <th style="padding: 0.75rem;">Kontak</th>
                        <th style="padding: 0.75rem;">Status</th>
                        <th style="padding: 0.75rem; text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($list_data) > 0): ?>
                        <?php foreach ($list_data as $mhs): ?>
                            <tr style="border-bottom: 1px solid #dee2e6;">
                                <td style="padding: 0.75rem;"><?= htmlspecialchars($mhs['nim']) ?></td>
                                <td style="padding: 0.75rem;"><strong><?= htmlspecialchars($mhs['nama']) ?></strong></td>
                                <td style="padding: 0.75rem;"><?= htmlspecialchars($mhs['email']) ?></td>
                                <td style="padding: 0.75rem;"><?= htmlspecialchars($mhs['prodi'] ?: '-') ?></td>
                                <td style="padding: 0.75rem;"><?= htmlspecialchars($mhs['kontak'] ?: '-') ?></td>
                                <td style="padding: 0.75rem;">
                                    <span style="background: <?= $mhs['is_verified'] == 1 ? '#d4edda' : '#fff3cd' ?>; color: <?= $mhs['is_verified'] == 1 ? '#155724' : '#856404' ?>; padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.85rem;">
                                        <?= $mhs['is_verified'] == 1 ? 'Aktif' : 'Pending' ?>
                                    </span>
                                </td>
                                <td style="padding: 0.75rem; text-align: center; white-space: nowrap;">
                                    <a href="edit_mahasiswa.php?id=<?= $mhs['id_mahasiswa'] ?>" style="background: #ffc107; color: black; padding: 0.3rem 0.6rem; border-radius: 4px; text-decoration: none; font-size: 0.85rem; margin-right: 0.3rem;">Edit</a>
                                    <a href="hapus_mahasiswa.php?id=<?= $mhs['id_mahasiswa'] ?>" style="background: #dc3545; color: white; padding: 0.3rem 0.6rem; border-radius: 4px; text-decoration: none; font-size: 0.85rem;" onclick="return confirm('Hapus mahasiswa <?= htmlspecialchars($mhs['nama']) ?>?')">Hapus</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7" style="padding: 2rem; text-align: center; color: #6c757d;">Data mahasiswa tidak ditemukan.</td></tr>
                    <?php endif; ?>
                </tbody>

            <?php else: ?>
                <thead>
                    <tr style="background-color: #f8f9fa; border-bottom: 2px solid #dee2e6;">
                        <th style="padding: 0.75rem;">Nama Lengkap</th>
                        <th style="padding: 0.75rem;">Username</th>
                        <th style="padding: 0.75rem;">No. HP</th>
                        <th style="padding: 0.75rem;">Status</th>
                        <th style="padding: 0.75rem; text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($list_data) > 0): ?>
                        <?php foreach ($list_data as $admin): ?>
                            <tr style="border-bottom: 1px solid #dee2e6;">
                                <td style="padding: 0.75rem;"><strong><?= htmlspecialchars($admin['nama_lengkap']) ?></strong></td>
                                <td style="padding: 0.75rem;"><?= htmlspecialchars($admin['username']) ?></td>
                                <td style="padding: 0.75rem;"><?= htmlspecialchars($admin['no_hp'] ?: '-') ?></td>
                                <td style="padding: 0.75rem;">
                                    <span style="background: #d4edda; color: #155724; padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.85rem;">
                                        <?= htmlspecialchars($admin['status_verifikasi']) ?>
                                    </span>
                                </td>
                                <td style="padding: 0.75rem; text-align: center; white-space: nowrap;">
                                    <a href="edit_user.php?id=<?= $admin['id_admin'] ?>" style="background: #ffc107; color: black; padding: 0.3rem 0.6rem; border-radius: 4px; text-decoration: none; font-size: 0.85rem; margin-right: 0.3rem;">Edit</a>
                                    <a href="hapus_user.php?id=<?= $admin['id_admin'] ?>" style="background: #dc3545; color: white; padding: 0.3rem 0.6rem; border-radius: 4px; text-decoration: none; font-size: 0.85rem;" onclick="return confirm('Hapus admin <?= htmlspecialchars($admin['username']) ?>?')">Hapus</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" style="padding: 2rem; text-align: center; color: #6c757d;">Data administrator tidak ditemukan.</td></tr>
                    <?php endif; ?>
                </tbody>
            <?php endif; ?>

        </table>
    </div>

    <div style="margin-top: 1.5rem; border-top: 1px solid #dee2e6; padding-top: 1rem;">
        <a href="index.php" style="text-decoration: none; color: #007bff; font-weight: 500;">← Kembali ke Dashboard</a>
    </div>
</div>

<?php include '../../include/footer.php'; ?>
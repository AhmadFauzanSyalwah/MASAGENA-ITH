<?php
require_once '../../config/session_check.php';
require_once '../../config/database.php';

if ($_SESSION['peran'] != 'admin') {
    header("Location: ../" . $_SESSION['peran'] . "/index.php");
    exit();
}

$message = '';
$status = '';

// 1. PROSES AKSI VERIFIKASI / TOLAK (Disulut saat tombol diklik)
if (isset($_GET['id']) && isset($_GET['action'])) {
    $id_target = intval($_GET['id']);
    $action = $_GET['action'];
    $type = isset($_GET['type']) ? $_GET['type'] : 'mahasiswa'; // default tipe data

    // Menentukan target tabel dan kolom status berdasarkan tipe user
    // (Sesuaikan nama tabel & kolom status jika nanti ada tabel 'pengurus' khusus)
    if ($type === 'mahasiswa') {
        $table = 'mahasiswa';
        $status_column = 'is_verified';
        $id_column = 'id_mahasiswa';
        $new_status = ($action === 'verify') ? 1 : 0; // Tabel mahasiswa pakai angka (1/0)
    } else {
        $table = 'administrator';
        $status_column = 'status_verifikasi';
        $id_column = 'id_admin';
        $new_status = ($action === 'verify') ? 'terverifikasi' : 'ditolak'; // Tabel admin pakai string
    }

    try {
        $stmt = $pdo->prepare("UPDATE $table SET $status_column = ? WHERE $id_column = ?");
        $stmt->execute([$new_status, $id_target]);
        
        $msg_text = ($action === 'verify') ? "Akun berhasil diverifikasi!" : "Akun telah ditolak!";
        header("Location: verifikasi_akun.php?msg=" . urlencode($msg_text) . "&status=success");
        exit();
    } catch (PDOException $e) {
        $message = "Gagal memperbarui status verifikasi: " . $e->getMessage();
        $status = 'danger';
    }
}

// 2. AMBIL DATA PENDING DARI TABEL MAHASISWA (is_verified = 0)
try {
    $stmt_mhs = $pdo->query("
        SELECT id_mahasiswa AS id, nim, nama, email, 'mahasiswa' AS tipe_user 
        FROM mahasiswa 
        WHERE is_verified = 0 OR is_verified IS NULL
        ORDER BY id_mahasiswa DESC
    ");
    $pending_mahasiswa = $stmt_mhs->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $pending_mahasiswa = []; // Set kosong jika ada kendala struktur tabel
}

// 3. AMBIL DATA PENDING DARI TABEL ADMINISTRATOR (status_verifikasi = 'pending')
try {
    $stmt_admin = $pdo->query("
        SELECT id_admin AS id, username AS nim, nama_lengkap AS nama, 'admin' AS email, 'admin' AS tipe_user 
        FROM administrator 
        WHERE status_verifikasi = 'pending'
        ORDER BY id_admin DESC
    ");
    $pending_admin = $stmt_admin->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $pending_admin = [];
}

// Gabungkan semua data antrean pending ke dalam satu array list
$all_pending = array_merge($pending_mahasiswa, $pending_admin);

include '../../include/header.php';
?>

<div class="container" style="padding: 2rem; background: white; border-radius: 12px; margin-top: 1rem; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
    <h2>⏳ Persetujuan & Verifikasi Akun Baru</h2>
    <p style="color: #6c757d;">Periksa dan aktifkan akun pendaftar pengurus atau mahasiswa baru agar dapat mengakses sistem.</p>

    <?php if (isset($_GET['msg'])): ?>
        <div style="padding: 1rem; margin-bottom: 1.5rem; border-radius: 8px; background-color: #d4edda; color: #155724; font-weight: 500;">
            <?= htmlspecialchars($_GET['msg']) ?>
        </div>
    <?php endif; ?>

    <?php if ($message): ?>
        <div style="padding: 1rem; margin-bottom: 1.5rem; border-radius: 8px; background-color: #f8d7da; color: #721c24;">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.95rem;">
            <thead>
                <tr style="background-color: #f8f9fa; border-bottom: 2px solid #dee2e6;">
                    <th style="padding: 0.75rem 1rem;">Nama Lengkap</th>
                    <th style="padding: 0.75rem 1rem;">NIM / Username</th>
                    <th style="padding: 0.75rem 1rem;">Kontak / Email</th>
                    <th style="padding: 0.75rem 1rem;">Kategori Peran</th>
                    <th style="padding: 0.75rem 1rem; text-align: center;">Aksi Persetujuan</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($all_pending) > 0): ?>
                    <?php foreach ($all_pending as $user): ?>
                        <tr style="border-bottom: 1px solid #dee2e6;">
                            <td style="padding: 0.75rem 1rem;"><strong><?= htmlspecialchars($user['nama']) ?></strong></td>
                            <td style="padding: 0.75rem 1rem;"><code style="background: #f1f3f5; padding: 0.2rem 0.4rem; border-radius: 4px;"><?= htmlspecialchars($user['nim']) ?></code></td>
                            <td style="padding: 0.75rem 1rem; color: #495057;"><?= htmlspecialchars($user['email']) ?></td>
                            <td style="padding: 0.75rem 1rem;">
                                <span style="background: <?= $user['tipe_user'] === 'mahasiswa' ? '#e3f2fd' : '#e8eaf6' ?>; color: <?= $user['tipe_user'] === 'mahasiswa' ? '#0d47a1' : '#1a237e' ?>; padding: 0.2rem 0.6rem; border-radius: 20px; font-size: 0.8rem; font-weight: bold; text-transform: uppercase;">
                                    <?= $user['tipe_user'] ?>
                                </span>
                            </td>
                            <td style="padding: 0.75rem 1rem; text-align: center; white-space: nowrap;">
                                <a href="verifikasi_akun.php?id=<?= $user['id'] ?>&type=<?= $user['tipe_user'] ?>&action=verify" style="background: #28a745; color: white; padding: 0.4rem 0.8rem; border-radius: 6px; text-decoration: none; font-size: 0.85rem; margin-right: 0.4rem; font-weight: 500;">Setujui</a>
                                <a href="verifikasi_akun.php?id=<?= $user['id'] ?>&type=<?= $user['tipe_user'] ?>&action=reject" style="background: #dc3545; color: white; padding: 0.4rem 0.8rem; border-radius: 6px; text-decoration: none; font-size: 0.85rem; font-weight: 500;" onclick="return confirm('Apakah Anda yakin ingin menolak pendaftaran dari <?= htmlspecialchars($user['nama']) ?>?')">Tolak</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="padding: 3rem; text-align: center; color: #6c757d;">
                            🎉 Bagus! Tidak ada akun baru yang sedang tertahan dalam antrean verifikasi.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div style="margin-top: 2rem; border-top: 1px solid #dee2e6; padding-top: 1rem;">
        <a href="index.php" style="text-decoration: none; color: #007bff; font-weight: 500;">← Kembali ke Dashboard Utama</a>
    </div>
</div>

<?php include '../../include/footer.php'; ?>
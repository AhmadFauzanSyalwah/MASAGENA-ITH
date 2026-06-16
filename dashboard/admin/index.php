<?php
require_once '../../config/session_check.php';
require_once '../../config/database.php';

if ($_SESSION['peran'] != 'admin') {
    header("Location: ../" . $_SESSION['peran'] . "/index.php");
    exit();
}

// Statistik
$total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$total_organisasi = $pdo->query("SELECT COUNT(*) FROM organisasi")->fetchColumn();
$total_konten = $pdo->query("SELECT COUNT(*) FROM konten_kegiatan")->fetchColumn();
$pending_verifikasi = $pdo->query("SELECT COUNT(*) FROM users WHERE status_verifikasi = 'pending' AND peran = 'pengurus'")->fetchColumn();

// 5 User pending terbaru (pengurus)
$stmt = $pdo->query("
    SELECT id_user, nama, email, created_at 
    FROM users 
    WHERE status_verifikasi = 'pending' AND peran = 'pengurus'
    ORDER BY created_at DESC 
    LIMIT 5
");
$pending_users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 5 Konten terbaru
$stmt = $pdo->query("
    SELECT k.id_konten, k.judul, k.created_at, o.nama_organisasi 
    FROM konten_kegiatan k
    JOIN organisasi o ON k.id_organisasi = o.id_organisasi
    ORDER BY k.created_at DESC 
    LIMIT 5
");
$latest_konten = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../../include/header.php';
?>

    <div class="dashboard-welcome">
        <h1>Dashboard Administrator</h1>
        <p>Selamat datang, <?= htmlspecialchars($_SESSION['nama']) ?>. Anda memiliki akses penuh ke sistem.</p>
    </div>

    <div class="stats-grid" style="display: flex; gap: 1.5rem; flex-wrap: wrap; margin-bottom: 2rem;">
        <div class="stat-card" style="background: white; padding: 1.5rem; border-radius: 12px; flex: 1; text-align: center;">
            <h3><?= $total_users ?></h3>
            <p>Total Pengguna</p>
        </div>
        <div class="stat-card" style="background: white; padding: 1.5rem; border-radius: 12px; flex: 1; text-align: center;">
            <h3><?= $total_organisasi ?></h3>
            <p>Organisasi</p>
        </div>
        <div class="stat-card" style="background: white; padding: 1.5rem; border-radius: 12px; flex: 1; text-align: center;">
            <h3><?= $total_konten ?></h3>
            <p>Total Konten</p>
        </div>
        <div class="stat-card" style="background: white; padding: 1.5rem; border-radius: 12px; flex: 1; text-align: center;">
            <h3><?= $pending_verifikasi ?></h3>
            <p>Verifikasi Pending</p>
        </div>
    </div>

    <div style="display: flex; flex-wrap: wrap; gap: 2rem;">
        <!-- Daftar User Pending Verifikasi -->
        <div style="flex: 1; min-width: 250px; background: white; border-radius: 12px; padding: 1rem; box-shadow: var(--shadow-sm);">
            <h3>⏳ Verifikasi Akun Pengurus</h3>
            <?php if (count($pending_users) > 0): ?>
                <ul style="list-style: none; padding: 0;">
                    <?php foreach ($pending_users as $u): ?>
                        <li style="padding: 0.5rem 0; border-bottom: 1px solid var(--border);">
                            <strong><?= htmlspecialchars($u['nama']) ?></strong><br>
                            <small><?= $u['email'] ?></small>
                            <div style="margin-top: 0.3rem;">
                                <a href="verifikasi_akun.php?id=<?= $u['id_user'] ?>&action=verify" class="btn-sm" style="background-color: #28a745; color: white;">Verifikasi</a>
                                <a href="verifikasi_akun.php?id=<?= $u['id_user'] ?>&action=reject" class="btn-sm" style="background-color: #dc3545; color: white;">Tolak</a>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <div style="margin-top: 0.5rem;"><a href="verifikasi_akun.php" class="btn-sm">Lihat Semua</a></div>
            <?php else: ?>
                <p>Tidak ada pending verifikasi.</p>
            <?php endif; ?>
        </div>

        <!-- Konten Terbaru -->
        <div style="flex: 2; min-width: 300px; background: white; border-radius: 12px; padding: 1rem; box-shadow: var(--shadow-sm);">
            <h3>📄 Konten Terbaru</h3>
            <?php if (count($latest_konten) > 0): ?>
                <ul style="list-style: none; padding: 0;">
                    <?php foreach ($latest_konten as $k): ?>
                        <li style="padding: 0.5rem 0; border-bottom: 1px solid var(--border);">
                            <a href="pengawasan_konten.php?edit=<?= $k['id_konten'] ?>" style="text-decoration: none; font-weight: 500;"><?= htmlspecialchars($k['judul']) ?></a>
                            <span style="float: right; font-size: 0.75rem;"><?= date('d/m/Y', strtotime($k['created_at'])) ?></span>
                            <br><small>Organisasi: <?= htmlspecialchars($k['nama_organisasi']) ?></small>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <a href="pengawasan_konten.php" class="btn-sm">Kelola Semua Konten</a>
            <?php else: ?>
                <p>Belum ada konten.</p>
            <?php endif; ?>
        </div>
    </div>

<?php include '../../include/footer.php'; ?>
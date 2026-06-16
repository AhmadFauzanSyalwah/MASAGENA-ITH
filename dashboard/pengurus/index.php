<?php
require_once '../../config/session_check.php';
require_once '../../config/database.php';

if ($_SESSION['peran'] != 'pengurus') {
    header("Location: ../" . $_SESSION['peran'] . "/index.php");
    exit();
}

$id_organisasi = $_SESSION['id_organisasi'];
$level = $_SESSION['level'] ?? 'biasa';

// Statistik
$stmt = $pdo->prepare("SELECT COUNT(*) FROM konten_kegiatan WHERE id_organisasi = ?");
$stmt->execute([$id_organisasi]);
$total_konten = $stmt->fetchColumn();

$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM pendaftaran p
    JOIN konten_kegiatan k ON p.id_konten = k.id_konten
    WHERE k.id_organisasi = ? AND p.status_pendaftaran = 'menunggu'
");
$stmt->execute([$id_organisasi]);
$total_pendaftaran_menunggu = $stmt->fetchColumn();

$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM aspirasi 
    WHERE id_organisasi_tujuan = ? AND status != 'direspons'
");
$stmt->execute([$id_organisasi]);
$total_aspirasi_baru = $stmt->fetchColumn();

// Jika pengurus inti, ambil data tambahan
$pengurus = [];
$kegiatan_terbaru = [];
if ($level === 'inti') {
    // Daftar pengurus aktif (semua user dengan peran 'pengurus' dan id_organisasi ini)
    $stmt = $pdo->prepare("
        SELECT id_user, nama, level, created_at 
        FROM users 
        WHERE peran = 'pengurus' AND id_organisasi = ?
        ORDER BY level DESC, nama ASC
    ");
    $stmt->execute([$id_organisasi]);
    $pengurus = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Daftar 5 kegiatan terbaru organisasi
    $stmt = $pdo->prepare("
        SELECT id_konten, judul, tanggal_kegiatan, status_publikasi, created_at 
        FROM konten_kegiatan 
        WHERE id_organisasi = ? 
        ORDER BY created_at DESC 
        LIMIT 5
    ");
    $stmt->execute([$id_organisasi]);
    $kegiatan_terbaru = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

include '../../include/header.php';
?>

    <div class="dashboard-welcome">
        <h1>Dashboard Pengurus</h1>
        <p>Selamat datang, <?= htmlspecialchars($_SESSION['nama']) ?>
            (<?= $level == 'inti' ? 'Pengurus Inti' : 'Pengurus Biasa' ?>)</p>
    </div>

    <!-- Statistik Card -->
    <div class="stats-grid" style="display: flex; gap: 1.5rem; flex-wrap: wrap; margin-bottom: 2rem;">
        <div class="stat-card" style="background: white; padding: 1.5rem; border-radius: 12px; flex: 1; text-align: center; box-shadow: var(--shadow-sm);">
            <h3 style="margin: 0; font-size: 2rem;"><?= $total_konten ?></h3>
            <p style="margin: 0; color: var(--text-muted);">Total Konten</p>
        </div>
        <div class="stat-card" style="background: white; padding: 1.5rem; border-radius: 12px; flex: 1; text-align: center; box-shadow: var(--shadow-sm);">
            <h3 style="margin: 0; font-size: 2rem;"><?= $total_pendaftaran_menunggu ?></h3>
            <p style="margin: 0; color: var(--text-muted);">Pendaftaran Menunggu</p>
        </div>
        <div class="stat-card" style="background: white; padding: 1.5rem; border-radius: 12px; flex: 1; text-align: center; box-shadow: var(--shadow-sm);">
            <h3 style="margin: 0; font-size: 2rem;"><?= $total_aspirasi_baru ?></h3>
            <p style="margin: 0; color: var(--text-muted);">Aspirasi Baru</p>
        </div>
    </div>

<?php if ($level === 'inti'): ?>
    <div style="display: flex; flex-wrap: wrap; gap: 2rem; margin-top: 1rem;">
        <!-- Daftar Pengurus Aktif -->
        <div style="flex: 1; min-width: 250px; background: white; border-radius: 12px; padding: 1rem; box-shadow: var(--shadow-sm);">
            <h3 style="border-left: 4px solid var(--accent); padding-left: 0.75rem; margin-bottom: 1rem;">Daftar Pengurus Aktif</h3>
            <?php if (count($pengurus) > 0): ?>
                <ul style="list-style: none; padding: 0;">
                    <?php foreach ($pengurus as $p): ?>
                        <li style="padding: 0.5rem 0; border-bottom: 1px solid var(--border);">
                            <strong><?= htmlspecialchars($p['nama']) ?></strong>
                            <span style="float: right; font-size: 0.75rem; color: var(--text-muted);">
                                <?= $p['level'] == 'inti' ? 'Pengurus Inti' : 'Anggota' ?>
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>Belum ada pengurus selain Anda.</p>
            <?php endif; ?>
            <div style="margin-top: 0.5rem;">
                <a href="manajemen_pengurus.php" class="btn-sm">Kelola Pengurus</a>
            </div>
        </div>

        <!-- Daftar Kegiatan Terbaru -->
        <div style="flex: 2; min-width: 300px; background: white; border-radius: 12px; padding: 1rem; box-shadow: var(--shadow-sm);">
            <h3 style="border-left: 4px solid var(--accent); padding-left: 0.75rem; margin-bottom: 1rem;">Kegiatan Terbaru</h3>
            <?php if (count($kegiatan_terbaru) > 0): ?>
                <ul style="list-style: none; padding: 0;">
                    <?php foreach ($kegiatan_terbaru as $k): ?>
                        <li style="padding: 0.5rem 0; border-bottom: 1px solid var(--border);">
                            <a href="edit_konten.php?id=<?= $k['id_konten'] ?>" style="text-decoration: none; color: var(--primary); font-weight: 500;">
                                <?= htmlspecialchars($k['judul']) ?>
                            </a>
                            <span style="float: right; font-size: 0.75rem; color: var(--text-muted);">
                                <?= date('d/m/Y', strtotime($k['tanggal_kegiatan'])) ?>
                            </span>
                            <div>
                                <span style="font-size: 0.7rem; color: <?= $k['status_publikasi'] == 'publish' ? 'green' : 'orange'; ?>;">
                                    <?= ucfirst($k['status_publikasi']) ?>
                                </span>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>Belum ada kegiatan. <a href="kelola_konten.php">Buat kegiatan pertama</a></p>
            <?php endif; ?>
            <div style="margin-top: 0.5rem;">
                <a href="kelola_konten.php" class="btn-sm">Lihat Semua</a>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php include '../../include/footer.php'; ?>
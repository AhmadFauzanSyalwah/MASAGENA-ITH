<?php
require_once '../../config/session_check.php';
require_once '../../config/database.php';

if ($_SESSION['peran'] != 'admin') {
    header("Location: ../" . $_SESSION['peran'] . "/index.php");
    exit();
}

// =========================================================================
// 1. ENDPOINT AJAX: MENANGKAPI PERMINTAAN REAL-TIME DARI JAVASCRIPT
// =========================================================================
if (isset($_GET['action']) && $_GET['action'] === 'get_pending') {
    header('Content-Type: application/json');
    try {
        $pending_verifikasi = $pdo->query("SELECT COUNT(*) FROM pengurus_organisasi WHERE status_verifikasi = 'Belum'")->fetchColumn(); 

        $stmt = $pdo->query("
            SELECT p.id_pengurus AS id_user, p.nama_pengurus AS nama, o.nama_organisasi 
            FROM pengurus_organisasi p
            LEFT JOIN organisasi o ON p.id_organisasi = o.id_organisasi
            WHERE p.status_verifikasi = 'Belum'
            ORDER BY p.id_pengurus DESC
            LIMIT 5
        ");
        $pending_users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'total' => (int)$pending_verifikasi,
            'users' => $pending_users
        ]);
        exit();
    } catch (Exception $e) {
        echo json_encode(['total' => 0, 'users' => [], 'error' => $e->getMessage()]);
        exit();
    }
}

// =========================================================================
// 2. DATA AWAL SAAT HALAMAN DIMUAT (DIKEMBALIKAN KE BENTUK ASLI)
// =========================================================================
// Total Pengguna digabung (Mahasiswa + Pengurus)
$total_mhs = $pdo->query("SELECT COUNT(*) FROM tbmahasiswa")->fetchColumn();
$total_pg  = $pdo->query("SELECT COUNT(*) FROM pengurus_organisasi")->fetchColumn();
$total_users = $total_mhs + $total_pg;

$total_organisasi = $pdo->query("SELECT COUNT(*) FROM organisasi")->fetchColumn();
$total_konten = $pdo->query("SELECT COUNT(*) FROM konten_kegiatan")->fetchColumn();

// Targetkan ke tabel Pengurus dengan status 'Belum'
$pending_verifikasi = $pdo->query("SELECT COUNT(*) FROM pengurus_organisasi WHERE status_verifikasi = 'Belum'")->fetchColumn(); 

// Ambil 5 Pengurus Pending terbaru
$stmt = $pdo->query("
    SELECT p.id_pengurus AS id_user, p.nama_pengurus AS nama, o.nama_organisasi 
    FROM pengurus_organisasi p
    LEFT JOIN organisasi o ON p.id_organisasi = o.id_organisasi
    WHERE p.status_verifikasi = 'Belum'
    ORDER BY p.id_pengurus DESC
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
        <p>Selamat datang, <?= htmlspecialchars($_SESSION['nama'] ?? 'Admin') ?>. Anda memiliki akses penuh ke sistem.</p>
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
            <h3 id="stat-pending-count"><?= $pending_verifikasi ?></h3>
            <p>Verifikasi Pending</p>
        </div>
    </div>

    <div style="display: flex; flex-wrap: wrap; gap: 2rem;">
        <div style="flex: 1; min-width: 250px; background: white; border-radius: 12px; padding: 1rem; box-shadow: var(--shadow-sm);">
            <h3>⏳ Verifikasi Akun Pengurus</h3>
            
            <div id="pending-users-container">
                <?php if (count($pending_users) > 0): ?>
                    <ul style="list-style: none; padding: 0;">
                        <?php foreach ($pending_users as $u): ?>
                            <li style="padding: 0.5rem 0; border-bottom: 1px solid var(--border);">
                                <strong><?= htmlspecialchars($u['nama']) ?></strong><br>
                                <small>Organisasi: <?= htmlspecialchars($u['nama_organisasi'] ?? 'Tanpa Organisasi') ?></small>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <div style="margin-top: 0.5rem;"><a href="verifikasi_akun.php" class="btn-sm">Lihat Semua</a></div>
                <?php else: ?>
                    <p>Tidak ada pending verifikasi.</p>
                <?php endif; ?>
            </div>
        </div>

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

<script>
function perbaruiDataPendingRealtime() {
    fetch('index.php?action=get_pending')
        .then(response => response.json())
        .then(data => {
            // Update angka total
            document.getElementById('stat-pending-count').innerText = data.total;

            const container = document.getElementById('pending-users-container');
            
            if (data.users.length > 0) {
                let htmlList = '<ul style="list-style: none; padding: 0;">';
                
                data.users.forEach(u => {
                    const namaBersih = u.nama.replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;");
                    const orgBersih = (u.nama_organisasi || 'Tanpa Organisasi').replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;");
                    
                    htmlList += `
                        <li style="padding: 0.5rem 0; border-bottom: 1px solid var(--border);">
                            <strong>${namaBersih}</strong><br>
                            <small>Organisasi: ${orgBersih}</small>
                        </li>
                    `;
                });
                
                htmlList += '</ul>';
                htmlList += '<div style="margin-top: 0.5rem;"><a href="verifikasi_akun.php" class="btn-sm">Lihat Semua</a></div>';
                container.innerHTML = htmlList;
            } else {
                container.innerHTML = '<p>Tidak ada pending verifikasi.</p>';
            }
        })
        .catch(error => console.error('Gagal mengambil data real-time:', error));
}

// Cek otomatis setiap 3 detik
setInterval(perbaruiDataPendingRealtime, 3000);
</script>

<?php include '../../include/footer.php'; ?>
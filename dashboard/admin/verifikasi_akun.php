<?php
session_start();
// Pastikan path ke database sesuai dengan struktur folder Anda
require_once '../../config/database.php';

// Proteksi Halaman: Pastikan yang mengakses adalah Admin
if (!isset($_SESSION['peran']) || $_SESSION['peran'] !== 'admin') {
    echo "<script>alert('Anda tidak memiliki akses ke halaman ini!'); window.location.href='../../auth/login.php';</script>";
    exit;
}

$pesan = "";
$tipe_pesan = "";

// =========================================================================
// 1. FUNGSI GATEWAY WHATSAPP (FONNTE) - Diadopsi dari Superadmin
// =========================================================================
function kirimWA($nomor_hp, $pesan) {
    $curl = curl_init();
    curl_setopt_array($curl, array(
      CURLOPT_URL => 'https://api.fonnte.com/send',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS => array(
        'target' => $nomor_hp,
        'message' => $pesan,
        'countryCode' => '62',
      ),
      CURLOPT_HTTPHEADER => array(
        'Authorization: KjgzbjYq9r4TE32YtJvY' // Token Fonnte Anda
      ),
    ));
    $response = curl_exec($curl);
    curl_close($curl);
    return $response;
}

// ==========================================
// 2. PROSES VERIFIKASI AKUN & KIRIM WA
// ==========================================
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['verifikasi_pengurus'])) {
    $id_pengurus_verif = $_POST['id_pengurus'];
    
    try {
        // Ambil data pengurus sekaligus nama organisasinya
        $stmt_cek = $pdo->prepare("
            SELECT p.*, o.nama_organisasi 
            FROM pengurus_organisasi p 
            LEFT JOIN organisasi o ON p.id_organisasi = o.id_organisasi 
            WHERE p.id_pengurus = ?
        ");
        $stmt_cek->execute([$id_pengurus_verif]);
        $pengurus_data = $stmt_cek->fetch();

        if ($pengurus_data) {
            // Cek jika nomor HP kosong, batalkan proses verifikasi
            if (empty($pengurus_data['no_hp'])) {
                $pesan = "Gagal! Nomor HP Pengurus belum diisi. Harap lengkapi di menu Manajemen Pengurus agar WA bisa dikirim.";
                $tipe_pesan = "error";
            } else {
                // Generate ID Akses Baru (Format: PGR-Tahun-4Digit)
                $id_akses_baru = "PGR-" . date("Y") . "-" . rand(1000, 9999);
                
                // Update Database
                $stmt = $pdo->prepare("UPDATE pengurus_organisasi SET id_akses = ?, status_verifikasi = 'Terverifikasi' WHERE id_pengurus = ?");
                
                if($stmt->execute([$id_akses_baru, $id_pengurus_verif])) {
                    // Pesan WhatsApp yang akan dikirim
                    // Pesan WhatsApp yang akan dikirim
                $pesan_wa = "🔑 VERIFIKASI AKUN PENGURUS ORGANISASI\n\n"
                        . "Halo " . $pengurus_data['nama_pengurus'] . ",\n"
                        . "Akun Pengurus Organisasi Anda telah diverifikasi oleh Superadmin.\n\n"
                        . "Berikut adalah ID AKSES login Anda:\n"
                        . "👉 [" . $id_akses_baru . "]\n\n"
                        . "Jaga kerahasiaan kode ini";
                    
                    // Eksekusi pengiriman WA
                    kirimWA($pengurus_data['no_hp'], $pesan_wa);
                    
                    $pesan = "Pengurus Organisasi berhasil diverifikasi! ID Akses [" . $id_akses_baru . "] telah dikirim via WhatsApp.";
                    $tipe_pesan = "success";
                }
            }
        }
    } catch (PDOException $e) {
        $pesan = "Terjadi kesalahan: " . $e->getMessage();
        $tipe_pesan = "error";
    }
}

// ==========================================
// 3. READ DATA PENGURUS (DENGAN PENCARIAN)
// ==========================================
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$data_pengurus = [];

try {
    if (!empty($search)) {
        // Mode Pencarian
        $query = "
            SELECT p.*, o.nama_organisasi 
            FROM pengurus_organisasi p 
            LEFT JOIN organisasi o ON p.id_organisasi = o.id_organisasi 
            WHERE p.nama_pengurus LIKE :search OR p.id_akses LIKE :search OR o.nama_organisasi LIKE :search
            ORDER BY p.id_pengurus DESC
        ";
        $stmt = $pdo->prepare($query);
        $stmt->execute([':search' => "%$search%"]);
        $data_pengurus = $stmt->fetchAll() ?: [];
    } else {
        // Mode Tampil Semua
        $query = "
            SELECT p.*, o.nama_organisasi 
            FROM pengurus_organisasi p 
            LEFT JOIN organisasi o ON p.id_organisasi = o.id_organisasi 
            ORDER BY p.status_verifikasi ASC, p.id_pengurus DESC
        ";
        $data_pengurus = $pdo->query($query)->fetchAll() ?: [];
    }
} catch (PDOException $e) {
    die("Error SQL: " . $e->getMessage());
}

// Include Header
include '../../include/header.php';
?>

<style>
    .page-title { margin-bottom: 20px; font-size: 24px; color: #1F3D68; font-family: 'Montserrat', sans-serif; }
    .alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: bold; }
    .alert-success { background-color: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
    .alert-error { background-color: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
    
    .card { background: #fff; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); padding: 25px; margin-bottom: 30px; border: 1px solid #eee; }
    .card-header { margin-bottom: 20px; border-bottom: 2px solid #f3f4f6; padding-bottom: 10px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; }
    .card-header h3 { font-size: 18px; color: #1F3D68; display: flex; align-items: center; gap: 8px; margin: 0; }
    
    /* Search Box Styles */
    .search-box { display: flex; gap: 10px; }
    .search-input { padding: 8px 15px; border: 1px solid #d1d5db; border-radius: 8px; outline: none; width: 250px; font-family: 'Inter', sans-serif; }
    .search-input:focus { border-color: #F59E0B; box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.1); }
    .btn-search { background: #1F3D68; color: white; border: none; padding: 8px 15px; border-radius: 8px; cursor: pointer; font-weight: bold; transition: 0.3s; }
    .btn-search:hover { background: #162c4a; }
    .btn-reset { background: #f3f4f6; color: #4b5563; border: 1px solid #d1d5db; padding: 8px 15px; border-radius: 8px; text-decoration: none; font-weight: bold; font-size: 13px; display: flex; align-items: center;}
    
    .data-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    .data-table th, .data-table td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #eee; font-size: 14px; }
    .data-table th { background: #f9fafb; color: #6b7280; text-transform: uppercase; font-size: 12px; }
    
    /* Badge Status */
    .badge-status { padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; display: inline-block; text-align: center; }
    .status-verified { background: #d1fae5; color: #065f46; }
    .status-unverified { background: #fee2e2; color: #991b1b; }
    .badge-id { background: #f3f4f6; color: #1F3D68; padding: 4px 8px; border-radius: 6px; font-family: monospace; font-weight: bold; border: 1px solid #e5e7eb; }
    
    /* Tombol Aksi */
    .btn { padding: 8px 15px; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; transition: 0.3s; font-size: 12px; display: inline-flex; align-items: center; gap: 5px; }
    .btn-success { background: #10b981; color: #fff; }
    .btn-success:hover { background: #059669; }
    .btn-disabled { background: #e5e7eb; color: #9ca3af; cursor: not-allowed; }
</style>

<div style="padding: 20px;">
    <h1 class="page-title"><i class="fa-solid fa-user-check" style="color: #F59E0B;"></i> Verifikasi Akun Pengurus</h1>

    <?php if ($pesan): ?>
        <div class="alert <?= $tipe_pesan == 'success' ? 'alert-success' : 'alert-error' ?>">
            <i class="fa-solid <?= $tipe_pesan == 'success' ? 'fa-circle-check' : 'fa-circle-exclamation' ?>"></i> <?= $pesan ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <h3><i class="fa-solid fa-list-check"></i> Daftar Antrean Verifikasi</h3>
            
            <form action="" method="GET" class="search-box">
                <input type="text" name="search" class="search-input" placeholder="Cari Nama / ID Akses..." value="<?= htmlspecialchars($search) ?>">
                <button type="submit" class="btn-search"><i class="fa-solid fa-search"></i> Cari</button>
                <?php if (!empty($search)): ?>
                    <a href="verifikasi_akun.php" class="btn-reset">Reset</a>
                <?php endif; ?>
            </form>
        </div>

        <div style="overflow-x: auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Nama Pengurus</th>
                        <th>Organisasi</th>
                        <th>Kontak WA</th>
                        <th>Status</th>
                        <th>ID Akses (Login)</th>
                        <th style="text-align: center;">Aksi Verifikasi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data_pengurus)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center; color: #6b7280; padding: 30px 0;">
                                📭 Tidak ditemukan data pengurus yang sesuai.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($data_pengurus as $pg): ?>
                            <?php 
                                $status_teks = strtolower(htmlspecialchars($pg['status_verifikasi']));
                                $is_verified = ($status_teks === 'sudah' || $status_teks === 'terverifikasi');
                            ?>
                        <tr>
                            <td style="font-weight: bold; color: #1F3D68;"><?= htmlspecialchars($pg['nama_pengurus']) ?></td>
                            <td><?= htmlspecialchars($pg['nama_organisasi'] ?? '-') ?></td>
                            <td>
                                <?php if (!empty($pg['no_hp'])): ?>
                                    <i class="fa-brands fa-whatsapp" style="color: #25D366;"></i> <?= htmlspecialchars($pg['no_hp']) ?>
                                <?php else: ?>
                                    <span style="color: #ef4444; font-size: 12px;">Belum Diisi</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge-status <?= $is_verified ? 'status-verified' : 'status-unverified' ?>">
                                    <?= $is_verified ? '<i class="fa-solid fa-check-circle"></i> Terverifikasi' : '<i class="fa-solid fa-clock"></i> Belum Verifikasi' ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($is_verified && !empty($pg['id_akses'])): ?>
                                    <span class="badge-id"><?= htmlspecialchars($pg['id_akses']) ?></span>
                                <?php else: ?>
                                    <span style="color: #9ca3af; font-size: 12px; font-style: italic;">Menunggu Verifikasi</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: center;">
                                <?php if (!$is_verified): ?>
                                    <form action="" method="POST" onsubmit="return confirm('Verifikasi akun <?= htmlspecialchars($pg['nama_pengurus']) ?>? Sistem akan otomatis mengirimkan ID Akses login ke WhatsApp mereka.');">
                                        <input type="hidden" name="id_pengurus" value="<?= $pg['id_pengurus'] ?>">
                                        <button type="submit" name="verifikasi_pengurus" class="btn btn-success">
                                            <i class="fa-solid fa-paper-plane"></i> Verifikasi & Kirim WA
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <button class="btn btn-disabled" disabled>
                                        <i class="fa-solid fa-check"></i> Selesai
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php 
// Include Footer
include '../../include/footer.php'; 
?>
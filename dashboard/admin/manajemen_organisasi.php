<?php
session_start();
// Pastikan path database.php sesuai dengan struktur folder Anda
require_once '../../config/database.php';

// Proteksi Halaman: Pastikan yang mengakses adalah Admin
if (!isset($_SESSION['peran']) || $_SESSION['peran'] !== 'admin') {
    echo "<script>alert('Anda tidak memiliki akses ke halaman ini!'); window.location.href='../../auth/login.php';</script>";
    exit;
}

$pesan = "";
$tipe_pesan = "";

// ==========================================
// 1. PROSES TAMBAH ORGANISASI BARU
// ==========================================
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['tambah_organisasi'])) {
    $nama_organisasi = $_POST['nama_organisasi'];
    $jenis           = $_POST['jenis'];
    $deskripsi       = $_POST['deskripsi'];
    $logo_final      = null;

    // Proses Upload Logo
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === 0) {
        $logo_name  = $_FILES['logo']['name'];
        $logo_tmp   = $_FILES['logo']['tmp_name'];
        $logo_size  = $_FILES['logo']['size'];
        $ekstensi_file = strtolower(pathinfo($logo_name, PATHINFO_EXTENSION));

        if (in_array($ekstensi_file, ['jpg', 'jpeg', 'png'])) {
            if ($logo_size < 2000000) { // Maks 2MB
                $logo_final = uniqid() . '.' . $ekstensi_file;
                // Pastikan folder uploads ada di root atau sesuai struktur
                $target_dir = '../../uploads/'; 
                if (!is_dir($target_dir)) { mkdir($target_dir, 0777, true); }
                move_uploaded_file($logo_tmp, $target_dir . $logo_final);
            } else {
                $pesan = "Gagal! Ukuran logo terlalu besar (Maks. 2MB)."; $tipe_pesan = "error";
            }
        } else {
            $pesan = "Gagal! Format logo harus JPG, JPEG, atau PNG."; $tipe_pesan = "error";
        }
    }

    if (empty($pesan)) {
        try {
            $sql = "INSERT INTO organisasi (nama_organisasi, jenis, deskripsi, logo) VALUES (:nama, :jenis, :deskripsi, :logo)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':nama'      => $nama_organisasi,
                ':jenis'     => $jenis, 
                ':deskripsi' => $deskripsi, 
                ':logo'      => $logo_final
            ]);
            $pesan = "Organisasi berhasil ditambahkan!";
            $tipe_pesan = "success";
        } catch (PDOException $e) {
            $pesan = "Gagal menyimpan: " . $e->getMessage();
            $tipe_pesan = "error";
        }
    }
}

// ==========================================
// 2. PROSES HAPUS ORGANISASI
// ==========================================
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['hapus_organisasi'])) {
    $id_organisasi = $_POST['id_organisasi'];
    try {
        $sql = "DELETE FROM organisasi WHERE id_organisasi = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $id_organisasi]);
        $pesan = "Organisasi berhasil dihapus dari sistem."; 
        $tipe_pesan = "success";
    } catch (PDOException $e) {
        $pesan = "Gagal menghapus data: " . $e->getMessage(); 
        $tipe_pesan = "error";
    }
}

// ==========================================
// 3. READ DATA ORGANISASI UNTUK TABEL
// ==========================================
$data_organisasi = [];
try {
    $data_organisasi = $pdo->query("SELECT * FROM organisasi ORDER BY id_organisasi DESC")->fetchAll() ?: [];
} catch (PDOException $e) {
    $pesan = "Error SQL: " . $e->getMessage();
    $tipe_pesan = "error";
}

// Include Header (Ini otomatis membuka tag <main class="main-container">)
include '../../include/header.php';
?>

<!-- ================= CSS TAMBAHAN UNTUK HALAMAN INI ================= -->
<style>
    .page-title { margin-bottom: 20px; font-size: 24px; color: #1F3D68; font-family: 'Montserrat', sans-serif; }
    .alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: bold; }
    .alert-success { background-color: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
    .alert-error { background-color: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
    
    .card { background: #fff; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); padding: 25px; margin-bottom: 30px; border: 1px solid #eee; }
    .card-header { margin-bottom: 20px; border-bottom: 2px solid #f3f4f6; padding-bottom: 10px; }
    .card-header h3 { font-size: 18px; color: #1F3D68; display: flex; align-items: center; gap: 8px; }
    
    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; font-weight: 600; margin-bottom: 8px; font-size: 14px; color: #4b5563; }
    .form-control { width: 100%; padding: 10px 15px; border: 1px solid #d1d5db; border-radius: 8px; outline: none; font-family: 'Inter', sans-serif; }
    .form-control:focus { border-color: #F59E0B; box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.1); }
    
    .btn { padding: 10px 20px; border: none; border-radius: 8px; cursor: pointer; font-weight: bold; transition: 0.3s; }
    .btn-primary { background: #1F3D68; color: #fff; }
    .btn-primary:hover { background: #162c4a; }
    .btn-danger { background: #ef4444; color: #fff; font-size: 12px; padding: 6px 12px; }
    .btn-danger:hover { background: #dc2626; }
    
    .data-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    .data-table th, .data-table td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #eee; font-size: 14px; }
    .data-table th { background: #f9fafb; color: #6b7280; text-transform: uppercase; font-size: 12px; }
    .badge { padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; background: #e0e7ff; color: #4338ca; }
    .logo-preview { width: 45px; height: 45px; object-fit: cover; border-radius: 50%; border: 1px solid #ccc; }
</style>

<!-- ================= KONTEN HALAMAN ================= -->
<div style="padding: 20px;">
    <h1 class="page-title"><i class="fa-solid fa-sitemap" style="color: #F59E0B;"></i> Manajemen Organisasi</h1>

    <!-- Notifikasi -->
    <?php if ($pesan): ?>
        <div class="alert <?= $tipe_pesan == 'success' ? 'alert-success' : 'alert-error' ?>">
            <i class="fa-solid <?= $tipe_pesan == 'success' ? 'fa-circle-check' : 'fa-circle-exclamation' ?>"></i> <?= $pesan ?>
        </div>
    <?php endif; ?>

    <!-- Form Tambah Organisasi -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fa-solid fa-plus-circle"></i> Tambah Organisasi Baru</h3>
        </div>
        <form action="" method="POST" enctype="multipart/form-data">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label>Nama Organisasi</label>
                    <input type="text" name="nama_organisasi" class="form-control" required placeholder="Masukkan nama organisasi...">
                </div>
                <div class="form-group">
                    <label>Jenis Organisasi</label>
                    <select name="jenis" class="form-control" required>
                        <option value="">-- Pilih Jenis --</option>
                        <option value="BEM">BEM</option>
                        <option value="UKM">UKM</option>
                        <option value="SC">SC</option>
                        <option value="Himpunan">Himpunan</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label>Logo Organisasi (Maks 2MB, JPG/PNG)</label>
                <input type="file" name="logo" class="form-control" accept="image/png, image/jpeg, image/jpg" required>
            </div>
            
            <div class="form-group">
                <label>Deskripsi Singkat</label>
                <textarea name="deskripsi" class="form-control" rows="3" required placeholder="Jelaskan secara singkat tentang organisasi ini..."></textarea>
            </div>
            
            <button type="submit" name="tambah_organisasi" class="btn btn-primary w-100" style="width: 100%;"><i class="fa-solid fa-save"></i> Simpan Organisasi</button>
        </form>
    </div>

    <!-- Tabel Daftar Organisasi -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fa-solid fa-list"></i> Daftar Organisasi Terdaftar</h3>
        </div>
        <div style="overflow-x: auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="text-align: center;">Logo</th>
                        <th>Nama Organisasi</th>
                        <th>Jenis</th>
                        <th>Deskripsi</th>
                        <th style="text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data_organisasi)): ?>
                        <tr>
                            <td colspan="5" style="text-align: center; color: #9ca3af; padding: 20px;">Belum ada data organisasi yang terdaftar.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($data_organisasi as $org): ?>
                        <tr>
                            <td style="text-align: center;">
                                <?php if(!empty($org['logo']) && file_exists('../../uploads/' . $org['logo'])): ?>
                                    <img src="../../uploads/<?= htmlspecialchars($org['logo']) ?>" alt="Logo" class="logo-preview">
                                <?php else: ?>
                                    <div class="logo-preview" style="display: flex; align-items: center; justify-content: center; background: #eee; margin: auto;">
                                        <i class="fa-solid fa-image" style="color: #aaa;"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td style="font-weight: bold; color: #1F3D68;">
                                <?= htmlspecialchars($org['nama_organisasi']) ?>
                            </td>
                            <td>
                                <span class="badge"><?= htmlspecialchars($org['jenis'] ?? 'Umum') ?></span>
                            </td>
                            <td style="max-width: 250px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                <?= htmlspecialchars($org['deskripsi'] ?? '-') ?>
                            </td>
                            <td style="text-align: center;">
                                <form action="" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus organisasi <?= htmlspecialchars($org['nama_organisasi']) ?>? Semua data pengurus atau kegiatan terkait mungkin akan terdampak.');">
                                    <input type="hidden" name="id_organisasi" value="<?= $org['id_organisasi'] ?>">
                                    <button type="submit" name="hapus_organisasi" class="btn btn-danger">
                                        <i class="fa-solid fa-trash-can"></i> Hapus
                                    </button>
                                </form>
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
// Include Footer (Ini akan menutup tag <main> dan memuat file js)
include '../../include/footer.php'; 
?>
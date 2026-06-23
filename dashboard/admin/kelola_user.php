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

// Menentukan tab aktif (default: mahasiswa)
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'mahasiswa';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// ==========================================
// 1. PROSES HAPUS DATA USER
// ==========================================
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['hapus_user'])) {
    $id_target = $_POST['id_target'];
    $jenis_user = $_POST['jenis_user']; // 'mahasiswa' atau 'pengurus'
    
    try {
        if ($jenis_user === 'mahasiswa') {
            $stmt = $pdo->prepare("DELETE FROM tbmahasiswa WHERE id_mahasiswa = ?");
            $stmt->execute([$id_target]);
        } else {
            $stmt = $pdo->prepare("DELETE FROM pengurus_organisasi WHERE id_pengurus = ?");
            $stmt->execute([$id_target]);
        }
        $pesan = "Akun berhasil dihapus secara permanen.";
        $tipe_pesan = "success";
        // Redirect agar URL bersih
        header("Location: kelola_user.php?tab=" . $tab . "&msg=del_success");
        exit;
    } catch (PDOException $e) {
        $pesan = "Gagal menghapus data: " . $e->getMessage();
        $tipe_pesan = "error";
    }
}

// ==========================================
// 2. PROSES TAMBAH MAHASISWA
// ==========================================
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['tambah_mahasiswa'])) {
    $nim      = trim($_POST['nim']);
    $nama     = trim($_POST['nama']);
    $prodi    = trim($_POST['prodi']);
    $email    = trim($_POST['email']);
    $no_hp    = trim($_POST['no_hp']); 
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    try {
        // Cek duplikasi NIM atau Email
        $cek = $pdo->prepare("SELECT * FROM tbmahasiswa WHERE nim = ? OR email = ?");
        $cek->execute([$nim, $email]);
        if ($cek->rowCount() > 0) {
            $pesan = "Gagal! NIM atau Email mahasiswa tersebut sudah terdaftar.";
            $tipe_pesan = "error";
        } else {
            $stmt = $pdo->prepare("INSERT INTO tbmahasiswa (nim, nama, prodi, email, kontak, password) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$nim, $nama, $prodi, $email, $no_hp, $password]);
            header("Location: kelola_user.php?tab=mahasiswa&msg=add_success");
            exit;
        }
    } catch (PDOException $e) {
        // Fallback jika nama tabel database menggunakan 'mahasiswa' dan kolom 'kontak'
        if (strpos($e->getMessage(), "tbmahasiswa") !== false) {
            try {
                $stmt = $pdo->prepare("INSERT INTO mahasiswa (nim, nama, prodi, email, kontak, password) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$nim, $nama, $prodi, $email, $no_hp, $password]);
                header("Location: kelola_user.php?tab=mahasiswa&msg=add_success");
                exit;
            } catch (PDOException $ex) {
                $pesan = "Gagal menambah mahasiswa: " . $ex->getMessage();
                $tipe_pesan = "error";
            }
        } else {
            $pesan = "Gagal menambah mahasiswa: " . $e->getMessage();
            $tipe_pesan = "error";
        }
    }
}

// ==========================================
// 3. PROSES TAMBAH PENGURUS
// ==========================================
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['tambah_pengurus'])) {
    $id_organisasi = $_POST['id_organisasi'];
    $nama_pengurus = trim($_POST['nama_pengurus']);
    $jabatan       = trim($_POST['jabatan']);
    $no_hp         = trim($_POST['no_hp']);
    $password      = password_hash($_POST['password'], PASSWORD_DEFAULT);

    try {
        $cek = $pdo->prepare("SELECT * FROM pengurus_organisasi WHERE nama_pengurus = ? AND id_organisasi = ?");
        $cek->execute([$nama_pengurus, $id_organisasi]);
        
        if ($cek->rowCount() > 0) {
            $pesan = "Gagal! Pengurus dengan nama ini sudah terdaftar di organisasi tersebut.";
            $tipe_pesan = "error";
        } else {
            $sql = "INSERT INTO pengurus_organisasi (id_organisasi, nama_pengurus, jabatan, no_hp, password, status_verifikasi) 
                    VALUES (?, ?, ?, ?, ?, 'Belum')";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id_organisasi, $nama_pengurus, $jabatan, $no_hp, $password]);
            header("Location: kelola_user.php?tab=pengurus&msg=add_success");
            exit;
        }
    } catch (PDOException $e) {
        $pesan = "Gagal menambah pengurus: " . $e->getMessage();
        $tipe_pesan = "error";
    }
}

// Tangkap Pesan Redirect
if (isset($_GET['msg'])) {
    if ($_GET['msg'] == 'del_success') { $pesan = "Akun berhasil dihapus permanen."; $tipe_pesan = "success"; }
    if ($_GET['msg'] == 'add_success') { $pesan = "Akun pengguna baru berhasil didaftarkan!"; $tipe_pesan = "success"; }
}

// ==========================================
// 4. READ DATA ORGANISASI (Untuk Form Pengurus)
// ==========================================
$data_organisasi = [];
try {
    $data_organisasi = $pdo->query("SELECT id_organisasi, nama_organisasi FROM organisasi ORDER BY nama_organisasi ASC")->fetchAll();
} catch (PDOException $e) { }

// ==========================================
// 5. READ DATA MAHASISWA & PENGURUS
// ==========================================
$data_mahasiswa = [];
$data_pengurus = [];

try {
    if ($tab === 'mahasiswa') {
        if (!empty($search)) {
            $stmt = $pdo->prepare("SELECT * FROM tbmahasiswa WHERE nama LIKE ? OR nim LIKE ? ORDER BY id_mahasiswa DESC");
            $stmt->execute(["%$search%", "%$search%"]);
            $data_mahasiswa = $stmt->fetchAll();
        } else {
            $data_mahasiswa = $pdo->query("SELECT * FROM tbmahasiswa ORDER BY id_mahasiswa DESC")->fetchAll();
        }
    } else if ($tab === 'pengurus') {
        if (!empty($search)) {
            $stmt = $pdo->prepare("
                SELECT p.*, o.nama_organisasi 
                FROM pengurus_organisasi p 
                LEFT JOIN organisasi o ON p.id_organisasi = o.id_organisasi 
                WHERE p.nama_pengurus LIKE ? OR p.id_akses LIKE ? 
                ORDER BY p.id_pengurus DESC
            ");
            $stmt->execute(["%$search%", "%$search%"]);
            $data_pengurus = $stmt->fetchAll();
        } else {
            $data_pengurus = $pdo->query("
                SELECT p.*, o.nama_organisasi 
                FROM pengurus_organisasi p 
                LEFT JOIN organisasi o ON p.id_organisasi = o.id_organisasi 
                ORDER BY p.id_pengurus DESC
            ")->fetchAll();
        }
    }
} catch (PDOException $e) {
    if (strpos($e->getMessage(), "tbmahasiswa' doesn't exist") !== false) {
        if (!empty($search)) {
            $stmt = $pdo->prepare("SELECT * FROM mahasiswa WHERE nama LIKE ? OR nim LIKE ? ORDER BY id_mahasiswa DESC");
            $stmt->execute(["%$search%", "%$search%"]);
            $data_mahasiswa = $stmt->fetchAll();
        } else {
            $data_mahasiswa = $pdo->query("SELECT * FROM mahasiswa ORDER BY id_mahasiswa DESC")->fetchAll();
        }
    } else {
        $pesan = "Error SQL: " . $e->getMessage();
        $tipe_pesan = "error";
    }
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
    .card-header { margin-bottom: 20px; border-bottom: 2px solid #f3f4f6; padding-bottom: 15px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; }
    
    /* GAYA TAB */
    .tab-container { display: flex; gap: 10px; }
    .tab-item { padding: 10px 20px; text-decoration: none; color: #4b5563; font-weight: 600; font-size: 14px; border-radius: 8px; transition: 0.2s; border: 1px solid #e5e7eb; background: #f9fafb; }
    .tab-item:hover { background-color: #e5e7eb; color: #1F3D68; }
    .tab-item.active { background-color: #F59E0B; color: white; border-color: #F59E0B; box-shadow: 0 4px 6px rgba(245, 158, 11, 0.2); }

    /* TOOLBAR KANAN */
    .toolbar-right { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
    .search-box { display: flex; gap: 10px; }
    .search-input { padding: 10px 15px; border: 1px solid #d1d5db; border-radius: 8px; outline: none; width: 220px; font-family: 'Inter', sans-serif; }
    .search-input:focus { border-color: #F59E0B; box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.1); }
    .btn-search { background: #1F3D68; color: white; border: none; padding: 10px 15px; border-radius: 8px; cursor: pointer; font-weight: bold; transition: 0.3s; }
    .btn-search:hover { background: #162c4a; }
    
    .btn-add { background: #10b981; color: white; border: none; padding: 10px 15px; border-radius: 8px; cursor: pointer; font-weight: bold; transition: 0.3s; display: inline-flex; align-items: center; gap: 6px; }
    .btn-add:hover { background: #059669; }

    .data-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    .data-table th, .data-table td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #eee; font-size: 14px; }
    .data-table th { background: #f9fafb; color: #6b7280; text-transform: uppercase; font-size: 12px; }
    
    .badge { padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; background: #f3f4f6; color: #374151; }
    .badge-id { background: #e0e7ff; color: #4338ca; font-family: monospace; border: 1px solid #c7d2fe; }
    
    .btn-danger { background: #ef4444; color: #fff; padding: 6px 12px; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; font-size: 12px; }
    .btn-danger:hover { background: #dc2626; }

    /* STYLING MODAL (POP-UP) */
    .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); display: flex; justify-content: center; align-items: center; z-index: 1000; opacity: 0; visibility: hidden; transition: 0.3s ease; }
    .modal-overlay.active { opacity: 1; visibility: visible; }
    .modal-content { background: white; width: 90%; max-width: 500px; border-radius: 12px; overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.2); transform: translateY(-20px); transition: 0.3s ease; max-height: 90vh; overflow-y: auto; }
    .modal-overlay.active .modal-content { transform: translateY(0); }
    .modal-header { background: #f9fafb; padding: 15px 20px; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 10; }
    .modal-header h3 { margin: 0; color: #1F3D68; font-size: 18px; display: flex; align-items: center; gap: 8px; }
    .btn-close { background: none; border: none; font-size: 20px; cursor: pointer; color: #6b7280; transition: 0.2s; }
    .btn-close:hover { color: #ef4444; }
    .modal-body { padding: 20px; }
    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; margin-bottom: 5px; font-weight: 600; color: #374151; font-size: 14px; }
    .form-control { width: 100%; padding: 10px 15px; border: 1px solid #d1d5db; border-radius: 8px; font-family: 'Inter', sans-serif; outline: none; transition: 0.2s; box-sizing: border-box; }
    .form-control:focus { border-color: #F59E0B; box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.1); }
    .btn-submit { background: #1F3D68; color: #fff; padding: 10px 20px; border-radius: 8px; font-size: 14px; width: 100%; cursor: pointer; border: none; font-weight: bold; margin-top: 10px;}
    .btn-submit:hover { background: #162c4a; }
</style>

<div style="padding: 20px;">
    <h1 class="page-title"><i class="fa-solid fa-users" style="color: #F59E0B;"></i> Kelola Akun Pengguna</h1>

    <?php if ($pesan): ?>
        <div class="alert <?= $tipe_pesan == 'success' ? 'alert-success' : 'alert-error' ?>">
            <i class="fa-solid <?= $tipe_pesan == 'success' ? 'fa-circle-check' : 'fa-circle-exclamation' ?>"></i> <?= $pesan ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <div class="tab-container">
                <a href="?tab=mahasiswa" class="tab-item <?= $tab == 'mahasiswa' ? 'active' : '' ?>">
                    <i class="fa-solid fa-user-graduate"></i> Akun Mahasiswa
                </a>
                <a href="?tab=pengurus" class="tab-item <?= $tab == 'pengurus' ? 'active' : '' ?>">
                    <i class="fa-solid fa-user-tie"></i> Akun Pengurus Organisasi
                </a>
            </div>
            
            <div class="toolbar-right">
                <form action="" method="GET" class="search-box">
                    <input type="hidden" name="tab" value="<?= $tab ?>">
                    <input type="text" name="search" class="search-input" placeholder="Cari nama atau NIM/ID..." value="<?= htmlspecialchars($search) ?>">
                    <button type="submit" class="btn-search"><i class="fa-solid fa-search"></i></button>
                </form>

                <?php if ($tab === 'mahasiswa'): ?>
                    <button type="button" class="btn-add" onclick="bukaModal('modalTambahMahasiswa')">
                        <i class="fa-solid fa-plus"></i> Tambah Mahasiswa
                    </button>
                <?php elseif ($tab === 'pengurus'): ?>
                    <button type="button" class="btn-add" onclick="bukaModal('modalTambahPengurus')">
                        <i class="fa-solid fa-plus"></i> Tambah Pengurus
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <div style="overflow-x: auto;">
            <table class="data-table">
                <?php if ($tab === 'mahasiswa'): ?>
                    <thead>
                        <tr>
                            <th>NIM</th>
                            <th>Nama Mahasiswa</th>
                            <th>Program Studi</th>
                            <th>Email & Kontak</th>
                            <th style="text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($data_mahasiswa)): ?>
                            <tr><td colspan="5" style="text-align: center; color: #9ca3af; padding: 20px;">Tidak ada data mahasiswa ditemukan.</td></tr>
                        <?php else: ?>
                            <?php foreach($data_mahasiswa as $mhs): ?>
                            <tr>
                                <td style="font-weight: bold;"><?= htmlspecialchars($mhs['nim']) ?></td>
                                <td style="color: #1F3D68; font-weight: 600;"><?= htmlspecialchars($mhs['nama'] ?? $mhs['nama_mahasiswa'] ?? '-') ?></td>
                                <td><span class="badge"><?= htmlspecialchars($mhs['prodi'] ?? '-') ?></span></td>
                                <td>
                                    <div style="font-size: 13px;"><i class="fa-solid fa-envelope" style="color:#9ca3af;"></i> <?= htmlspecialchars($mhs['email'] ?? '-') ?></div>
                                    <div style="font-size: 13px; margin-top: 4px;"><i class="fa-solid fa-phone" style="color:#9ca3af;"></i> <?= htmlspecialchars($mhs['kontak'] ?? $mhs['no_hp'] ?? '-') ?></div>
                                </td>
                                <td style="text-align: center;">
                                    <form action="" method="POST" onsubmit="return confirm('Hapus akun Mahasiswa <?= htmlspecialchars($mhs['nama'] ?? $mhs['nama_mahasiswa'] ?? '') ?>?');">
                                        <input type="hidden" name="id_target" value="<?= $mhs['id_mahasiswa'] ?>">
                                        <input type="hidden" name="jenis_user" value="mahasiswa">
                                        <button type="submit" name="hapus_user" class="btn-danger"><i class="fa-solid fa-trash"></i> Hapus</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>

                <?php elseif ($tab === 'pengurus'): ?>
                    <thead>
                        <tr>
                            <th>ID Akses</th>
                            <th>Nama Pengurus</th>
                            <th>Organisasi & Jabatan</th>
                            <th>Kontak WA</th>
                            <th style="text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($data_pengurus)): ?>
                            <tr><td colspan="5" style="text-align: center; color: #9ca3af; padding: 20px;">Tidak ada data pengurus ditemukan.</td></tr>
                        <?php else: ?>
                            <?php foreach($data_pengurus as $pg): ?>
                            <tr>
                                <td>
                                    <?php if(!empty($pg['id_akses'])): ?>
                                        <span class="badge badge-id"><?= htmlspecialchars($pg['id_akses']) ?></span>
                                    <?php else: ?>
                                        <span style="color: #ef4444; font-size: 12px;">Belum Verifikasi</span>
                                    <?php endif; ?>
                                </td>
                                <td style="color: #1F3D68; font-weight: 600;"><?= htmlspecialchars($pg['nama_pengurus']) ?></td>
                                <td>
                                    <div><span class="badge" style="background:#fef3c7; color:#d97706;"><?= htmlspecialchars($pg['nama_organisasi'] ?? '-') ?></span></div>
                                    <div style="font-size: 13px; margin-top: 4px; color: #4b5563;"><i class="fa-solid fa-sitemap"></i> <?= htmlspecialchars($pg['jabatan']) ?></div>
                                </td>
                                <td><div style="font-size: 13px;"><i class="fa-solid fa-phone" style="color:#9ca3af;"></i> <?= htmlspecialchars($pg['no_hp'] ?? '-') ?></div></td>
                                <td style="text-align: center;">
                                    <form action="" method="POST" onsubmit="return confirm('Hapus akun Pengurus <?= htmlspecialchars($pg['nama_pengurus']) ?>?');">
                                        <input type="hidden" name="id_target" value="<?= $pg['id_pengurus'] ?>">
                                        <input type="hidden" name="jenis_user" value="pengurus">
                                        <button type="submit" name="hapus_user" class="btn-danger"><i class="fa-solid fa-trash"></i> Hapus</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                <?php endif; ?>
            </table>
        </div>
    </div>
</div>

<div id="modalTambahMahasiswa" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fa-solid fa-user-graduate" style="color: #10b981;"></i> Tambah Akun Mahasiswa</h3>
            <button class="btn-close" onclick="tutupModal('modalTambahMahasiswa')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="modal-body">
            <form action="" method="POST">
                <div class="form-group">
                    <label>NIM Mahasiswa</label>
                    <input type="text" name="nim" class="form-control" placeholder="Masukkan NIM..." required>
                </div>
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="nama" class="form-control" placeholder="Masukkan nama mahasiswa..." required>
                </div>
                <div class="form-group">
                    <label>Program Studi</label>
                    <input type="text" name="prodi" class="form-control" placeholder="Contoh: Sistem Informasi" required>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" placeholder="email@contoh.com" required>
                    </div>
                    <div class="form-group">
                        <label>Nomor HP / WA</label>
                        <input type="number" name="no_hp" class="form-control" placeholder="08xxxxxxxx">
                    </div>
                </div>
                <div class="form-group">
                    <label>Password Login</label>
                    <input type="password" name="password" class="form-control" placeholder="Buat password akun..." required>
                </div>
                <button type="submit" name="tambah_mahasiswa" class="btn-submit">Simpan Mahasiswa</button>
            </form>
        </div>
    </div>
</div>

<style>
    .hidden {
        display: none !important;
    }
</style>

<div id="modalTambahPengurus" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fa-solid fa-user-tie" style="color: #10b981;"></i> Tambah Akun Pengurus</h3>
            <button class="btn-close" onclick="tutupModal('modalTambahPengurus')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="modal-body">
            <form action="" method="POST">
                <div class="form-group">
                    <label>Organisasi</label>
                    <select name="id_organisasi" class="form-control" required>
                        <option value="">-- Pilih Organisasi --</option>
                        <?php foreach($data_organisasi as $org): ?>
                            <option value="<?= $org['id_organisasi'] ?>"><?= htmlspecialchars($org['nama_organisasi']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Nama Pengurus</label>
                    <input type="text" name="nama_pengurus" class="form-control" placeholder="Masukkan nama pengurus..." required>
                </div>
                <div class="form-group">
                    <label>Jabatan di Organisasi</label>
                    <select id="tambah_p_jabatan_select" class="form-control" onchange="toggleJabatanUser('tambah')" style="margin-bottom: 8px;">
                        <option value="Ketua">Ketua</option>
                        <option value="Wakil Ketua">Wakil Ketua</option>
                        <option value="Sekretaris">Sekretaris</option>
                        <option value="Bendahara">Bendahara</option>
                        <option value="Lainnya">Lainnya (Ketik Manual...)</option>
                    </select>
                    
                    <input type="hidden" name="jabatan" id="tambah_p_jabatan_asli" value="Ketua">
                    
                    <input type="text" id="tambah_p_jabatan_custom" class="form-control hidden" placeholder="Ketik nama jabatan lainnya di sini...">
                </div>
                <div class="form-group">
                    <label>Level Pengurus</label>
                    <select name="peran" class="form-control">
                        <option value="pengurus" selected>Pengurus Inti</option>
                        <option value="admin">Pengurus Departemen</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Nomor WhatsApp</label>
                    <input type="number" name="no_hp" class="form-control" placeholder="08xxxxxxxx" required>
                </div>
                <div class="form-group">
                    <label>Password Login</label>
                    <input type="password" name="password" class="form-control" placeholder="Buat password login pengurus..." required>
                </div>
                <button type="submit" name="tambah_pengurus" class="btn-submit">Simpan Pengurus</button>
            </form>
        </div>
    </div>
</div>

<script>
    // Membuka modal sesuai ID
    function bukaModal(modalId) {
        document.getElementById(modalId).classList.add('active');
    }

    // Menutup modal sesuai ID
    function tutupModal(modalId) {
        document.getElementById(modalId).classList.remove('active');
    }

    // Menutup modal jika user mengklik area abu-abu (overlay)
    window.onclick = function(event) {
        if (event.target.classList.contains('modal-overlay')) {
            event.target.classList.remove('active');
        }
    }

    // Pertahankan posisi scroll setelah halaman refresh / submit data
    document.addEventListener("DOMContentLoaded", function() {
        if (sessionStorage.getItem("scrollPositionUser") !== null) {
            window.scrollTo({
                top: sessionStorage.getItem("scrollPositionUser"),
                behavior: "instant"
            });
        }
    });

    window.addEventListener("beforeunload", function() {
        sessionStorage.setItem("scrollPositionUser", window.scrollY);
    });

        // Fungsi untuk menyembunyikan/menampilkan form input ketik manual secara dinamis
    function toggleJabatanUser(mode) {
        const jabatSelect = document.getElementById(`${mode}_p_jabatan_select`);
        const jabatCustom = document.getElementById(`${mode}_p_jabatan_custom`);
        const jabatAsli = document.getElementById(`${mode}_p_jabatan_asli`);

        if (jabatSelect.value === 'Lainnya') {
            // Jika pilih 'Lainnya', hapus class hidden agar form muncul, dan buat jadi required
            jabatCustom.classList.remove('hidden');
            jabatCustom.setAttribute('required', 'required');
            jabatCustom.value = '';
            jabatAsli.value = ''; // Kosongkan dulu sampai user mengetik sendiri
            jabatCustom.focus();
        } else {
            // Jika pilih selain 'Lainnya', sembunyikan kembali form input text
            jabatCustom.classList.add('hidden');
            jabatCustom.removeAttribute('required');
            jabatAsli.value = jabatSelect.value; // Isi value asli dengan opsi yang dipilih
        }
    }

    // Event listener otomatis untuk mendengarkan ketikan user di input custom
    document.addEventListener('input', function(e) {
        if (e.target && e.target.id === 'tambah_p_jabatan_custom') {
            document.getElementById('tambah_p_jabatan_asli').value = e.target.value;
        }
    });
</script>

<?php include '../../include/footer.php'; ?>
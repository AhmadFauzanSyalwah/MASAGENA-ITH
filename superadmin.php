<?php
session_start();
require 'koneksi.php';

// ==========================================
// 1. PENGATURAN KREDENSIAL SUPER ADMIN
// ==========================================
$superadmin_username = "owner";
$superadmin_password = "password123";

if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    unset($_SESSION['is_superadmin']);
    unset($_SESSION['superadmin_nama']);
    header("Location: superadmin.php");
    exit;
}

$login_error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login_superadmin'])) {
    if ($_POST['username'] === $superadmin_username && $_POST['password'] === $superadmin_password) {
        $_SESSION['is_superadmin'] = true;
        $_SESSION['superadmin_nama'] = $superadmin_username; 
        header("Location: superadmin.php?tab=dashboard");
        exit;
    } else {
        $login_error = "Otorisasi Super Admin Gagal! Username/Password salah.";
    }
}

$tab = isset($_GET['tab']) ? $_GET['tab'] : 'dashboard';

// =========================================================================
// 2. FUNGSI GATEWAY WHATSAPP (FONNTE)
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
// 3. PROSES CRUD & KONTROL DATA (POST ACTIONS)
// ==========================================
$pesan = "";
$tipe_pesan = "";

if (isset($_SESSION['flash_pesan'])) {
    $pesan = $_SESSION['flash_pesan'];
    $tipe_pesan = $_SESSION['flash_tipe'];
    unset($_SESSION['flash_pesan']);
    unset($_SESSION['flash_tipe']);
}

if (isset($_SESSION['is_superadmin']) && $_SERVER["REQUEST_METHOD"] == "POST") {
    
    // --- VERIFIKASI ADMIN & GENERATE ID AKSES ---
    if (isset($_POST['verifikasi_admin_sekarang'])) {
        $id_admin_verif = $_POST['id_admin'];
        
        $stmt_cek = $pdo->prepare("SELECT * FROM administrator WHERE id_admin = ?");
        $stmt_cek->execute([$id_admin_verif]);
        $admin_data = $stmt_cek->fetch();

        if ($admin_data) {
            if (empty($admin_data['no_hp'])) {
                $pesan = "Gagal Verifikasi! Nomor HP Admin belum diisi. Edit di menu Kontrol Akun terlebih dahulu.";
                $tipe_pesan = "error";
            } else {
                $id_akses_baru = "ADM-" . date("Y") . "-" . rand(1000, 9999);
                
                $stmt = $pdo->prepare("UPDATE administrator SET id_akses = ?, status_verifikasi = 'Terverifikasi' WHERE id_admin = ?");
                if($stmt->execute([$id_akses_baru, $id_admin_verif])) {
                    $pesan_wa = "🔑 *VERIFIKASI AKUN MASAGENA ITH*\n\nHalo *" . $admin_data['nama_lengkap'] . "*,\nAkun Administrator Anda telah diverifikasi oleh Superadmin.\n\nBerikut adalah ID AKSES login Anda:\n👉 *[" . $id_akses_baru . "]*\n\nJaga kerahasiaan kode ini.";
                    kirimWA($admin_data['no_hp'], $pesan_wa);
                    
                    $pesan = "Administrator berhasil diverifikasi! ID Akses telah dikirim via WA.";
                    $tipe_pesan = "success";
                }
            }
        }
    }

    // --- VERIFIKASI PENGURUS ORGANISASI & GENERATE ID AKSES ---
    if (isset($_POST['verifikasi_pengurus_sekarang'])) {
        $id_pengurus_verif = $_POST['id_pengurus'];
        
        $stmt_cek = $pdo->prepare("SELECT * FROM pengurus_organisasi WHERE id_pengurus = ?");
        $stmt_cek->execute([$id_pengurus_verif]);
        $pengurus_data = $stmt_cek->fetch();

        if ($pengurus_data) {
            if (empty($pengurus_data['no_hp'])) {
                $pesan = "Gagal Verifikasi! Nomor HP Pengurus belum diisi. Edit di menu Kontrol Akun terlebih dahulu.";
                $tipe_pesan = "error";
            } else {
                $id_akses_baru = "PGR-" . date("Y") . "-" . rand(1000, 9999);
                
                $stmt = $pdo->prepare("UPDATE pengurus_organisasi SET id_akses = ?, status_verifikasi = 'Terverifikasi' WHERE id_pengurus = ?");
                if($stmt->execute([$id_akses_baru, $id_pengurus_verif])) {
                    $pesan_wa = "🔑 *VERIFIKASI AKUN PENGURUS ORGANISASI*\n\nHalo *" . $pengurus_data['nama_pengurus'] . "*,\nAkun Pengurus Organisasi Anda telah diverifikasi oleh Superadmin.\n\nBerikut adalah ID AKSES login Anda:\n👉 *[" . $id_akses_baru . "]*\n\nJaga kerahasiaan kode ini.";
                    kirimWA($pengurus_data['no_hp'], $pesan_wa);
                    
                    $pesan = "Pengurus Organisasi berhasil diverifikasi! ID Akses telah dikirim via WA.";
                    $tipe_pesan = "success";
                }
            }
        }
    }

    // --- TAMBAH USER BARU ---
    if (isset($_POST['tambah_user'])) {
        $role = $_POST['role'];
        $password_hash = password_hash($_POST['password'], PASSWORD_DEFAULT); 
        try {
            if ($role === 'mahasiswa') {
                $sql = "INSERT INTO tbmahasiswa (nim, nama, email, password, is_verified) VALUES (:nim, :nama, :email, :password, '1')";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([':nim' => $_POST['nim'], ':nama' => $_POST['nama'], ':email' => $_POST['email'], ':password' => $password_hash]);
                $pesan = "Mahasiswa Baru Berhasil Didaftarkan!";
            } elseif ($role === 'admin') {
                $sql = "INSERT INTO administrator (username, nama_lengkap, password, no_hp, status_verifikasi) VALUES (:username, :nama_lengkap, :password, :no_telp, 'Belum')";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([':username' => $_POST['username_admin'], ':nama_lengkap' => $_POST['nama_admin'], ':password' => $password_hash, ':no_telp' => $_POST['no_telp_admin']]);
                $pesan = "Administrator Baru Berhasil Ditambahkan (Status: Belum Verifikasi)!";
            } elseif ($role === 'pengurus') {
                $sql = "INSERT INTO pengurus_organisasi (id_organisasi, nama_pengurus, jabatan, password, no_hp, status_verifikasi) VALUES (:id_org, :nama, :jabatan, :password, :no_telp, 'Belum')";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([':id_org' => $_POST['id_organisasi'], ':nama' => $_POST['nama_pengurus'], ':jabatan' => $_POST['jabatan'], ':password' => $password_hash, ':no_telp' => $_POST['no_telp_pengurus']]);
                $pesan = "Pengurus Organisasi Baru Berhasil Ditambahkan (Status: Belum Verifikasi)!";
            }
            $tipe_pesan = "success";
        } catch (PDOException $e) {
            $pesan = "Gagal memproses data: " . $e->getMessage();
            $tipe_pesan = "error";
        }
    }

    // --- EDIT USER VIA MODAL ---
    if (isset($_POST['edit_user_baru'])) {
        $role = $_POST['role_edit'];
        $id = $_POST['id_edit'];
        try {
            if ($role === 'mahasiswa') {
                $sql = "UPDATE tbmahasiswa SET nim = :nim, nama = :nama, email = :email WHERE id_mahasiswa = :id";
                $pdo->prepare($sql)->execute([':nim' => $_POST['nim_edit'], ':nama' => $_POST['nama_edit'], ':email' => $_POST['email_edit'], ':id' => $id]);
            } elseif ($role === 'admin') {
                $sql = "UPDATE administrator SET username = :username, nama_lengkap = :nama, no_hp = :no_telp WHERE id_admin = :id";
                $pdo->prepare($sql)->execute([':username' => $_POST['username_edit'], ':nama' => $_POST['nama_edit'], ':no_telp' => $_POST['no_telp_edit'], ':id' => $id]);
            } elseif ($role === 'pengurus') {
                $sql = "UPDATE pengurus_organisasi SET id_organisasi = :id_org, nama_pengurus = :nama, jabatan = :jabatan, no_hp = :no_telp WHERE id_pengurus = :id";
                $pdo->prepare($sql)->execute([':id_org' => $_POST['id_organisasi_edit'], ':nama' => $_POST['nama_edit'], ':jabatan' => $_POST['jabatan_edit'], ':no_telp' => $_POST['no_telp_edit'], ':id' => $id]);
            }
            
            if (!empty($_POST['password_edit'])) {
                $password_hash = password_hash($_POST['password_edit'], PASSWORD_DEFAULT);
                if ($role === 'mahasiswa') $pdo->prepare("UPDATE tbmahasiswa SET password = :pw WHERE id_mahasiswa = :id")->execute([':pw' => $password_hash, ':id' => $id]);
                if ($role === 'admin') $pdo->prepare("UPDATE administrator SET password = :pw WHERE id_admin = :id")->execute([':pw' => $password_hash, ':id' => $id]);
                if ($role === 'pengurus') $pdo->prepare("UPDATE pengurus_organisasi SET password = :pw WHERE id_pengurus = :id")->execute([':pw' => $password_hash, ':id' => $id]);
            }
            
            $pesan = "Data Pengguna Berhasil Diperbarui!"; $tipe_pesan = "success";
        } catch (PDOException $e) {
            $pesan = "Gagal memperbarui data: " . $e->getMessage(); $tipe_pesan = "error";
        }
    }

    // --- TAMBAH ORGANISASI BARU ---
    if (isset($_POST['tambah_organisasi'])) {
        $nama_organisasi = $_POST['nama_organisasi'];
        $deskripsi       = $_POST['deskripsi'];
        $logo_final      = null;

        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === 0) {
            $logo_name  = $_FILES['logo']['name'];
            $logo_tmp   = $_FILES['logo']['tmp_name'];
            $logo_size  = $_FILES['logo']['size'];
            $ekstensi_file = strtolower(pathinfo($logo_name, PATHINFO_EXTENSION));

            if (in_array($ekstensi_file, ['jpg', 'jpeg', 'png'])) {
                if ($logo_size < 2000000) { 
                    $logo_final = uniqid() . '.' . $ekstensi_file;
                    $target_dir = 'uploads/';
                    if (!is_dir($target_dir)) { mkdir($target_dir, 0777, true); }
                    move_uploaded_file($logo_tmp, $target_dir . $logo_final);
                } else {
                    $pesan = "Gagal! Ukuran logo terlalu besar (Maks. 2MB)."; $tipe_pesan = "error";
                }
            } else {
                $pesan = "Gagal! Format logo harus JPG, JPEG, atau PNG."; $tipe_pesan = "error";
            }
        }

        if ($tipe_pesan !== "error") {
            try {
                $sql = "INSERT INTO organisasi (nama_organisasi, deskripsi, logo) VALUES (:nama, :deskripsi, :logo)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([':nama' => $nama_organisasi, ':deskripsi' => $deskripsi, ':logo' => $logo_final]);
                $pesan = "Organisasi Baru Beserta Logo Berhasil Dibuat!"; $tipe_pesan = "success";
            } catch (PDOException $e) {
                $pesan = "Gagal menambah organisasi: " . $e->getMessage(); $tipe_pesan = "error";
            }
        }
    }

    // --- TAMBAH KONTEN KEGIATAN BARU ---
    if (isset($_POST['tambah_konten'])) {
        $judul  = $_POST['judul_kegiatan'];
        $isi    = $_POST['isi_kegiatan'];
        $id_org = $_POST['id_organisasi'];
        $uploaded_files = []; 
        $target_dir = 'uploads/kegiatan/'; 
        
        if (!is_dir($target_dir)) { mkdir($target_dir, 0777, true); }

        if (isset($_FILES['foto_kegiatan']) && !empty($_FILES['foto_kegiatan']['name'][0])) {
            $file_count = count($_FILES['foto_kegiatan']['name']);
            for ($i = 0; $i < $file_count; $i++) {
                $tmp_name   = $_FILES['foto_kegiatan']['tmp_name'][$i];
                $file_name  = $_FILES['foto_kegiatan']['name'][$i];
                $file_size  = $_FILES['foto_kegiatan']['size'][$i];
                $file_error = $_FILES['foto_kegiatan']['error'][$i];

                if ($file_error === 0) {
                    $ekstensi = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                    if (in_array($ekstensi, ['jpg', 'jpeg', 'png']) && $file_size < 3000000) {
                        $new_name = uniqid() . '_keg_' . $i . '.' . $ekstensi;
                        if (move_uploaded_file($tmp_name, $target_dir . $new_name)) {
                            $uploaded_files[] = $new_name;
                        }
                    }
                }
            }
        }
        $foto_string = !empty($uploaded_files) ? implode(',', $uploaded_files) : null;

        try {
            $sql = "INSERT INTO konten_kegiatan (judul_kegiatan, isi_kegiatan, id_organisasi, foto) VALUES (:judul, :isi, :id_org, :foto)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':judul' => $judul, ':isi' => $isi, ':id_org' => $id_org, ':foto' => $foto_string]);
            $pesan = "Konten Kegiatan Beserta Foto Berhasil Dipublikasikan!"; $tipe_pesan = "success";
        } catch (PDOException $e) {
            $pesan = "Gagal menambah konten: " . $e->getMessage(); $tipe_pesan = "error";
        }
    }

    // --- UBAH STATUS PENDAFTARAN ---
    if (isset($_POST['ubah_status_pendaftaran'])) {
        try {
            $sql = "UPDATE pendaftaran SET status_pendaftaran = :status WHERE id_pendaftaran = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':status' => $_POST['status_pendaftaran'], ':id' => $_POST['id_pendaftaran']]);
            $pesan = "Status Pendaftaran Mahasiswa Diperbarui!"; $tipe_pesan = "success";
        } catch (PDOException $e) {
            $pesan = "Gagal mengubah status: " . $e->getMessage(); $tipe_pesan = "error";
        }
    }

    // --- RESPONS ASPIRASI ---
    if (isset($_POST['tanggapi_aspirasi'])) {
        try {
            $sqlStatus = "UPDATE aspirasi SET status = :status WHERE id_aspirasi = :id";
            $stmtStatus = $pdo->prepare($sqlStatus);
            $stmtStatus->execute([':status' => $_POST['status_aspirasi'], ':id' => $_POST['id_aspirasi']]);

            if(!empty($_POST['isi_tanggapan'])) {
                $sqlKom = "INSERT INTO komentar (id_aspirasi, id_user, level_user, isi_komentar) VALUES (:id_asp, :id_user, 'admin', :isi)";
                $stmtKom = $pdo->prepare($sqlKom);
                $stmtKom->execute([':id_asp' => $_POST['id_aspirasi'], ':id_user' => 0, ':isi' => $_POST['isi_tanggapan']]);
            }
            $pesan = "Aspirasi berhasil ditanggapi & status diperbarui!"; $tipe_pesan = "success";
        } catch (PDOException $e) {
            $pesan = "Gagal memperbarui aspirasi: " . $e->getMessage(); $tipe_pesan = "error";
        }
    }

    // --- FITUR HAPUS ENTITAS GLOBAL ---
    if (isset($_POST['hapus_entitas'])) {
        $target = $_POST['target_tabel'];
        $id_kolom = $_POST['id_kolom'];
        $id_nilai = $_POST['id_nilai'];
        
        try {
            if ($target === 'aspirasi') {
                $pdo->prepare("DELETE FROM komentar WHERE id_aspirasi = :id")->execute([':id' => $id_nilai]);
                $pdo->prepare("DELETE FROM tblike WHERE id_aspirasi = :id")->execute([':id' => $id_nilai]);
            } elseif ($target === 'konten_kegiatan') {
                $pdo->prepare("DELETE FROM pendaftaran WHERE id_konten = :id")->execute([':id' => $id_nilai]);
            }
            
            $sql = "DELETE FROM $target WHERE $id_kolom = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':id' => $id_nilai]);
            
            $pesan = "Data berhasil dihapus dari sistem."; $tipe_pesan = "success";
        } catch (PDOException $e) {
            $pesan = "Gagal menghapus data: Data ini berelasi dengan tabel lain!"; $tipe_pesan = "error";
        }
    }
}

// ==========================================
// 4. READ DATA MASTER UNTUK TABEL
// ==========================================
$data_mahasiswa = []; $data_admin = []; $data_pengurus = []; $data_aspirasi = []; $data_organisasi = [];
$data_konten = []; $data_pendaftaran = []; $data_komentar = []; $data_like = [];
$counts = [];

try {
    if (isset($_SESSION['is_superadmin'])) {
        $counts['mahasiswa'] = $pdo->query("SELECT COUNT(*) FROM tbmahasiswa")->fetchColumn() ?: 0;
        $counts['admin'] = $pdo->query("SELECT COUNT(*) FROM administrator")->fetchColumn() ?: 0;
        $counts['pengurus'] = $pdo->query("SELECT COUNT(*) FROM pengurus_organisasi")->fetchColumn() ?: 0;
        $counts['aspirasi'] = $pdo->query("SELECT COUNT(*) FROM aspirasi")->fetchColumn() ?: 0;
        $counts['organisasi'] = $pdo->query("SELECT COUNT(*) FROM organisasi")->fetchColumn() ?: 0;
        $counts['konten'] = $pdo->query("SELECT COUNT(*) FROM konten_kegiatan")->fetchColumn() ?: 0;
        $counts['pendaftaran'] = $pdo->query("SELECT COUNT(*) FROM pendaftaran")->fetchColumn() ?: 0;
        $counts['interaksi'] = ($pdo->query("SELECT COUNT(*) FROM komentar")->fetchColumn() ?: 0) + ($pdo->query("SELECT COUNT(*) FROM tblike")->fetchColumn() ?: 0);

        $data_organisasi = $pdo->query("SELECT * FROM organisasi ORDER BY id_organisasi DESC")->fetchAll() ?: [];

        if ($tab === 'kontrol_akun' || $tab === 'verifikasi_admin' || $tab === 'verifikasi_pengurus') {
            $data_admin = $pdo->query("SELECT * FROM administrator ORDER BY id_admin DESC")->fetchAll() ?: [];
            $data_pengurus = $pdo->query("SELECT po.*, o.nama_organisasi FROM pengurus_organisasi po LEFT JOIN organisasi o ON po.id_organisasi = o.id_organisasi ORDER BY po.id_pengurus DESC")->fetchAll() ?: [];
        }

        if ($tab === 'kontrol_akun') {
            $data_mahasiswa = $pdo->query("SELECT * FROM tbmahasiswa ORDER BY id_mahasiswa DESC")->fetchAll() ?: [];
        } elseif ($tab === 'aspirasi') {
            $queryAspirasi = "SELECT a.*, m.nama as nama_mahasiswa, (SELECT isi_komentar FROM komentar k WHERE k.id_aspirasi = a.id_aspirasi AND k.level_user = 'admin' ORDER BY k.id_komentar DESC LIMIT 1) as tanggapan_admin FROM aspirasi a LEFT JOIN tbmahasiswa m ON a.id_mahasiswa = m.id_mahasiswa ORDER BY a.id_aspirasi DESC";
            $data_aspirasi = $pdo->query($queryAspirasi)->fetchAll() ?: [];
        } elseif ($tab === 'konten') {
            $data_konten = $pdo->query("SELECT k.*, o.nama_organisasi FROM konten_kegiatan k LEFT JOIN organisasi o ON k.id_organisasi = o.id_organisasi ORDER BY k.id_konten DESC")->fetchAll() ?: [];
        } elseif ($tab === 'pendaftaran') {
            $data_pendaftaran = $pdo->query("SELECT p.*, m.nama as nama_mhs, m.nim, k.judul_kegiatan FROM pendaftaran p JOIN tbmahasiswa m ON p.id_mahasiswa = m.id_mahasiswa LEFT JOIN konten_kegiatan k ON p.id_konten = k.id_konten ORDER BY p.tanggal_daftar DESC")->fetchAll() ?: [];
        } elseif ($tab === 'interaksi') {
            $data_komentar = $pdo->query("SELECT k.*, a.judul FROM komentar k JOIN aspirasi a ON k.id_aspirasi = a.id_aspirasi ORDER BY k.tanggal DESC")->fetchAll() ?: [];
            $data_like = $pdo->query("SELECT l.*, a.judul, m.nama FROM tblike l JOIN aspirasi a ON l.id_aspirasi = a.id_aspirasi JOIN tbmahasiswa m ON l.id_mahasiswa = m.id_mahasiswa ORDER BY l.id_like DESC")->fetchAll() ?: [];
        }
    }
} catch (PDOException $e) {
    if(!isset($_POST['login_superadmin'])) { 
        $pesan = "Error SQL: " . $e->getMessage();
        $tipe_pesan = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin Control Panel - MASAGENA ITH</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .form-card-custom { border: none; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); overflow: hidden; }
        .form-header-custom { background: linear-gradient(135deg, #1F3D68, #2a528a); color: white; padding: 1.5rem 2rem; }
        .form-label-custom { font-weight: 700; color: #4b5563; margin-bottom: 0.5rem; display: block; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;}
        .form-control-custom { border-radius: 10px; border: 2px solid #e5e7eb; padding: 0.75rem 1rem; width: 100%; transition: all 0.2s; outline: none; font-size: 0.875rem;}
        .form-control-custom:focus { border-color: #F59E0B; background-color: #fffbeb;}
        .upload-area { border: 2px dashed #cbd5e1; border-radius: 12px; background-color: #f8fafc; padding: 1.5rem 1rem; text-align: center; cursor: pointer; }
    </style>
</head>
<body class="bg-[#F4F6F9] min-h-screen flex flex-col">

    <?php if (!isset($_SESSION['is_superadmin'])): ?>
    <div class="flex-grow flex items-center justify-center p-4">
        <div class="bg-white p-8 rounded-2xl shadow-xl max-w-md w-full border-t-8 border-red-600">
            <div class="text-center mb-6">
                <div class="w-16 h-16 bg-red-100 text-red-600 rounded-full flex items-center justify-center mx-auto mb-3 text-2xl"><i class="fa-solid fa-shield-halved"></i></div>
                <h2 class="text-2xl font-extrabold text-gray-800">SUPER ADMIN GATEWAY</h2>
            </div>
            <?php if($login_error): ?>
                <div class="bg-red-50 text-red-600 p-3 rounded-xl mb-4 text-sm font-semibold text-center border border-red-200"><?= $login_error ?></div>
            <?php endif; ?>
            <form action="" method="POST" class="space-y-4">
                <input type="text" name="username" required placeholder="Username Akses" class="w-full p-3 border-2 border-gray-200 rounded-xl focus:outline-none focus:border-red-600 transition">
                <input type="password" name="password" required placeholder="Password Kunci" class="w-full p-3 border-2 border-gray-200 rounded-xl focus:outline-none focus:border-red-600 transition">
                <button type="submit" name="login_superadmin" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3.5 rounded-xl transition shadow-lg">MASUK KONSOL</button>
            </form>
        </div>
    </div>

    <?php else: ?>
    <div class="flex flex-col md:flex-row flex-grow">
        <div class="w-full md:w-64 bg-[#1F3D68] text-white flex flex-col justify-between h-auto md:h-screen sticky top-0 overflow-y-auto">
            <div class="p-6">
                <div class="font-black text-xl tracking-widest text-center border-b border-blue-900 pb-4 mb-6">
                    MASAGENA <span class="text-[#F59E0B]">ITH</span>
                    <span class="block text-[9px] text-gray-400 uppercase tracking-widest font-bold mt-1">SUPERADMIN CONSOLE</span>
                </div>
                <nav class="space-y-2 text-sm">
                    <a href="?tab=dashboard" class="flex items-center space-x-3 px-4 py-3 rounded-xl transition <?= $tab === 'dashboard' ? 'bg-[#F59E0B] text-white font-bold' : 'hover:bg-blue-900 text-gray-300' ?>">
                        <i class="fa-solid fa-chart-pie w-5"></i> <span>Dashboard Utama</span>
                    </a>
                    <a href="?tab=kontrol_akun" class="flex items-center space-x-3 px-4 py-3 rounded-xl transition <?= $tab === 'kontrol_akun' ? 'bg-[#F59E0B] text-white font-bold' : 'hover:bg-blue-900 text-gray-300' ?>">
                        <i class="fa-solid fa-users-gear w-5"></i> <span>Kontrol Semua Akun</span>
                    </a>
                    <a href="?tab=verifikasi_admin" class="flex items-center space-x-3 px-4 py-3 rounded-xl transition <?= $tab === 'verifikasi_admin' ? 'bg-[#F59E0B] text-white font-bold' : 'hover:bg-blue-900 text-gray-300' ?>">
                        <i class="fa-solid fa-user-check w-5"></i> <span>Verifikasi Admin</span>
                    </a>
                    <a href="?tab=verifikasi_pengurus" class="flex items-center space-x-3 px-4 py-3 rounded-xl transition <?= $tab === 'verifikasi_pengurus' ? 'bg-[#F59E0B] text-white font-bold' : 'hover:bg-blue-900 text-gray-300' ?>">
                        <i class="fa-solid fa-user-shield w-5"></i> <span>Verifikasi Pengurus</span>
                    </a>
                    <a href="?tab=organisasi" class="flex items-center space-x-3 px-4 py-3 rounded-xl transition <?= $tab === 'organisasi' ? 'bg-[#F59E0B] text-white font-bold' : 'hover:bg-blue-900 text-gray-300' ?>">
                        <i class="fa-solid fa-sitemap w-5"></i> <span>Manajemen Organisasi</span>
                    </a>
                    <a href="?tab=konten" class="flex items-center space-x-3 px-4 py-3 rounded-xl transition <?= $tab === 'konten' ? 'bg-[#F59E0B] text-white font-bold' : 'hover:bg-blue-900 text-gray-300' ?>">
                        <i class="fa-regular fa-newspaper w-5"></i> <span>Konten Kegiatan</span>
                    </a>
                    <a href="?tab=pendaftaran" class="flex items-center space-x-3 px-4 py-3 rounded-xl transition <?= $tab === 'pendaftaran' ? 'bg-[#F59E0B] text-white font-bold' : 'hover:bg-blue-900 text-gray-300' ?>">
                        <i class="fa-solid fa-clipboard-list w-5"></i> <span>Pendaftaran Mhs</span>
                    </a>
                    <a href="?tab=aspirasi" class="flex items-center space-x-3 px-4 py-3 rounded-xl transition <?= $tab === 'aspirasi' ? 'bg-[#F59E0B] text-white font-bold' : 'hover:bg-blue-900 text-gray-300' ?>">
                        <i class="fa-solid fa-comments w-5"></i> <span>Moderasi Aspirasi</span>
                    </a>
                    <a href="?tab=interaksi" class="flex items-center space-x-3 px-4 py-3 rounded-xl transition <?= $tab === 'interaksi' ? 'bg-[#F59E0B] text-white font-bold' : 'hover:bg-blue-900 text-gray-300' ?>">
                        <i class="fa-solid fa-heart w-5"></i> <span>Komentar & Likes</span>
                    </a>
                </nav>
            </div>
            <div class="p-6 border-t border-blue-900">
                <a href="?action=logout" class="block text-center bg-red-600 hover:bg-red-700 text-white font-bold py-2.5 rounded-xl text-sm transition">
                    <i class="fa-solid fa-right-from-bracket mr-1"></i> Keluar
                </a>
            </div>
        </div>

        <div class="flex-grow p-6 md:p-10 max-w-7xl mx-auto w-full h-screen overflow-y-auto">
            
            <?php if($pesan): ?>
                <div class="<?= $tipe_pesan == 'success' ? 'bg-green-50 text-green-700 border-green-200' : 'bg-red-50 text-red-700 border-red-200' ?> border-2 p-4 rounded-xl mb-6 font-bold flex items-center space-x-3 shadow-sm">
                    <i class="fa-solid <?= $tipe_pesan == 'success' ? 'fa-circle-check' : 'fa-circle-exclamation' ?> text-lg"></i><span><?= $pesan ?></span>
                </div>
            <?php endif; ?>

            <?php if ($tab === 'dashboard'): ?>
                <div class="mb-8"><h1 class="text-3xl font-extrabold text-gray-800">God-Mode Dashboard</h1></div>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                    <div class="bg-white p-5 rounded-2xl shadow border flex items-center justify-between">
                        <div><p class="text-[10px] font-bold text-gray-400">MAHASISWA</p><h3 class="text-2xl font-black mt-1"><?= $counts['mahasiswa'] ?></h3></div>
                        <div class="text-blue-500 text-2xl"><i class="fa-solid fa-graduation-cap"></i></div>
                    </div>
                    <div class="bg-white p-5 rounded-2xl shadow border flex items-center justify-between">
                        <div><p class="text-[10px] font-bold text-gray-400">ORGANISASI</p><h3 class="text-2xl font-black mt-1"><?= $counts['organisasi'] ?></h3></div>
                        <div class="text-amber-500 text-2xl"><i class="fa-solid fa-sitemap"></i></div>
                    </div>
                    <div class="bg-white p-5 rounded-2xl shadow border flex items-center justify-between">
                        <div><p class="text-[10px] font-bold text-gray-400">ADMINISTRATOR</p><h3 class="text-2xl font-black mt-1"><?= $counts['admin'] ?></h3></div>
                        <div class="text-purple-500 text-2xl"><i class="fa-solid fa-user-shield"></i></div>
                    </div>
                    <div class="bg-white p-5 rounded-2xl shadow border flex items-center justify-between">
                        <div><p class="text-[10px] font-bold text-gray-400">PENGURUS ORG</p><h3 class="text-2xl font-black mt-1"><?= $counts['pengurus'] ?></h3></div>
                        <div class="text-teal-500 text-2xl"><i class="fa-solid fa-users"></i></div>
                    </div>
                    <div class="bg-white p-5 rounded-2xl shadow border flex items-center justify-between">
                        <div><p class="text-[10px] font-bold text-gray-400">KONTEN KEGIATAN</p><h3 class="text-2xl font-black mt-1"><?= $counts['konten'] ?></h3></div>
                        <div class="text-emerald-500 text-2xl"><i class="fa-regular fa-newspaper"></i></div>
                    </div>
                    <div class="bg-white p-5 rounded-2xl shadow border flex items-center justify-between">
                        <div><p class="text-[10px] font-bold text-gray-400">ASPIRASI MASUK</p><h3 class="text-2xl font-black mt-1"><?= $counts['aspirasi'] ?></h3></div>
                        <div class="text-red-500 text-2xl"><i class="fa-solid fa-comments"></i></div>
                    </div>
                    <div class="bg-white p-5 rounded-2xl shadow border flex items-center justify-between">
                        <div><p class="text-[10px] font-bold text-gray-400">PENDAFTARAN</p><h3 class="text-2xl font-black mt-1"><?= $counts['pendaftaran'] ?></h3></div>
                        <div class="text-indigo-500 text-2xl"><i class="fa-solid fa-clipboard-list"></i></div>
                    </div>
                    <div class="bg-white p-5 rounded-2xl shadow border flex items-center justify-between">
                        <div><p class="text-[10px] font-bold text-gray-400">INTERAKSI (Komen & Like)</p><h3 class="text-2xl font-black mt-1"><?= $counts['interaksi'] ?></h3></div>
                        <div class="text-pink-500 text-2xl"><i class="fa-solid fa-heart"></i></div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($tab === 'kontrol_akun'): ?>
                <h1 class="text-2xl font-extrabold text-gray-800 mb-6">Kontrol Registrasi & Akun</h1>
                
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 mb-8">
                    <h3 class="text-lg font-bold mb-4 flex items-center"><i class="fa-solid fa-user-plus mr-2 text-[#F59E0B]"></i> Tambah Pengguna Baru</h3>
                    <form action="" method="POST" class="space-y-4">
                        <select id="roleSelector" name="role" onchange="toggleForm()" class="w-full p-3 border rounded-xl font-bold text-gray-700 bg-gray-50 focus:outline-none" required>
                            <option value="mahasiswa">Mahasiswa (Umum)</option>
                            <option value="admin">Administrator Kampus</option>
                            <option value="pengurus">Pengurus Organisasi</option>
                        </select>
                        
                        <div id="formMahasiswa" class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4 bg-gray-50 rounded-xl border border-dashed border-gray-200">
                            <input type="text" name="nim" placeholder="NIM" class="p-2.5 border rounded-lg">
                            <input type="text" name="nama" placeholder="Nama Lengkap" class="p-2.5 border rounded-lg">
                            <input type="email" name="email" placeholder="Email Akun" class="p-2.5 border rounded-lg">
                            <input type="password" name="password" placeholder="Buat Password" class="p-2.5 border rounded-lg">
                        </div>
                        
                        <div id="formAdmin" class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4 bg-gray-50 rounded-xl border border-dashed border-gray-200 hidden">
                            <input type="text" name="username_admin" placeholder="Username Admin" class="p-2.5 border rounded-lg">
                            <input type="text" name="nama_admin" placeholder="Nama Lengkap" class="p-2.5 border rounded-lg">
                            <input type="text" name="no_telp_admin" placeholder="No. HP (contoh: 08123...)" class="p-2.5 border rounded-lg">
                            <input type="password" name="password" placeholder="Buat Password" class="p-2.5 border rounded-lg">
                        </div>
                        
                        <div id="formPengurus" class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4 bg-gray-50 rounded-xl border border-dashed border-gray-200 hidden">
                            <select name="id_organisasi" class="p-2.5 border rounded-lg">
                                <option value="">-- Organisasi --</option>
                                <?php foreach($data_organisasi as $org): ?>
                                <option value="<?= $org['id_organisasi'] ?>"><?= $org['nama_organisasi'] ?></option>
                                <?php endforeach; ?>
                            </select>
                            <input type="text" name="nama_pengurus" placeholder="Nama Lengkap" class="p-2.5 border rounded-lg">
                            <input type="text" name="jabatan" placeholder="Jabatan" class="p-2.5 border rounded-lg">
                            <input type="text" name="no_telp_pengurus" placeholder="No. HP (contoh: 08123...)" class="p-2.5 border rounded-lg">
                            <input type="password" name="password" placeholder="Buat Password" class="p-2.5 border rounded-lg md:col-span-2">
                        </div>

                        <button type="submit" name="tambah_user" class="w-full bg-[#1F3D68] text-white font-bold py-3.5 rounded-xl transition hover:bg-blue-900">DAFTARKAN PENGGUNA</button>
                    </form>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-8">
                    <div class="bg-[#1F3D68] text-white p-4 font-bold flex justify-between items-center"><span>Data Mahasiswa</span></div>
                    <table class="w-full text-left text-sm text-gray-600">
                        <thead class="bg-gray-50 text-xs uppercase border-b">
                            <tr><th class="p-4">NIM</th><th class="p-4">Nama</th><th class="p-4 text-center">Aksi</th></tr>
                        </thead>
                        <tbody class="divide-y">
                            <?php foreach($data_mahasiswa as $mhs): ?>
                            <tr>
                                <td class="p-4 font-bold"><?= $mhs['nim'] ?></td>
                                <td class="p-4"><?= $mhs['nama'] ?></td>
                                <td class="p-4 text-center space-x-2">
                                    <button type="button" onclick="bukaModalEdit('mahasiswa', '<?= $mhs['id_mahasiswa'] ?>', {nim: '<?= $mhs['nim'] ?>', nama: '<?= addslashes($mhs['nama']) ?>', email: '<?= $mhs['email'] ?>'})" class="text-amber-500 hover:text-amber-700 font-bold bg-amber-50 px-3 py-1.5 rounded-lg border border-amber-200"><i class="fa-solid fa-pen-to-square"></i> Edit</button>
                                    <form method="POST" onsubmit="return confirm('Hapus mahasiswa?');" class="inline-block">
                                        <input type="hidden" name="target_tabel" value="tbmahasiswa"><input type="hidden" name="id_kolom" value="id_mahasiswa"><input type="hidden" name="id_nilai" value="<?= $mhs['id_mahasiswa'] ?>">
                                        <button type="submit" name="hapus_entitas" class="text-red-500 hover:text-red-700 font-bold bg-red-50 px-3 py-1.5 rounded-lg border border-red-200"><i class="fa-solid fa-trash-can"></i> Hapus</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-8">
                    <div class="bg-purple-700 text-white p-4 font-bold flex justify-between items-center"><span>Grup Akun Administrator</span></div>
                    <table class="w-full text-left text-sm text-gray-600">
                        <thead class="bg-gray-50 text-xs uppercase border-b">
                            <tr><th class="p-4">Nama Admin</th><th class="p-4">Username</th><th class="p-4">No. WA</th><th class="p-4 text-center">Status</th><th class="p-4 text-center">Aksi</th></tr>
                        </thead>
                        <tbody class="divide-y">
                            <?php foreach($data_admin as $adm): ?>
                            <tr>
                                <td class="p-4 font-bold"><?= htmlspecialchars($adm['nama_lengkap']) ?></td>
                                <td class="p-4 text-xs font-semibold text-gray-500"><?= htmlspecialchars($adm['username']) ?></td>
                                <td class="p-4">
                                    <?php if(!empty($adm['no_hp'])): ?>
                                        <span class="text-green-600 font-bold"><i class="fa-brands fa-whatsapp"></i> +62<?= htmlspecialchars(ltrim($adm['no_hp'], '0')); ?></span>
                                    <?php else: ?>
                                        <span class="text-red-500 text-xs italic">Belum diisi</span>
                                    <?php endif; ?>
                                </td>
                                <td class="p-4 text-center">
                                    <span class="px-2 py-1 rounded text-xs font-bold <?= $adm['status_verifikasi'] === 'Terverifikasi' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>"><?= $adm['status_verifikasi'] ?></span>
                                </td>
                                <td class="p-4 text-center space-x-2 flex justify-center">
                                    <button type="button" onclick="bukaModalEdit('admin', '<?= $adm['id_admin'] ?>', {username: '<?= $adm['username'] ?>', nama: '<?= addslashes($adm['nama_lengkap']) ?>', no_hp: '<?= $adm['no_hp'] ?>'})" class="text-amber-500 hover:text-amber-700 font-bold bg-amber-50 px-3 py-1.5 rounded-lg border border-amber-200"><i class="fa-solid fa-pen-to-square"></i> Edit</button>
                                    <form method="POST" onsubmit="return confirm('Hapus Administrator?');" class="inline-block">
                                        <input type="hidden" name="target_tabel" value="administrator"><input type="hidden" name="id_kolom" value="id_admin"><input type="hidden" name="id_nilai" value="<?= $adm['id_admin'] ?>">
                                        <button type="submit" name="hapus_entitas" class="text-red-500 hover:text-red-700 font-bold bg-red-50 px-3 py-1.5 rounded-lg border border-red-200"><i class="fa-solid fa-trash-can"></i> Hapus</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-8">
                    <div class="bg-teal-600 text-white p-4 font-bold flex justify-between items-center"><span>Grup Akun Pengurus Organisasi</span></div>
                    <table class="w-full text-left text-sm text-gray-600">
                        <thead class="bg-gray-50 text-xs uppercase border-b">
                            <tr><th class="p-4">Nama Pengurus</th><th class="p-4">Organisasi</th><th class="p-4">Jabatan</th><th class="p-4">No. WA</th><th class="p-4 text-center">Status</th><th class="p-4 text-center">Aksi</th></tr>
                        </thead>
                        <tbody class="divide-y">
                            <?php foreach($data_pengurus as $png): ?>
                            <tr>
                                <td class="p-4 font-bold"><?= htmlspecialchars($png['nama_pengurus']) ?></td>
                                <td class="p-4 text-xs font-semibold text-[#1F3D68] uppercase"><?= htmlspecialchars($png['nama_organisasi'] ?? 'N/A') ?></td>
                                <td class="p-4 text-xs text-gray-500"><?= htmlspecialchars($png['jabatan']) ?></td>
                                <td class="p-4">
                                    <?php if(!empty($png['no_hp'])): ?>
                                        <span class="text-green-600 font-bold"><i class="fa-brands fa-whatsapp"></i> +62<?= htmlspecialchars(ltrim($png['no_hp'], '0')); ?></span>
                                    <?php else: ?>
                                        <span class="text-red-500 text-xs italic">Belum diisi</span>
                                    <?php endif; ?>
                                </td>
                                <td class="p-4 text-center">
                                    <span class="px-2 py-1 rounded text-xs font-bold <?= $png['status_verifikasi'] === 'Terverifikasi' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>"><?= $png['status_verifikasi'] ?></span>
                                </td>
                                <td class="p-4 text-center space-x-2 flex justify-center">
                                    <button type="button" onclick="bukaModalEdit('pengurus', '<?= $png['id_pengurus'] ?>', {id_organisasi: '<?= $png['id_organisasi'] ?>', nama: '<?= addslashes($png['nama_pengurus']) ?>', jabatan: '<?= $png['jabatan'] ?>', no_hp: '<?= $png['no_hp'] ?>'})" class="text-amber-500 hover:text-amber-700 font-bold bg-amber-50 px-3 py-1.5 rounded-lg border border-amber-200"><i class="fa-solid fa-pen-to-square"></i> Edit</button>
                                    <form method="POST" onsubmit="return confirm('Hapus Pengurus Organisasi?');" class="inline-block">
                                        <input type="hidden" name="target_tabel" value="pengurus_organisasi"><input type="hidden" name="id_kolom" value="id_pengurus"><input type="hidden" name="id_nilai" value="<?= $png['id_pengurus'] ?>">
                                        <button type="submit" name="hapus_entitas" class="text-red-500 hover:text-red-700 font-bold bg-red-50 px-3 py-1.5 rounded-lg border border-red-200"><i class="fa-solid fa-trash-can"></i> Hapus</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <?php if ($tab === 'verifikasi_admin'): ?>
                <div class="mb-6">
                    <h1 class="text-3xl font-extrabold text-gray-800 flex items-center gap-3"><i class="fa-solid fa-user-check text-[#F59E0B]"></i> Verifikasi Administrator</h1>
                    <p class="text-sm text-gray-500 mt-2">Generate kode ID AKSES login 2 tahap untuk akun Administrator.</p>
                </div>
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <table class="w-full text-left text-sm text-gray-600">
                        <thead class="bg-[#1F3D68] text-white text-xs font-bold uppercase tracking-wider">
                            <tr><th class="p-4">Nama Admin</th><th class="p-4">No. WhatsApp</th><th class="p-4">Status</th><th class="p-4">ID Akses Login</th><th class="p-4 text-center">Aksi Verifikasi</th></tr>
                        </thead>
                        <tbody class="divide-y">
                            <?php foreach($data_admin as $adm): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="p-4 font-bold text-gray-800"><?= htmlspecialchars($adm['nama_lengkap']) ?></td>
                                <td class="p-4">
                                    <?php if(!empty($adm['no_hp'])): ?>
                                        <span class="text-green-600 font-bold">+62<?= ltrim(htmlspecialchars($adm['no_hp']), '0'); ?></span>
                                    <?php else: ?>
                                        <span class="text-red-500 text-xs italic">Belum disetting</span>
                                    <?php endif; ?>
                                </td>
                                <td class="p-4"><span class="font-bold <?= $adm['status_verifikasi'] === 'Terverifikasi' ? 'text-green-500' : 'text-red-500' ?>"><?= $adm['status_verifikasi'] ?></span></td>
                                <td class="p-4 font-mono font-bold text-[#F59E0B]"><?= htmlspecialchars($adm['id_akses'] ?? 'Belum Ada') ?></td>
                                <td class="p-4 text-center">
                                    <?php if($adm['status_verifikasi'] !== 'Terverifikasi'): ?>
                                    <form method="POST" action="">
                                        <input type="hidden" name="id_admin" value="<?= $adm['id_admin'] ?>">
                                        <button type="submit" name="verifikasi_admin_sekarang" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-xs font-bold" onclick="return confirm('Apakah Anda yakin ingin memverifikasi admin ini?');">Verifikasi & Buat ID</button>
                                    </form>
                                    <?php else: ?>
                                    <span class="text-gray-400 text-xs italic font-bold">Selesai Diverifikasi</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <?php if ($tab === 'verifikasi_pengurus'): ?>
                <div class="mb-6">
                    <h1 class="text-3xl font-extrabold text-gray-800 flex items-center gap-3"><i class="fa-solid fa-user-shield text-[#F59E0B]"></i> Verifikasi Pengurus Organisasi</h1>
                    <p class="text-sm text-gray-500 mt-2">Generate kode ID AKSES login 2 tahap untuk akun Pengurus.</p>
                </div>
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <table class="w-full text-left text-sm text-gray-600">
                        <thead class="bg-[#1F3D68] text-white text-xs font-bold uppercase tracking-wider">
                            <tr><th class="p-4">Nama Pengurus</th><th class="p-4">Detail Organisasi</th><th class="p-4">No. WhatsApp</th><th class="p-4">Status</th><th class="p-4">ID Akses Login</th><th class="p-4 text-center">Aksi Verifikasi</th></tr>
                        </thead>
                        <tbody class="divide-y">
                            <?php if(count($data_pengurus) == 0): ?>
                            <tr><td colspan="6" class="p-6 text-center text-gray-400 italic">Belum ada data pengurus organisasi.</td></tr>
                            <?php endif; ?>
                            <?php foreach($data_pengurus as $png): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="p-4 font-bold text-gray-800"><?= htmlspecialchars($png['nama_pengurus']) ?></td>
                                <td class="p-4 text-xs font-semibold text-gray-500 uppercase"><?= htmlspecialchars($png['nama_organisasi']) ?> - <span class="text-indigo-600"><?= htmlspecialchars($png['jabatan']) ?></span></td>
                                <td class="p-4">
                                    <?php if(!empty($png['no_hp'])): ?>
                                        <span class="text-green-600 font-bold">+62<?= ltrim(htmlspecialchars($png['no_hp']), '0'); ?></span>
                                    <?php else: ?>
                                        <span class="text-red-500 text-xs italic">Belum disetting</span>
                                    <?php endif; ?>
                                </td>
                                <td class="p-4"><span class="font-bold <?= $png['status_verifikasi'] === 'Terverifikasi' ? 'text-green-500' : 'text-red-500' ?>"><?= $png['status_verifikasi'] ?></span></td>
                                <td class="p-4 font-mono font-bold text-[#F59E0B]"><?= htmlspecialchars($png['id_akses'] ?? 'Belum Ada') ?></td>
                                <td class="p-4 text-center">
                                    <?php if($png['status_verifikasi'] !== 'Terverifikasi'): ?>
                                    <form method="POST" action="">
                                        <input type="hidden" name="id_pengurus" value="<?= $png['id_pengurus'] ?>">
                                        <button type="submit" name="verifikasi_pengurus_sekarang" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-xs font-bold" onclick="return confirm('Apakah Anda yakin ingin memverifikasi pengurus ini?');">Verifikasi & Buat ID</button>
                                    </form>
                                    <?php else: ?>
                                    <span class="text-gray-400 text-xs italic font-bold">Selesai Diverifikasi</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <?php if ($tab === 'organisasi'): ?>
                <h1 class="text-2xl font-extrabold text-gray-800 mb-6">Manajemen Data Organisasi</h1>
                <div class="form-card-custom bg-white mb-8">
                    <div class="form-header-custom"><h3 class="text-xl font-bold flex items-center"><i class="fa-solid fa-plus-circle mr-2"></i> Tambah Organisasi Baru</h3></div>
                    <form action="" method="POST" enctype="multipart/form-data" class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                            <div><label class="form-label-custom">Nama Organisasi</label><input type="text" name="nama_organisasi" required class="form-control-custom"></div>
                            <div><label class="form-label-custom">Logo Organisasi</label><input type="file" name="logo" required accept="image/*" class="form-control-custom"></div>
                        </div>
                        <div class="mb-4"><label class="form-label-custom">Deskripsi Singkat</label><textarea name="deskripsi" required class="form-control-custom" rows="3"></textarea></div>
                        <button type="submit" name="tambah_organisasi" class="bg-[#1F3D68] hover:bg-blue-900 text-white font-bold py-3 px-6 rounded-xl w-full">Simpan Organisasi</button>
                    </form>
                </div>
            <?php endif; ?>

            <?php if ($tab === 'konten'): ?>
                <h1 class="text-2xl font-extrabold text-gray-800 mb-6">Publikasi Konten Kegiatan</h1>
                <div class="form-card-custom bg-white mb-8">
                    <div class="form-header-custom !bg-emerald-600">
                        <h3 class="text-xl font-bold flex items-center"><i class="fa-solid fa-pen-to-square mr-2"></i> Tulis Kegiatan Baru</h3>
                    </div>
                    <form action="" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="form-label-custom">Penyelenggara</label>
                                <select name="id_organisasi" class="form-control-custom bg-gray-50" required>
                                    <option value="">-- Pilih Organisasi --</option>
                                    <?php foreach($data_organisasi as $org): ?>
                                    <option value="<?= $org['id_organisasi'] ?>"><?= $org['nama_organisasi'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="form-label-custom">Judul Kegiatan</label>
                                <input type="text" name="judul_kegiatan" required class="form-control-custom">
                            </div>
                        </div>
                        <div>
                            <label class="form-label-custom">Isi & Deskripsi Kegiatan</label>
                            <textarea name="isi_kegiatan" required class="form-control-custom" rows="4"></textarea>
                        </div>
                        <div>
                            <label class="form-label-custom">Galeri Foto Kegiatan</label>
                            <div class="upload-area">
                                <input type="file" name="foto_kegiatan[]" multiple class="file-input-btn w-full text-gray-500 text-xs" accept="image/png, image/jpeg, image/jpg">
                            </div>
                        </div>
                        <button type="submit" name="tambah_konten" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 px-6 rounded-xl w-full">Posting Kegiatan Publik</button>
                    </form>
                </div>
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mt-8">
                    <table class="w-full text-left text-sm text-gray-600">
                        <thead class="bg-gray-50 text-xs font-bold text-gray-500 uppercase border-b">
                            <tr><th class="p-4 w-1/4">Judul Kegiatan</th><th class="p-4">Penyelenggara</th><th class="p-4 text-center">Aksi</th></tr>
                        </thead>
                        <tbody class="divide-y">
                            <?php foreach($data_konten as $kon): ?>
                            <tr>
                                <td class="p-4 font-bold text-gray-800"><?= htmlspecialchars($kon['judul_kegiatan']) ?></td>
                                <td class="p-4 text-xs font-bold text-amber-600"><?= htmlspecialchars($kon['nama_organisasi'] ?? 'Sistem Pusat') ?></td>
                                <td class="p-4 text-center">
                                    <form action="" method="POST" onsubmit="return confirm('Hapus konten ini?');">
                                        <input type="hidden" name="target_tabel" value="konten_kegiatan"><input type="hidden" name="id_kolom" value="id_konten"><input type="hidden" name="id_nilai" value="<?= $kon['id_konten'] ?>">
                                        <button type="submit" name="hapus_entitas" class="text-red-500 hover:text-red-700"><i class="fa-solid fa-trash-can"></i></button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <?php if ($tab === 'pendaftaran'): ?>
                <div class="mb-8"><h1 class="text-3xl font-extrabold text-gray-800">Daftar Pendaftaran Mahasiswa</h1></div>
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <table class="w-full text-left text-sm text-gray-600">
                        <thead class="bg-[#1F3D68] text-white text-xs font-bold uppercase">
                            <tr><th class="p-4">Mahasiswa</th><th class="p-4">Kegiatan Diikuti</th><th class="p-4">Tgl Daftar</th><th class="p-4 text-center">Status</th><th class="p-4 text-center">Ubah Status</th></tr>
                        </thead>
                        <tbody class="divide-y">
                            <?php foreach($data_pendaftaran as $pend): ?>
                            <tr>
                                <td class="p-4"><p class="font-bold text-gray-800"><?= htmlspecialchars($pend['nama_mhs']) ?></p><p class="text-xs text-gray-500"><?= htmlspecialchars($pend['nim']) ?></p></td>
                                <td class="p-4 font-semibold text-gray-700"><?= htmlspecialchars($pend['judul_kegiatan']) ?></td>
                                <td class="p-4 text-xs"><?= htmlspecialchars($pend['tanggal_daftar']) ?></td>
                                <td class="p-4 text-center">
                                    <span class="px-2 py-1 rounded text-xs font-bold <?= $pend['status_pendaftaran']=='Diterima'?'bg-green-100 text-green-700':($pend['status_pendaftaran']=='Ditolak'?'bg-red-100 text-red-700':'bg-amber-100 text-amber-700') ?>"><?= htmlspecialchars($pend['status_pendaftaran']) ?></span>
                                </td>
                                <td class="p-4 text-center">
                                    <form action="" method="POST" class="flex justify-center items-center space-x-2">
                                        <input type="hidden" name="id_pendaftaran" value="<?= $pend['id_pendaftaran'] ?>">
                                        <select name="status_pendaftaran" class="p-1 border rounded text-xs bg-gray-50 font-bold">
                                            <option value="Menunggu" <?= $pend['status_pendaftaran']=='Menunggu'?'selected':'' ?>>Menunggu</option>
                                            <option value="Diterima" <?= $pend['status_pendaftaran']=='Diterima'?'selected':'' ?>>Diterima</option>
                                            <option value="Ditolak" <?= $pend['status_pendaftaran']=='Ditolak'?'selected':'' ?>>Ditolak</option>
                                        </select>
                                        <button type="submit" name="ubah_status_pendaftaran" class="bg-[#1F3D68] text-white px-2 py-1 rounded text-xs font-bold"><i class="fa-solid fa-check"></i></button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <?php if ($tab === 'aspirasi'): ?>
                <div class="mb-8"><h1 class="text-3xl font-extrabold text-gray-800">Moderasi Aspirasi Mahasiswa</h1></div>
                <div class="grid grid-cols-1 gap-6">
                    <?php foreach($data_aspirasi as $asp): ?>
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                        <div class="flex justify-between items-start mb-3">
                            <div><span class="text-xs bg-blue-50 text-[#1F3D68] px-3 py-1 rounded-full font-bold uppercase">Kategori: <?= htmlspecialchars($asp['kategori']) ?></span><p class="text-xs text-gray-400 mt-1">Pengirim: <b class="text-gray-700"><?= htmlspecialchars($asp['nama_mahasiswa']??'Anonim') ?></b></p></div>
                            <span class="px-3 py-1 rounded-full text-xs font-black uppercase <?= $asp['status']=='proses'?'bg-amber-100 text-amber-700':'bg-green-100 text-green-700' ?>"><?= $asp['status'] ?></span>
                        </div>
                        <h3 class="font-bold text-gray-800 mb-1"><?= htmlspecialchars($asp['judul']) ?></h3>
                        <div class="bg-slate-50 p-4 rounded-xl text-gray-700 text-sm italic border-l-4 border-amber-500 mb-4">"<?= htmlspecialchars($asp['isi_aspirasi']) ?>"</div>
                        <?php if(!empty($asp['tanggapan_admin'])): ?>
                            <div class="bg-green-50 p-4 rounded-xl text-green-800 text-sm mb-4 border border-green-200"><b class="block text-xs text-green-700 mb-1"><i class="fa-solid fa-reply"></i> Balasan Anda:</b><?= htmlspecialchars($asp['tanggapan_admin']) ?></div>
                        <?php endif; ?>
                        <div class="border-t pt-4 flex flex-col md:flex-row justify-between items-center gap-4">
                            <form action="" method="POST" class="w-full flex items-end gap-3">
                                <input type="hidden" name="id_aspirasi" value="<?= $asp['id_aspirasi'] ?>">
                                <input type="text" name="isi_tanggapan" placeholder="Kirim balasan..." class="w-full p-2.5 text-xs border rounded-lg">
                                <select name="status_aspirasi" class="p-2.5 text-xs border rounded-lg bg-gray-50 font-bold"><option value="proses" <?= $asp['status']=='proses'?'selected':'' ?>>Proses</option><option value="selesai" <?= $asp['status']=='selesai'?'selected':'' ?>>Selesai</option></select>
                                <button type="submit" name="tanggapi_aspirasi" class="bg-emerald-600 text-white font-bold px-4 py-2.5 rounded-lg text-xs"><i class="fa-solid fa-paper-plane"></i></button>
                            </form>
                            <form action="" method="POST" onsubmit="return confirm('Hapus aspirasi ini?');">
                                <input type="hidden" name="target_tabel" value="aspirasi"><input type="hidden" name="id_kolom" value="id_aspirasi"><input type="hidden" name="id_nilai" value="<?= $asp['id_aspirasi'] ?>">
                                <button type="submit" name="hapus_entitas" class="text-red-500 hover:text-red-700 font-bold text-sm bg-red-50 px-3 py-2 rounded-lg"><i class="fa-solid fa-trash-can"></i> Hapus</button>
                            </form>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if ($tab === 'interaksi'): ?>
                <div class="mb-8"><h1 class="text-3xl font-extrabold text-gray-800">Manajemen Komentar & Like</h1></div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="bg-indigo-600 text-white p-4 font-bold">Daftar Komentar Aspirasi</div>
                        <div class="overflow-x-auto h-96">
                            <table class="w-full text-left text-sm text-gray-600">
                                <thead class="bg-gray-50 text-xs font-bold text-gray-500 uppercase sticky top-0 border-b">
                                    <tr><th class="p-3 w-3/4">Isi Komentar</th><th class="p-3 text-center">Aksi</th></tr>
                                </thead>
                                <tbody class="divide-y">
                                    <?php foreach($data_komentar as $kom): ?>
                                    <tr>
                                        <td class="p-3">
                                            <p class="font-bold text-gray-800 text-xs mb-1">Aspirasi: <?= htmlspecialchars($kom['judul']) ?></p>
                                            <p class="italic text-gray-700 bg-gray-50 p-2 rounded">"<?= htmlspecialchars($kom['isi_komentar']) ?>"</p>
                                        </td>
                                        <td class="p-3 text-center">
                                            <form action="" method="POST" onsubmit="return confirm('Hapus komentar?');"><input type="hidden" name="target_tabel" value="komentar"><input type="hidden" name="id_kolom" value="id_komentar"><input type="hidden" name="id_nilai" value="<?= $kom['id_komentar'] ?>"><button type="submit" name="hapus_entitas" class="text-red-500 hover:text-red-700"><i class="fa-solid fa-trash-can"></i></button></form>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="bg-pink-600 text-white p-4 font-bold">Daftar Like Aspirasi</div>
                        <div class="overflow-x-auto h-96">
                            <table class="w-full text-left text-sm text-gray-600">
                                <thead class="bg-gray-50 text-xs font-bold text-gray-500 uppercase sticky top-0 border-b">
                                    <tr><th class="p-3">Data Like</th><th class="p-3 text-center">Aksi</th></tr>
                                </thead>
                                <tbody class="divide-y">
                                    <?php foreach($data_like as $lk): ?>
                                    <tr>
                                        <td class="p-3">
                                            <p class="font-bold text-gray-800"><i class="fa-solid fa-heart text-pink-500"></i> <?= htmlspecialchars($lk['nama']) ?></p>
                                            <p class="text-xs text-gray-500 mt-1">Menyukai: <?= htmlspecialchars($lk['judul'] ?? 'Unknown') ?></p>
                                        </td>
                                        <td class="p-3 text-center">
                                            <form action="" method="POST" onsubmit="return confirm('Hapus like?');"><input type="hidden" name="target_tabel" value="tblike"><input type="hidden" name="id_kolom" value="id_like"><input type="hidden" name="id_nilai" value="<?= $lk['id_like'] ?>"><button type="submit" name="hapus_entitas" class="text-red-500 hover:text-red-700"><i class="fa-solid fa-trash-can"></i></button></form>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </div>

    <div id="modalEdit" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
        <div class="bg-white p-6 rounded-2xl shadow-lg w-full max-w-md border-t-8 border-amber-500">
            <h3 class="text-xl font-bold mb-4 text-gray-800 flex justify-between items-center">
                <span><i class="fa-solid fa-pen-to-square text-amber-500 mr-2"></i> Edit Data Pengguna</span>
                <button onclick="document.getElementById('modalEdit').classList.add('hidden'); document.getElementById('modalEdit').classList.remove('flex');" class="text-gray-400 hover:text-red-500"><i class="fa-solid fa-times"></i></button>
            </h3>
            <form action="" method="POST" class="space-y-4">
                <input type="hidden" name="id_edit" id="id_edit">
                <input type="hidden" name="role_edit" id="role_edit">
                
                <div id="formEditMahasiswa" class="hidden space-y-3">
                    <input type="text" name="nim_edit" id="nim_edit" placeholder="NIM" class="w-full p-3 border rounded-lg focus:border-amber-500 focus:outline-none">
                    <input type="text" name="nama_edit" id="nama_mahasiswa_edit" placeholder="Nama Lengkap" class="w-full p-3 border rounded-lg focus:border-amber-500 focus:outline-none">
                    <input type="email" name="email_edit" id="email_edit" placeholder="Email" class="w-full p-3 border rounded-lg focus:border-amber-500 focus:outline-none">
                </div>

                <div id="formEditAdmin" class="hidden space-y-3">
                    <input type="text" name="username_edit" id="username_edit" placeholder="Username" class="w-full p-3 border rounded-lg focus:border-amber-500 focus:outline-none">
                    <input type="text" name="nama_edit" id="nama_admin_edit" placeholder="Nama Lengkap" class="w-full p-3 border rounded-lg focus:border-amber-500 focus:outline-none">
                    <input type="text" name="no_telp_edit" id="no_telp_admin_edit" placeholder="No. HP" class="w-full p-3 border rounded-lg focus:border-amber-500 focus:outline-none">
                </div>

                <div id="formEditPengurus" class="hidden space-y-3">
                    <select name="id_organisasi_edit" id="id_organisasi_edit" class="w-full p-3 border rounded-lg focus:border-amber-500 focus:outline-none">
                        <?php foreach($data_organisasi as $org): ?>
                        <option value="<?= $org['id_organisasi'] ?>"><?= $org['nama_organisasi'] ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="text" name="nama_edit" id="nama_pengurus_edit" placeholder="Nama Lengkap" class="w-full p-3 border rounded-lg focus:border-amber-500 focus:outline-none">
                    <input type="text" name="jabatan_edit" id="jabatan_edit" placeholder="Jabatan" class="w-full p-3 border rounded-lg focus:border-amber-500 focus:outline-none">
                    <input type="text" name="no_telp_edit" id="no_telp_pengurus_edit" placeholder="No. HP" class="w-full p-3 border rounded-lg focus:border-amber-500 focus:outline-none">
                </div>

                <div class="mt-4 pt-4 border-t border-gray-100">
                    <label class="text-xs font-bold text-gray-500">Ubah Password (Opsional)</label>
                    <input type="password" name="password_edit" placeholder="Kosongkan jika tidak diubah" class="w-full p-3 mt-1 border rounded-lg focus:border-amber-500 focus:outline-none">
                </div>

                <button type="submit" name="edit_user_baru" class="w-full bg-amber-500 hover:bg-amber-600 text-white font-bold py-3 rounded-lg mt-4 transition">Simpan Perubahan</button>
            </form>
        </div>
    </div>

    <script>
        function toggleForm() {
            let role = document.getElementById('roleSelector').value;
            ['formMahasiswa', 'formAdmin', 'formPengurus'].forEach(id => document.getElementById(id).classList.add('hidden'));
            
            // Nonaktifkan semua input dalam subform agar tidak bentrok atau mengirim string kosong
            document.querySelectorAll('#formMahasiswa input, #formAdmin input, #formPengurus input, #formPengurus select').forEach(el => {
                el.removeAttribute('required');
                el.setAttribute('disabled', 'true');
            });

            if (role === 'mahasiswa') {
                document.getElementById('formMahasiswa').classList.remove('hidden');
                document.querySelectorAll('#formMahasiswa input').forEach(el => el.removeAttribute('disabled'));
                document.querySelector('input[name="nim"]').setAttribute('required', 'true');
                document.querySelector('#formMahasiswa input[type="password"]').setAttribute('required', 'true');
            } else if (role === 'admin') {
                document.getElementById('formAdmin').classList.remove('hidden');
                document.querySelectorAll('#formAdmin input').forEach(el => el.removeAttribute('disabled'));
                document.querySelector('input[name="username_admin"]').setAttribute('required', 'true');
                document.querySelector('input[name="no_telp_admin"]').setAttribute('required', 'true');
                document.querySelector('#formAdmin input[type="password"]').setAttribute('required', 'true');
            } else if (role === 'pengurus') {
                document.getElementById('formPengurus').classList.remove('hidden');
                document.querySelectorAll('#formPengurus input, #formPengurus select').forEach(el => el.removeAttribute('disabled'));
                document.querySelector('select[name="id_organisasi"]').setAttribute('required', 'true');
                document.querySelector('input[name="no_telp_pengurus"]').setAttribute('required', 'true');
                document.querySelector('#formPengurus input[type="password"]').setAttribute('required', 'true');
            }
        }
        if(document.getElementById('roleSelector')) window.onload = toggleForm;

        // MANIPULASI DATA POPULATE KE MODAL EDIT
        function bukaModalEdit(role, id, data) {
            document.getElementById('role_edit').value = role;
            document.getElementById('id_edit').value = id;
            
            ['formEditMahasiswa', 'formEditAdmin', 'formEditPengurus'].forEach(id => document.getElementById(id).classList.add('hidden'));

            if (role === 'mahasiswa') {
                document.getElementById('formEditMahasiswa').classList.remove('hidden');
                document.getElementById('nim_edit').value = data.nim;
                document.getElementById('nama_mahasiswa_edit').value = data.nama;
                document.getElementById('email_edit').value = data.email;
            } else if (role === 'admin') {
                document.getElementById('formEditAdmin').classList.remove('hidden');
                document.getElementById('username_edit').value = data.username;
                document.getElementById('nama_admin_edit').value = data.nama;
                document.getElementById('no_telp_admin_edit').value = data.no_hp;
            } else if (role === 'pengurus') {
                document.getElementById('formEditPengurus').classList.remove('hidden');
                document.getElementById('id_organisasi_edit').value = data.id_organisasi;
                document.getElementById('nama_pengurus_edit').value = data.nama;
                document.getElementById('jabatan_edit').value = data.jabatan;
                document.getElementById('no_telp_pengurus_edit').value = data.no_hp;
            }

            let modal = document.getElementById('modalEdit');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
    </script>
    <?php endif; ?>
</body>
</html>
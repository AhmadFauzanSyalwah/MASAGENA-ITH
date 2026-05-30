<?php
session_start();
require 'koneksi.php';

// ==========================================
// 1. CEK SESSION ADMIN DARI login_admin.php
// ==========================================
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: login_admin.php");
    exit;
}

$admin_id = $_SESSION['admin_id'];
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'dashboard';

$alert_message = "";
$alert_type = ""; 

// ==========================================
// 2. FUNGSI GATEWAY WHATSAPP (FONNTE)
// ==========================================
if (!function_exists('kirimWA')) {
    function kirimWA($nomor_hp, $pesan) {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.fonnte.com/send',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
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
}

// ==========================================
// 3. LOGIKA PROSES AKSI (POST/GET)
// ==========================================

if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    unset($_SESSION['is_admin']);
    unset($_SESSION['admin_id']);
    unset($_SESSION['admin_nama']);
    session_destroy();
    header("Location: login_admin.php");
    exit;
}

// --- AKSI UPDATE PROFIL ADMIN (NEW) ---
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_akun'])) {
    $nama_lengkap = $_POST['nama_lengkap'];
    $username = $_POST['username'];
    $no_hp = $_POST['no_hp'];
    $password_baru = $_POST['password'];

    try {
        if (!empty($password_baru)) {
            // Jika password diisi, enkripsi password baru lalu update
            $hashed_password = password_hash($password_baru, PASSWORD_DEFAULT);
            $pdo->prepare("UPDATE administrator SET nama_lengkap = ?, username = ?, no_hp = ?, password = ? WHERE id_admin = ?")
                ->execute([$nama_lengkap, $username, $no_hp, $hashed_password, $admin_id]);
        } else {
            // Jika password kosong, update field lainnya saja
            $pdo->prepare("UPDATE administrator SET nama_lengkap = ?, username = ?, no_hp = ? WHERE id_admin = ?")
                ->execute([$nama_lengkap, $username, $no_hp, $admin_id]);
        }
        
        // Perbarui session nama agar berubah seketika di layar
        $_SESSION['admin_nama'] = $nama_lengkap;
        $alert_message = "Rincian profil Anda berhasil diperbarui.";
        $alert_type = "success";
    } catch (PDOException $e) {
        $alert_message = "Gagal memperbarui profil: " . $e->getMessage();
        $alert_type = "error";
    }
}


// --- AKSI MODERASI ASPIRASI & KOMENTAR ---
if (isset($_GET['action']) && $_GET['action'] === 'hapus_aspirasi' && isset($_GET['id'])) {
    $id_aspirasi = (int)$_GET['id'];
    try {
        $pdo->prepare("DELETE FROM komentar WHERE id_aspirasi = ?")->execute([$id_aspirasi]);
        $pdo->prepare("DELETE FROM tblike WHERE id_aspirasi = ?")->execute([$id_aspirasi]);
        $pdo->prepare("DELETE FROM aspirasi WHERE id_aspirasi = ?")->execute([$id_aspirasi]);
        
        $alert_message = "Aspirasi berhasil dimoderasi (dihapus).";
        $alert_type = "success";
    } catch (PDOException $e) {
        $alert_message = "Gagal memoderasi aspirasi: " . $e->getMessage();
        $alert_type = "error";
    }
}

if (isset($_GET['action']) && $_GET['action'] === 'hapus_komentar' && isset($_GET['id'])) {
    $id_komentar = (int)$_GET['id'];
    try {
        $pdo->prepare("DELETE FROM komentar WHERE id_komentar = ?")->execute([$id_komentar]);
        $alert_message = "Komentar berhasil dihapus.";
        $alert_type = "success";
    } catch (PDOException $e) {
        $alert_message = "Gagal menghapus komentar: " . $e->getMessage();
        $alert_type = "error";
    }
}

// --- AKSI DATA MAHASISWA ---
if (isset($_GET['action']) && $_GET['action'] === 'hapus_mahasiswa' && isset($_GET['id'])) {
    $id_mhs = (int)$_GET['id'];
    try {
        $pdo->prepare("DELETE FROM tbmahasiswa WHERE id_mahasiswa = ?")->execute([$id_mhs]);
        $alert_message = "Data mahasiswa berhasil dihapus dari sistem.";
        $alert_type = "success";
    } catch (PDOException $e) {
        $alert_message = "Gagal menghapus mahasiswa: " . $e->getMessage();
        $alert_type = "error";
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_mahasiswa'])) {
    $id_mhs = (int)$_POST['id_mahasiswa'];
    $nim = $_POST['nim'];
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $is_verified = $_POST['is_verified'];
    try {
        $pdo->prepare("UPDATE tbmahasiswa SET nim = ?, nama = ?, email = ?, is_verified = ? WHERE id_mahasiswa = ?")
            ->execute([$nim, $nama, $email, $is_verified, $id_mhs]);
        $alert_message = "Data mahasiswa berhasil diperbarui.";
        $alert_type = "success";
    } catch (PDOException $e) {
        $alert_message = "Gagal memperbarui data: " . $e->getMessage();
        $alert_type = "error";
    }
}

// --- AKSI KONTEN KEGIATAN ---
if (isset($_GET['action']) && $_GET['action'] === 'hapus_kegiatan' && isset($_GET['id'])) {
    $id_konten = (int)$_GET['id'];
    try {
        $pdo->prepare("DELETE FROM konten_kegiatan WHERE id_konten = ?")->execute([$id_konten]);
        $alert_message = "Konten kegiatan berhasil dihapus.";
        $alert_type = "success";
    } catch (PDOException $e) {
        $alert_message = "Gagal menghapus konten: " . $e->getMessage();
        $alert_type = "error";
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_kegiatan'])) {
    $id_konten = (int)$_POST['id_konten'];
    $judul = $_POST['judul_kegiatan'];
    $isi = $_POST['isi_kegiatan'];
    try {
        $pdo->prepare("UPDATE konten_kegiatan SET judul_kegiatan = ?, isi_kegiatan = ? WHERE id_konten = ?")
            ->execute([$judul, $isi, $id_konten]);
        $alert_message = "Konten kegiatan berhasil diperbarui.";
        $alert_type = "success";
    } catch (PDOException $e) {
        $alert_message = "Gagal memperbarui konten: " . $e->getMessage();
        $alert_type = "error";
    }
}

// --- AKSI DATA PENGURUS ORGANISASI ---
if (isset($_GET['action']) && $_GET['action'] === 'hapus_pengurus' && isset($_GET['id'])) {
    $id_pengurus = (int)$_GET['id'];
    try {
        $pdo->prepare("DELETE FROM pengurus_organisasi WHERE id_pengurus = ?")->execute([$id_pengurus]);
        $alert_message = "Data pengurus organisasi berhasil dihapus.";
        $alert_type = "success";
    } catch (PDOException $e) {
        $alert_message = "Gagal menghapus data pengurus: " . $e->getMessage();
        $alert_type = "error";
    }
}

// PROSES VERIFIKASI INSTAN (KLIK TOMBOL CENTANG HIJAU)
if (isset($_GET['action']) && $_GET['action'] === 'verifikasi_pengurus' && isset($_GET['id'])) {
    $id_pengurus = (int)$_GET['id'];
    
    $stmt = $pdo->prepare("SELECT nama_pengurus, no_hp, id_akses FROM pengurus_organisasi WHERE id_pengurus = ?");
    $stmt->execute([$id_pengurus]);
    $p_data = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($p_data) {
        if (empty($p_data['no_hp'])) {
            $alert_message = "Gagal Verifikasi! Nomor HP pengurus belum diisi.";
            $alert_type = "error";
        } else {
            $id_akses_baru = !empty($p_data['id_akses']) ? $p_data['id_akses'] : "PGR-" . date("Y") . "-" . rand(1000, 9999);
            try {
                $stmt_up = $pdo->prepare("UPDATE pengurus_organisasi SET id_akses = ?, status_verifikasi = 'Terverifikasi' WHERE id_pengurus = ?");
                $stmt_up->execute([$id_akses_baru, $id_pengurus]);

                $pesan_wa = "🔑 *VERIFIKASI AKUN PENGURUS ORGANISASI*\n\nHalo *" . $p_data['nama_pengurus'] . "*,\nAkun Pengurus Organisasi Anda telah diverifikasi oleh Administrator.\n\nBerikut adalah ID AKSES login Anda:\n👉 *[" . $id_akses_baru . "]*\n\nMohon simpan dan jaga kerahasiaan kode ini.";
                kirimWA($p_data['no_hp'], $pesan_wa);

                $alert_message = "Pengurus berhasil diverifikasi! ID Akses [" . $id_akses_baru . "] telah dikirim ke WhatsApp pengurus.";
                $alert_type = "success";
            } catch (PDOException $e) {
                $alert_message = "Gagal memproses verifikasi database: " . $e->getMessage();
                $alert_type = "error";
            }
        }
    } else {
        $alert_message = "Data pengurus tidak ditemukan.";
        $alert_type = "error";
    }
}

// PROSES SIMPAN FORM EDIT PENGURUS
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_pengurus'])) {
    $id_pengurus = (int)$_POST['id_pengurus'];
    $nama_pengurus = $_POST['nama_pengurus'];
    $jabatan = $_POST['jabatan'];
    $id_organisasi = (int)$_POST['id_organisasi'];
    $no_hp = $_POST['no_hp'];
    $status_verifikasi = $_POST['status_verifikasi'];
    
    try {
        $stmt_cek = $pdo->prepare("SELECT id_akses FROM pengurus_organisasi WHERE id_pengurus = ?");
        $stmt_cek->execute([$id_pengurus]);
        $curr = $stmt_cek->fetch(PDO::FETCH_ASSOC);
        
        $id_akses_final = $curr['id_akses'] ?? '';

        if ($status_verifikasi === 'Terverifikasi' && empty($id_akses_final)) {
            $id_akses_final = "PGR-" . date("Y") . "-" . rand(1000, 9999);
            $pdo->prepare("UPDATE pengurus_organisasi SET nama_pengurus = ?, jabatan = ?, id_organisasi = ?, no_hp = ?, status_verifikasi = ?, id_akses = ? WHERE id_pengurus = ?")
                ->execute([$nama_pengurus, $jabatan, $id_organisasi, $no_hp, $status_verifikasi, $id_akses_final, $id_pengurus]);
                
            $pesan_wa = "🔑 *VERIFIKASI AKUN PENGURUS ORGANISASI*\n\nHalo *" . $nama_pengurus . "*,\nAkun Anda telah diverifikasi oleh Administrator.\n\nBerikut adalah ID AKSES login Anda:\n👉 *[" . $id_akses_final . "]*\n\nMohon simpan kode ini untuk masuk ke sistem.";
            kirimWA($no_hp, $pesan_wa);
        } else {
            $pdo->prepare("UPDATE pengurus_organisasi SET nama_pengurus = ?, jabatan = ?, id_organisasi = ?, no_hp = ?, status_verifikasi = ? WHERE id_pengurus = ?")
                ->execute([$nama_pengurus, $jabatan, $id_organisasi, $no_hp, $status_verifikasi, $id_pengurus]);
        }
        
        $alert_message = "Data pengurus organisasi berhasil diperbarui.";
        $alert_type = "success";
    } catch (PDOException $e) {
        $alert_message = "Gagal memperbarui data pengurus: " . $e->getMessage();
        $alert_type = "error";
    }
}


// ==========================================
// 4. AMBIL DATA ADMIN & QUERY DATABASE
// ==========================================
function fetchAllSafe($pdo, $sql, $params = []) {
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (PDOException $e) { return []; }
}

function fetchCountSafe($pdo, $sql, $params = []) {
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    } catch (PDOException $e) { return 0; }
}

// AMBIL DATA PROFIL ADMIN REAL-TIME DARI DB
$stmt_admin = $pdo->prepare("SELECT * FROM administrator WHERE id_admin = ?");
$stmt_admin->execute([$admin_id]);
$admin_data = $stmt_admin->fetch(PDO::FETCH_ASSOC);
$admin_nama = $admin_data['nama_lengkap'] ?? 'Administrator';

// AMBIL DATA JIKA TOMBOL EDIT DIKLIK
$edit_mhs = null;
if ($tab === 'mahasiswa' && isset($_GET['action']) && $_GET['action'] === 'edit_mahasiswa' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM tbmahasiswa WHERE id_mahasiswa = ?");
    $stmt->execute([(int)$_GET['id']]);
    $edit_mhs = $stmt->fetch(PDO::FETCH_ASSOC);
}

$edit_keg = null;
if ($tab === 'kegiatan' && isset($_GET['action']) && $_GET['action'] === 'edit_kegiatan' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM konten_kegiatan WHERE id_konten = ?");
    $stmt->execute([(int)$_GET['id']]);
    $edit_keg = $stmt->fetch(PDO::FETCH_ASSOC);
}

$edit_pengurus = null;
if ($tab === 'pengurus' && isset($_GET['action']) && $_GET['action'] === 'edit_pengurus' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM pengurus_organisasi WHERE id_pengurus = ?");
    $stmt->execute([(int)$_GET['id']]);
    $edit_pengurus = $stmt->fetch(PDO::FETCH_ASSOC);
}


// ==========================================
// 5. PENGAMBILAN DATA STATISTIK & TABEL
// ==========================================
$stats = [
    'mahasiswa' => fetchCountSafe($pdo, "SELECT COUNT(*) FROM tbmahasiswa"),
    'konten'    => fetchCountSafe($pdo, "SELECT COUNT(*) FROM konten_kegiatan"),
    'aspirasi'  => fetchCountSafe($pdo, "SELECT COUNT(*) FROM aspirasi"),
    'komentar'  => fetchCountSafe($pdo, "SELECT COUNT(*) FROM komentar")
];

$data_mahasiswa = [];
$data_organisasi = [];
$data_aspirasi = [];
$data_komentar = [];
$data_pengurus = [];
$data_kegiatan = [];
$data_pendaftaran = [];
$list_organisasi = fetchAllSafe($pdo, "SELECT id_organisasi, nama_organisasi FROM organisasi ORDER BY nama_organisasi ASC");

if ($tab === 'dashboard' || $tab === 'mahasiswa') {
    $data_mahasiswa = fetchAllSafe($pdo, "SELECT * FROM tbmahasiswa ORDER BY id_mahasiswa DESC");
}

if ($tab === 'organisasi') {
    $data_organisasi = fetchAllSafe($pdo, "SELECT o.*, 
        (SELECT COUNT(*) FROM pengurus_organisasi p WHERE p.id_organisasi = o.id_organisasi) as total_pengurus,
        (SELECT COUNT(*) FROM konten_kegiatan k WHERE k.id_organisasi = o.id_organisasi) as total_konten
        FROM organisasi o ORDER BY o.nama_organisasi ASC");
        
    $all_pengurus_org = fetchAllSafe($pdo, "SELECT * FROM pengurus_organisasi ORDER BY id_pengurus DESC");
    $pengurus_by_org = [];
    foreach ($all_pengurus_org as $p) {
        $pengurus_by_org[$p['id_organisasi']][] = $p;
    }

    $all_kegiatan_org = fetchAllSafe($pdo, "SELECT * FROM konten_kegiatan ORDER BY id_konten DESC");
    $kegiatan_by_org = [];
    foreach ($all_kegiatan_org as $k) {
        $kegiatan_by_org[$k['id_organisasi']][] = $k;
    }
}

if ($tab === 'pengurus') {
    $data_pengurus = fetchAllSafe($pdo, "SELECT p.*, o.nama_organisasi 
        FROM pengurus_organisasi p 
        LEFT JOIN organisasi o ON p.id_organisasi = o.id_organisasi 
        ORDER BY p.id_pengurus DESC");
}

if ($tab === 'kegiatan') {
    $data_kegiatan = fetchAllSafe($pdo, "SELECT k.*, o.nama_organisasi 
        FROM konten_kegiatan k 
        LEFT JOIN organisasi o ON k.id_organisasi = o.id_organisasi 
        ORDER BY k.id_konten DESC");
}

if ($tab === 'pendaftaran') {
    $data_pendaftaran = fetchAllSafe($pdo, "SELECT p.*, m.nama, m.nim, o.nama_organisasi, k.judul_kegiatan 
        FROM pendaftaran p 
        LEFT JOIN tbmahasiswa m ON p.id_mahasiswa = m.id_mahasiswa 
        LEFT JOIN organisasi o ON p.id_organisasi = o.id_organisasi 
        LEFT JOIN konten_kegiatan k ON p.id_konten = k.id_konten
        ORDER BY p.id_pendaftaran DESC");
}

if ($tab === 'moderasi_aspirasi') {
    $data_aspirasi = fetchAllSafe($pdo, "SELECT a.*, m.nama, m.nim,
        (SELECT COUNT(*) FROM tblike l WHERE l.id_aspirasi = a.id_aspirasi) as total_like,
        (SELECT COUNT(*) FROM komentar k WHERE k.id_aspirasi = a.id_aspirasi) as total_komentar
        FROM aspirasi a 
        LEFT JOIN tbmahasiswa m ON a.id_mahasiswa = m.id_mahasiswa 
        ORDER BY a.id_aspirasi DESC");
}

if ($tab === 'moderasi_interaksi') {
    $data_komentar = fetchAllSafe($pdo, "SELECT k.*, 
        CASE 
            WHEN k.level_user = 'mahasiswa' THEN m.nama 
            WHEN k.level_user = 'admin' THEN ad.nama_lengkap 
        END AS nama_komentator,
        m.nim, 
        a.isi_aspirasi 
        FROM komentar k
        LEFT JOIN tbmahasiswa m ON k.id_user = m.id_mahasiswa AND k.level_user = 'mahasiswa'
        LEFT JOIN administrator ad ON k.id_user = ad.id_admin AND k.level_user = 'admin'
        LEFT JOIN aspirasi a ON k.id_aspirasi = a.id_aspirasi
        ORDER BY k.id_komentar DESC");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrator Dashboard - MASAGENA ITH</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #F8FAFC; }
        .scrollbar-hide::-webkit-scrollbar { display: none; }
        .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
        
        /* Animasi Modal Profil */
        @keyframes fadeInScale {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }
        .modal-animate {
            animation: fadeInScale 0.3s ease-out forwards;
        }
    </style>
</head>
<body class="h-screen flex overflow-hidden text-gray-800">

    <aside class="w-[260px] bg-[#1F3D68] text-white flex-shrink-0 flex flex-col z-20 shadow-xl transition-all duration-300">
        <div class="h-20 flex items-center px-6 border-b border-white/10 bg-[#172f53]">
            <div class="w-9 h-9 rounded-xl bg-[#F59E0B] text-white flex items-center justify-center font-black text-lg mr-3 shadow-md">M</div>
            <div>
                <h1 class="font-extrabold text-sm tracking-wider leading-none">MASAGENA <span class="text-[#F59E0B]">ITH</span></h1>
                <span class="text-[9px] text-blue-200/60 font-bold tracking-[0.15em] uppercase block mt-1">ADMINISTRATOR</span>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto scrollbar-hide py-5 px-4 space-y-1">
            <p class="text-[10px] font-bold text-blue-300/40 uppercase tracking-widest px-3 mb-2 mt-2">Main Menu</p>
            <a href="dashboard_admin.php?tab=dashboard" class="flex items-center gap-3 px-4 py-3 rounded-xl text-xs font-bold transition duration-200 <?= $tab === 'dashboard' ? 'bg-[#F59E0B] text-white shadow-md' : 'text-blue-100/80 hover:bg-white/5 hover:text-white' ?>">
                <i class="fa-solid fa-chart-pie w-5 text-center text-sm"></i><span>Dashboard</span>
            </a>

            <p class="text-[10px] font-bold text-blue-300/40 uppercase tracking-widest px-3 mb-2 mt-4">Master Data</p>
            <a href="dashboard_admin.php?tab=mahasiswa" class="flex items-center gap-3 px-4 py-3 rounded-xl text-xs font-bold transition duration-200 <?= $tab === 'mahasiswa' ? 'bg-[#F59E0B] text-white shadow-md' : 'text-blue-100/80 hover:bg-white/5 hover:text-white' ?>">
                <i class="fa-solid fa-users w-5 text-center text-sm"></i><span>Data Mahasiswa</span>
            </a>
            <a href="dashboard_admin.php?tab=organisasi" class="flex items-center gap-3 px-4 py-3 rounded-xl text-xs font-bold transition duration-200 <?= $tab === 'organisasi' ? 'bg-[#F59E0B] text-white shadow-md' : 'text-blue-100/80 hover:bg-white/5 hover:text-white' ?>">
                <i class="fa-solid fa-building-columns w-5 text-center text-sm"></i><span>Pantau Organisasi</span>
            </a>
            <a href="dashboard_admin.php?tab=pengurus" class="flex items-center gap-3 px-4 py-3 rounded-xl text-xs font-bold transition duration-200 <?= $tab === 'pengurus' ? 'bg-[#F59E0B] text-white shadow-md' : 'text-blue-100/80 hover:bg-white/5 hover:text-white' ?>">
                <i class="fa-solid fa-sitemap w-5 text-center text-sm"></i><span>Data Pengurus Org.</span>
            </a>

            <p class="text-[10px] font-bold text-blue-300/40 uppercase tracking-widest px-3 mb-2 mt-4">Manajemen Konten</p>
            <a href="dashboard_admin.php?tab=kegiatan" class="flex items-center gap-3 px-4 py-3 rounded-xl text-xs font-bold transition duration-200 <?= $tab === 'kegiatan' ? 'bg-[#F59E0B] text-white shadow-md' : 'text-blue-100/80 hover:bg-white/5 hover:text-white' ?>">
                <i class="fa-regular fa-newspaper w-5 text-center text-sm"></i><span>Konten Kegiatan</span>
            </a>
            <a href="dashboard_admin.php?tab=pendaftaran" class="flex items-center gap-3 px-4 py-3 rounded-xl text-xs font-bold transition duration-200 <?= $tab === 'pendaftaran' ? 'bg-[#F59E0B] text-white shadow-md' : 'text-blue-100/80 hover:bg-white/5 hover:text-white' ?>">
                <i class="fa-solid fa-clipboard-list w-5 text-center text-sm"></i><span>Pendaftaran Mhs</span>
            </a>

            <p class="text-[10px] font-bold text-blue-300/40 uppercase tracking-widest px-3 mb-2 mt-4">Sistem Moderasi</p>
            <a href="dashboard_admin.php?tab=moderasi_aspirasi" class="flex items-center gap-3 px-4 py-3 rounded-xl text-xs font-bold transition duration-200 <?= $tab === 'moderasi_aspirasi' ? 'bg-[#F59E0B] text-white shadow-md' : 'text-blue-100/80 hover:bg-white/5 hover:text-white' ?>">
                <i class="fa-solid fa-heart-circle-exclamation w-5 text-center text-sm"></i><span>Moderasi Aspirasi</span>
            </a>
            <a href="dashboard_admin.php?tab=moderasi_interaksi" class="flex items-center gap-3 px-4 py-3 rounded-xl text-xs font-bold transition duration-200 <?= $tab === 'moderasi_interaksi' ? 'bg-[#F59E0B] text-white shadow-md' : 'text-blue-100/80 hover:bg-white/5 hover:text-white' ?>">
                <i class="fa-regular fa-comments w-5 text-center text-sm"></i><span>Moderasi Komen</span>
            </a>
        </div>

        <div class="p-4 border-t border-white/10 bg-[#172f53]">
            
        </div>
    </aside>

    <div class="flex-1 flex flex-col h-screen overflow-hidden relative">
        
        <header class="h-20 bg-white border-b border-gray-200 flex items-center justify-between px-8 flex-shrink-0 shadow-sm z-30">
            <div>
                <h2 class="text-base font-black text-[#1F3D68] uppercase tracking-wider">Panel <?= htmlspecialchars(str_replace('_', ' ', ucfirst($tab))) ?></h2>
                <p class="text-[11px] text-gray-400 font-bold mt-0.5">Sistem Informasi Kemahasiswaan</p>
            </div>
            
            <div class="relative dropdown-container">
                <button onclick="toggleProfileDropdown()" class="flex items-center gap-3 bg-gray-50 border border-gray-200 px-4 py-1.5 rounded-xl hover:bg-gray-100 transition focus:outline-none focus:ring-2 focus:ring-[#1F3D68]/20">
                    <div class="text-right">
                        <p class="text-xs font-extrabold text-[#1F3D68] leading-tight"><?= htmlspecialchars($admin_nama) ?></p>
                        <span class="text-[10px] font-bold text-emerald-500 flex items-center justify-end gap-1 mt-0.5">
                            <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full inline-block animate-pulse"></span> Mode Admin
                        </span>
                    </div>
                    <div class="w-9 h-9 rounded-xl bg-[#1F3D68] text-white flex items-center justify-center font-bold text-sm shadow-md">
                        <?= strtoupper(substr($admin_data['username'] ?? 'A', 0, 1)) ?>
                    </div>
                </button>

                <div id="profileDropdown" class="hidden absolute right-0 mt-3 w-48 bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden z-50 transform origin-top-right transition-all">
                    <button onclick="openProfileModal()" class="w-full text-left px-4 py-3 text-xs font-bold text-gray-700 hover:bg-gray-50 hover:text-[#1F3D68] transition flex items-center gap-2">
                        <i class="fa-regular fa-id-card text-sm"></i> Rincian Profil
                    </button>
                    <hr class="border-gray-100">
                    <a href="dashboard_admin.php?action=logout" onclick="return confirm('Yakin ingin keluar?')" class="w-full block text-left px-4 py-3 text-xs font-bold text-red-600 hover:bg-red-50 transition flex items-center gap-2">
                        <i class="fa-solid fa-right-from-bracket text-sm"></i> Keluar Sistem
                    </a>
                </div>
            </div>
        </header>

        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-[#F8FAFC] p-8 z-10">
            
            <?php if(!empty($alert_message)): ?>
                <div class="mb-6 p-4 rounded-xl text-xs font-bold flex items-center gap-3 shadow-sm <?= $alert_type === 'success' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-red-50 text-red-800 border border-red-200' ?>">
                    <i class="<?= $alert_type === 'success' ? 'fa-solid fa-circle-check text-lg' : 'fa-solid fa-triangle-exclamation text-lg' ?>"></i>
                    <span><?= htmlspecialchars($alert_message) ?></span>
                </div>
            <?php endif; ?>
            
            <?php if ($tab === 'dashboard'): ?>
                <div class="bg-gradient-to-r from-[#1F3D68] to-[#25497c] rounded-[24px] p-8 text-white shadow-md relative overflow-hidden mb-8">
                    <div class="relative z-10 max-w-2xl">
                        <h3 class="text-xl font-black mb-1.5">Selamat Bekerja, <?= htmlspecialchars($admin_nama) ?>! ⚙️</h3>
                        <p class="text-blue-100/80 text-xs leading-relaxed font-medium">Pantau dan bersihkan konten, kelola aspirasi mahasiswa dari hal negatif, serta jaga kenyamanan ekosistem ruang komunikasi kemahasiswaan MASAGENA ITH.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white rounded-[20px] border border-gray-100 p-5 shadow-sm flex items-center justify-between hover:shadow-md transition duration-300">
                        <div>
                            <span class="text-[10px] font-extrabold text-gray-400 uppercase tracking-widest block mb-1">Total Mahasiswa</span>
                            <h4 class="text-2xl font-black text-[#1F3D68] leading-none"><?= $stats['mahasiswa'] ?></h4>
                        </div>
                        <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg shadow-inner"><i class="fa-solid fa-graduation-cap"></i></div>
                    </div>
                    <div class="bg-white rounded-[20px] border border-gray-100 p-5 shadow-sm flex items-center justify-between hover:shadow-md transition duration-300">
                        <div>
                            <span class="text-[10px] font-extrabold text-gray-400 uppercase tracking-widest block mb-1">Konten Kegiatan</span>
                            <h4 class="text-2xl font-black text-[#1F3D68] leading-none"><?= $stats['konten'] ?></h4>
                        </div>
                        <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-500 flex items-center justify-center text-lg shadow-inner"><i class="fa-regular fa-newspaper"></i></div>
                    </div>
                    <div class="bg-white rounded-[20px] border border-gray-100 p-5 shadow-sm flex items-center justify-between hover:shadow-md transition duration-300">
                        <div>
                            <span class="text-[10px] font-extrabold text-gray-400 uppercase tracking-widest block mb-1">Aspirasi Masuk</span>
                            <h4 class="text-2xl font-black text-[#1F3D68] leading-none"><?= $stats['aspirasi'] ?></h4>
                        </div>
                        <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-500 flex items-center justify-center text-lg shadow-inner"><i class="fa-solid fa-lightbulb"></i></div>
                    </div>
                    <div class="bg-white rounded-[20px] border border-gray-100 p-5 shadow-sm flex items-center justify-between hover:shadow-md transition duration-300">
                        <div>
                            <span class="text-[10px] font-extrabold text-gray-400 uppercase tracking-widest block mb-1">Komentar Aktif</span>
                            <h4 class="text-2xl font-black text-[#1F3D68] leading-none"><?= $stats['komentar'] ?></h4>
                        </div>
                        <div class="w-11 h-11 rounded-xl bg-purple-50 text-purple-500 flex items-center justify-center text-lg shadow-inner"><i class="fa-regular fa-comments"></i></div>
                    </div>
                </div>

                <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6">
                    <h4 class="text-xs font-black text-[#1F3D68] uppercase tracking-wider mb-5"><i class="fa-solid fa-list-check text-[#F59E0B] mr-2"></i> Mahasiswa Terdaftar Terbaru</h4>
                    <div class="overflow-x-auto rounded-xl border border-gray-200">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-200 text-gray-500 font-bold uppercase tracking-wider">
                                    <th class="py-3.5 px-4">NIM</th>
                                    <th class="py-3.5 px-4">Nama Lengkap</th>
                                    <th class="py-3.5 px-4 text-center">Status Verifikasi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 font-semibold text-gray-700">
                                <?php if (empty($data_mahasiswa)): ?>
                                    <tr><td colspan="3" class="p-8 text-center text-gray-400 font-medium">Belum ada data mahasiswa masuk.</td></tr>
                                <?php else: ?>
                                    <?php foreach (array_slice($data_mahasiswa, 0, 5) as $mhs): ?>
                                        <tr class="hover:bg-gray-50/60 transition">
                                            <td class="py-4 px-4 font-bold text-[#1F3D68]"><?= htmlspecialchars($mhs['nim']) ?></td>
                                            <td class="py-4 px-4"><?= htmlspecialchars($mhs['nama']) ?></td>
                                            <td class="py-4 px-4 text-center">
                                                <?php if ($mhs['is_verified'] == '1'): ?>
                                                    <span class="px-2.5 py-1 bg-green-50 text-green-700 rounded-lg text-[10px] font-bold"><i class="fa-solid fa-circle-check text-[9px] mr-1"></i> Terverifikasi</span>
                                                <?php else: ?>
                                                    <span class="px-2.5 py-1 bg-amber-50 text-amber-700 rounded-lg text-[10px] font-bold"><i class="fa-solid fa-clock text-[9px] mr-1"></i> Pending</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($tab === 'mahasiswa'): ?>
                <?php if ($edit_mhs): ?>
                    <div class="bg-white rounded-3xl border border-amber-200 shadow-md p-6 mb-8 bg-gradient-to-b from-amber-50/30 to-white">
                        <div class="flex justify-between items-center mb-4">
                            <h4 class="text-xs font-black text-amber-800 uppercase tracking-wider"><i class="fa-solid fa-user-pen mr-2"></i> Edit Data Mahasiswa</h4>
                            <a href="dashboard_admin.php?tab=mahasiswa" class="text-xs font-bold text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark"></i> Batal</a>
                        </div>
                        <form action="dashboard_admin.php?tab=mahasiswa" method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                            <input type="hidden" name="id_mahasiswa" value="<?= $edit_mhs['id_mahasiswa'] ?>">
                            <div>
                                <label class="text-[10px] font-bold text-gray-500 uppercase">NIM</label>
                                <input type="text" name="nim" value="<?= htmlspecialchars($edit_mhs['nim']) ?>" required class="w-full mt-1 p-2.5 bg-white border border-gray-200 rounded-xl text-xs font-semibold focus:outline-none focus:border-[#1F3D68]">
                            </div>
                            <div>
                                <label class="text-[10px] font-bold text-gray-500 uppercase">Nama Lengkap</label>
                                <input type="text" name="nama" value="<?= htmlspecialchars($edit_mhs['nama']) ?>" required class="w-full mt-1 p-2.5 bg-white border border-gray-200 rounded-xl text-xs font-semibold focus:outline-none focus:border-[#1F3D68]">
                            </div>
                            <div>
                                <label class="text-[10px] font-bold text-gray-500 uppercase">Email</label>
                                <input type="email" name="email" value="<?= htmlspecialchars($edit_mhs['email'] ?? '') ?>" class="w-full mt-1 p-2.5 bg-white border border-gray-200 rounded-xl text-xs font-semibold focus:outline-none focus:border-[#1F3D68]">
                            </div>
                            <div>
                                <label class="text-[10px] font-bold text-gray-500 uppercase">Status Verifikasi</label>
                                <select name="is_verified" class="w-full mt-1 p-2.5 bg-white border border-gray-200 rounded-xl text-xs font-semibold focus:outline-none focus:border-[#1F3D68]">
                                    <option value="1" <?= $edit_mhs['is_verified'] == '1' ? 'selected' : '' ?>>Aktif (Terverifikasi)</option>
                                    <option value="0" <?= $edit_mhs['is_verified'] == '0' ? 'selected' : '' ?>>Belum Verifikasi</option>
                                </select>
                            </div>
                            <div class="md:col-span-4 flex justify-end">
                                <button type="submit" name="update_mahasiswa" class="bg-[#1F3D68] hover:bg-[#152a48] text-white font-bold text-xs px-5 py-2.5 rounded-xl shadow-sm transition">
                                    <i class="fa-solid fa-floppy-disk mr-1"></i> Simpan Perubahan
                                </button>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>

                <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6">
                    <div class="mb-5"><h4 class="text-sm font-black text-[#1F3D68] uppercase tracking-wider">Kelola Master Data Mahasiswa</h4></div>
                    <div class="overflow-x-auto rounded-xl border border-gray-200">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-200 text-gray-500 font-bold uppercase tracking-wider">
                                    <th class="py-3.5 px-4">NIM</th>
                                    <th class="py-3.5 px-4">Nama Lengkap</th>
                                    <th class="py-3.5 px-4">Email</th>
                                    <th class="py-3.5 px-4 text-center">Status</th>
                                    <th class="py-3.5 px-4 text-center w-36">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 font-semibold text-gray-700">
                                <?php if (empty($data_mahasiswa)): ?>
                                    <tr><td colspan="5" class="p-8 text-center text-gray-400 font-medium">Belum ada data mahasiswa terdaftar.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($data_mahasiswa as $mhs): ?>
                                        <tr class="hover:bg-gray-50/60 transition">
                                            <td class="py-4 px-4 font-bold text-[#1F3D68]"><?= htmlspecialchars($mhs['nim']) ?></td>
                                            <td class="py-4 px-4"><?= htmlspecialchars($mhs['nama']) ?></td>
                                            <td class="py-4 px-4 text-gray-500"><?= htmlspecialchars($mhs['email'] ?? '-') ?></td>
                                            <td class="py-4 px-4 text-center">
                                                <?php if ($mhs['is_verified'] == '1'): ?>
                                                    <span class="px-2.5 py-1 bg-green-50 text-green-700 rounded-lg text-[10px] font-bold">Aktif</span>
                                                <?php else: ?>
                                                    <span class="px-2.5 py-1 bg-rose-50 text-rose-700 rounded-lg text-[10px] font-bold">Belum Verifikasi</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="py-4 px-4 text-center space-x-1">
                                                <a href="dashboard_admin.php?tab=mahasiswa&action=edit_mahasiswa&id=<?= $mhs['id_mahasiswa'] ?>" class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-100 transition shadow-sm" title="Edit Data">
                                                    <i class="fa-solid fa-pen text-[10px]"></i>
                                                </a>
                                                <a href="dashboard_admin.php?tab=mahasiswa&action=hapus_mahasiswa&id=<?= $mhs['id_mahasiswa'] ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus data mahasiswa ini?')" class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-100 transition shadow-sm" title="Hapus Data">
                                                    <i class="fa-solid fa-trash-can text-[10px]"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($tab === 'organisasi'): ?>
                <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6">
                    <div class="mb-6 flex justify-between items-center">
                        <div>
                            <h4 class="text-sm font-black text-[#1F3D68] uppercase tracking-wider">Pantau Data Organisasi</h4>
                            <p class="text-[11px] text-gray-400 font-medium mt-1">Daftar seluruh organisasi mahasiswa yang terdaftar di sistem.</p>
                        </div>
                        <span class="text-[10px] bg-blue-50 text-blue-600 px-3 py-1.5 rounded-full font-bold border border-blue-100 flex items-center gap-1.5 shadow-sm">
                            <i class="fa-solid fa-hand-pointer animate-pulse text-xs"></i> 
                            Klik baris tabel untuk melihat detail anggota
                        </span>
                    </div>

                    <div class="overflow-x-auto rounded-xl border border-gray-200">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-200 text-gray-500 font-bold uppercase tracking-wider">
                                    <th class="py-3.5 px-5">Nama Organisasi</th>
                                    <th class="py-3.5 px-4 text-center">Total Pengurus</th>
                                    <th class="py-3.5 px-4 text-center">Publikasi Kegiatan</th>
                                    <th class="py-3.5 px-5 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 font-semibold text-gray-700">
                                <?php if (empty($data_organisasi)): ?>
                                    <tr>
                                        <td colspan="4" class="py-8 text-center text-gray-400 font-medium">Belum ada data organisasi.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($data_organisasi as $org): ?>
                                        <tr class="hover:bg-blue-50/40 transition duration-200 cursor-pointer group" onclick="document.getElementById('modal_org_<?= $org['id_organisasi'] ?>').classList.remove('hidden')">
                                            <td class="py-4 px-5 font-bold text-[#1F3D68] group-hover:text-blue-700 flex items-center gap-3 transition">
                                                <div class="w-8 h-8 rounded-lg bg-gray-50 border border-gray-100 flex items-center justify-center text-gray-400 group-hover:bg-blue-100 group-hover:text-blue-600 group-hover:border-blue-200 transition">
                                                    <i class="fa-solid fa-users text-sm"></i>
                                                </div>
                                                <span><?= htmlspecialchars($org['nama_organisasi'] ?? '') ?></span>
                                            </td>
                                            <td class="py-4 px-4 text-center">
                                                <span class="bg-blue-50/80 text-blue-700 px-3 py-1 rounded-lg text-[11px] font-bold border border-blue-100/50"><?= $org['total_pengurus'] ?> Org</span>
                                            </td>
                                            <td class="py-4 px-4 text-center">
                                                <span class="bg-emerald-50 text-emerald-700 px-3 py-1 rounded-lg text-[11px] font-bold border border-emerald-100/50"><?= $org['total_konten'] ?> Konten</span>
                                            </td>
                                            <td class="py-4 px-5 text-center">
                                                <button class="text-[10px] bg-white border border-gray-200 text-gray-500 px-3 py-1.5 rounded-xl font-bold group-hover:bg-[#1F3D68] group-hover:text-white group-hover:border-[#1F3D68] shadow-sm transition duration-200">
                                                    Lihat Rincian <i class="fa-solid fa-angle-right ml-1"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <?php foreach ($data_organisasi as $org): ?>
                    <div id="modal_org_<?= $org['id_organisasi'] ?>" class="hidden fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm transition-all">
                        <div class="bg-white rounded-3xl w-full max-w-4xl shadow-2xl overflow-hidden transform transition-all scale-100 flex flex-col max-h-[90vh]">
                            <div class="bg-gradient-to-r from-[#1F3D68] to-[#2b538c] p-6 flex justify-between items-center shrink-0">
                                <div>
                                    <h3 class="text-white font-black text-lg tracking-wide">Rincian Organisasi</h3>
                                    <p class="text-blue-100 text-xs font-semibold mt-0.5 opacity-90"><i class="fa-solid fa-building-columns mr-1 text-[11px]"></i> <?= htmlspecialchars($org['nama_organisasi'] ?? '') ?></p>
                                </div>
                                <button onclick="event.stopPropagation(); document.getElementById('modal_org_<?= $org['id_organisasi'] ?>').classList.add('hidden')" class="w-8 h-8 flex items-center justify-center rounded-full bg-white/10 text-white hover:bg-white/20 hover:rotate-90 transition duration-300 focus:outline-none">
                                    <i class="fa-solid fa-xmark text-base"></i>
                                </button>
                            </div>
                            
                            <div class="p-6 overflow-y-auto scrollbar-hide flex-1 space-y-8 bg-gray-50/50">
                                <div>
                                    <div class="flex items-center gap-2 mb-4">
                                        <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center"><i class="fa-solid fa-users-gear text-sm"></i></div>
                                        <h4 class="font-bold text-[#1F3D68] text-sm uppercase tracking-wide">Susunan Pengurus</h4>
                                    </div>
                                    <?php if (empty($pengurus_by_org[$org['id_organisasi']])): ?>
                                        <div class="text-center py-8 text-gray-400 bg-white rounded-xl border border-dashed border-gray-200">
                                            <p class="text-xs font-bold tracking-wide">Belum ada pengurus yang terdaftar.</p>
                                        </div>
                                    <?php else: ?>
                                        <div class="overflow-x-auto rounded-xl border border-gray-200 shadow-sm bg-white">
                                            <table class="w-full text-left text-xs border-collapse">
                                                <thead>
                                                    <tr class="bg-gray-50 border-b border-gray-200 text-gray-500 font-bold uppercase tracking-wider">
                                                        <th class="py-3 px-4 w-12 text-center">No</th>
                                                        <th class="py-3 px-4">Nama Lengkap</th>
                                                        <th class="py-3 px-4">Jabatan</th>
                                                        <th class="py-3 px-4">No. WhatsApp</th>
                                                        <th class="py-3 px-4 text-center">Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-gray-100 font-semibold text-gray-700">
                                                    <?php $no = 1; foreach ($pengurus_by_org[$org['id_organisasi']] as $p): ?>
                                                        <tr class="hover:bg-gray-50 transition">
                                                            <td class="py-3 px-4 text-center text-gray-400"><?= $no++ ?></td>
                                                            <td class="py-3 px-4 text-[#1F3D68] font-bold"><?= htmlspecialchars($p['nama_pengurus'] ?? '') ?></td>
                                                            <td class="py-3 px-4 text-gray-600"><?= htmlspecialchars($p['jabatan'] ?? '') ?></td>
                                                            <td class="py-3 px-4 text-gray-500 font-mono text-[11px]"><?= htmlspecialchars($p['no_hp'] ?: '-') ?></td>
                                                            <td class="py-3 px-4 text-center">
                                                                <?= (($p['status_verifikasi'] ?? '') === 'Terverifikasi') ? '<span class="px-2 py-0.5 bg-green-50 text-green-700 rounded text-[10px] font-bold border border-green-100"><i class="fa-solid fa-circle-check"></i> Aktif</span>' : '<span class="px-2 py-0.5 bg-amber-50 text-amber-700 rounded text-[10px] font-bold border border-amber-100">Pending</span>' ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <hr class="border-gray-200 border-dashed">
                                <div>
                                    <div class="flex items-center gap-2 mb-4">
                                        <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center"><i class="fa-solid fa-bullhorn text-sm"></i></div>
                                        <h4 class="font-bold text-[#1F3D68] text-sm uppercase tracking-wide">Riwayat Kegiatan Organisasi</h4>
                                    </div>
                                    <?php if (empty($kegiatan_by_org[$org['id_organisasi']])): ?>
                                        <div class="text-center py-8 text-gray-400 bg-white rounded-xl border border-dashed border-gray-200">
                                            <p class="text-xs font-bold tracking-wide">Belum ada aktivitas / kegiatan yang dipublikasikan.</p>
                                        </div>
                                    <?php else: ?>
                                        <div class="overflow-x-auto rounded-xl border border-gray-200 shadow-sm bg-white">
                                            <table class="w-full text-left text-xs border-collapse">
                                                <thead>
                                                    <tr class="bg-gray-50 border-b border-gray-200 text-gray-500 font-bold uppercase tracking-wider">
                                                        <th class="py-3 px-4 w-12 text-center">No</th>
                                                        <th class="py-3 px-4">Judul Kegiatan</th>
                                                        <th class="py-3 px-4">Tanggal Publikasi</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-gray-100 font-semibold text-gray-700">
                                                    <?php $nok = 1; foreach ($kegiatan_by_org[$org['id_organisasi']] as $k): ?>
                                                        <tr class="hover:bg-gray-50 transition">
                                                            <td class="py-3 px-4 text-center text-gray-400"><?= $nok++ ?></td>
                                                            <td class="py-3 px-4 text-[#1F3D68] font-bold"><?= htmlspecialchars($k['judul_kegiatan'] ?? '') ?></td>
                                                            <td class="py-3 px-4 text-gray-600 font-medium">
                                                                <i class="fa-regular fa-calendar text-gray-400 mr-1"></i> 
                                                                <?= htmlspecialchars($k['tanggal_upload'] ?? '-') ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="bg-gray-50 p-4 border-t border-gray-100 text-right shrink-0">
                                <button onclick="document.getElementById('modal_org_<?= $org['id_organisasi'] ?>').classList.add('hidden')" class="px-5 py-2.5 bg-[#1F3D68] text-white hover:bg-[#2b538c] font-bold rounded-xl shadow-sm transition text-xs">
                                    Tutup Rincian
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
            
            <?php if ($tab === 'pengurus'): ?>
                <?php if ($edit_pengurus): ?>
                    <div class="bg-white rounded-3xl border border-amber-200 shadow-md p-6 mb-8 bg-gradient-to-b from-amber-50/30 to-white">
                        <div class="flex justify-between items-center mb-4">
                            <h4 class="text-xs font-black text-amber-800 uppercase tracking-wider"><i class="fa-solid fa-sitemap mr-2"></i> Edit Data Pengurus</h4>
                            <a href="dashboard_admin.php?tab=pengurus" class="text-xs font-bold text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark"></i> Batal</a>
                        </div>
                        <form action="dashboard_admin.php?tab=pengurus" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                            <input type="hidden" name="id_pengurus" value="<?= $edit_pengurus['id_pengurus'] ?>">
                            <div>
                                <label class="text-[10px] font-bold text-gray-500 uppercase">Nama Pengurus</label>
                                <input type="text" name="nama_pengurus" value="<?= htmlspecialchars($edit_pengurus['nama_pengurus']) ?>" required class="w-full mt-1 p-2.5 bg-white border border-gray-200 rounded-xl text-xs font-semibold focus:outline-none focus:border-[#1F3D68]">
                            </div>
                            <div>
                                <label class="text-[10px] font-bold text-gray-500 uppercase">Jabatan</label>
                                <input type="text" name="jabatan" value="<?= htmlspecialchars($edit_pengurus['jabatan']) ?>" required class="w-full mt-1 p-2.5 bg-white border border-gray-200 rounded-xl text-xs font-semibold focus:outline-none focus:border-[#1F3D68]">
                            </div>
                            <div>
                                <label class="text-[10px] font-bold text-gray-500 uppercase">Organisasi</label>
                                <select name="id_organisasi" required class="w-full mt-1 p-2.5 bg-white border border-gray-200 rounded-xl text-xs font-semibold focus:outline-none focus:border-[#1F3D68]">
                                    <?php foreach ($list_organisasi as $org): ?>
                                        <option value="<?= $org['id_organisasi'] ?>" <?= $org['id_organisasi'] == $edit_pengurus['id_organisasi'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($org['nama_organisasi']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="text-[10px] font-bold text-gray-500 uppercase">No HP</label>
                                <input type="text" name="no_hp" value="<?= htmlspecialchars($edit_pengurus['no_hp']) ?>" required class="w-full mt-1 p-2.5 bg-white border border-gray-200 rounded-xl text-xs font-semibold focus:outline-none focus:border-[#1F3D68]">
                            </div>
                            <div>
                                <label class="text-[10px] font-bold text-gray-500 uppercase">Status Verifikasi</label>
                                <select name="status_verifikasi" class="w-full mt-1 p-2.5 bg-white border border-gray-200 rounded-xl text-xs font-semibold focus:outline-none focus:border-[#1F3D68]">
                                    <option value="Terverifikasi" <?= $edit_pengurus['status_verifikasi'] === 'Terverifikasi' ? 'selected' : '' ?>>Terverifikasi</option>
                                    <option value="Belum" <?= $edit_pengurus['status_verifikasi'] !== 'Terverifikasi' ? 'selected' : '' ?>>Belum Verifikasi</option>
                                </select>
                            </div>
                            <div class="flex justify-end">
                                <button type="submit" name="update_pengurus" class="w-full bg-[#1F3D68] hover:bg-[#152a48] text-white font-bold text-xs px-5 py-2.5 rounded-xl shadow-sm transition">
                                    <i class="fa-solid fa-floppy-disk mr-1"></i> Simpan Perubahan
                                </button>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>

                <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6">
                    <div class="mb-5"><h4 class="text-sm font-black text-[#1F3D68] uppercase tracking-wider">Data Pengurus Organisasi</h4></div>
                    <div class="overflow-x-auto rounded-xl border border-gray-200">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-200 text-gray-500 font-bold uppercase tracking-wider">
                                    <th class="py-3.5 px-4">Nama Pengurus</th>
                                    <th class="py-3.5 px-4">Jabatan</th>
                                    <th class="py-3.5 px-4">Organisasi</th>
                                    <th class="py-3.5 px-4">No HP</th>
                                    <th class="py-3.5 px-4 text-center">ID Akses</th>
                                    <th class="py-3.5 px-4 text-center">Status</th>
                                    <th class="py-3.5 px-4 text-center w-36">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 font-semibold text-gray-700">
                                <?php foreach ($data_pengurus as $p): ?>
                                    <tr class="hover:bg-gray-50/60 transition">
                                        <td class="py-4 px-4 font-bold text-[#1F3D68]"><?= htmlspecialchars($p['nama_pengurus']) ?></td>
                                        <td class="py-4 px-4"><?= htmlspecialchars($p['jabatan']) ?></td>
                                        <td class="py-4 px-4 text-gray-500"><?= htmlspecialchars($p['nama_organisasi'] ?? '-') ?></td>
                                        <td class="py-4 px-4 text-gray-500"><?= htmlspecialchars($p['no_hp']) ?></td>
                                        <td class="py-4 px-4 text-center text-blue-600 font-mono font-bold">
                                            <?= !empty($p['id_akses']) ? htmlspecialchars($p['id_akses']) : '<span class="text-gray-300 font-normal italic">- Belum Dibuat -</span>' ?>
                                        </td>
                                        <td class="py-4 px-4 text-center">
                                            <?= $p['status_verifikasi'] === 'Terverifikasi' ? '<span class="px-2.5 py-1 bg-green-50 text-green-700 rounded-lg text-[10px] font-bold">Terverifikasi</span>' : '<span class="px-2.5 py-1 bg-rose-50 text-rose-700 rounded-lg text-[10px] font-bold">Belum</span>' ?>
                                        </td>
                                        <td class="py-4 px-4 text-center space-x-1">
                                            <?php if ($p['status_verifikasi'] !== 'Terverifikasi'): ?>
                                                <a href="dashboard_admin.php?tab=pengurus&action=verifikasi_pengurus&id=<?= $p['id_pengurus'] ?>" 
                                                   onclick="return confirm('Apakah Anda yakin ingin memverifikasi pengurus ini? ID AKSES otomatis akan dikirim ke nomor WhatsApp pengurus.')" 
                                                   class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-100 transition shadow-sm" 
                                                   title="Verifikasi & Kirim ID Akses">
                                                    <i class="fa-solid fa-check text-[11px] font-bold"></i>
                                                </a>
                                            <?php endif; ?>
                                            
                                            <a href="dashboard_admin.php?tab=pengurus&action=edit_pengurus&id=<?= $p['id_pengurus'] ?>" class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-100 transition shadow-sm" title="Edit Pengurus">
                                                <i class="fa-solid fa-pen text-[10px]"></i>
                                            </a>
                                            <a href="dashboard_admin.php?tab=pengurus&action=hapus_pengurus&id=<?= $p['id_pengurus'] ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus data pengurus ini?')" class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-100 transition shadow-sm" title="Hapus Pengurus">
                                                <i class="fa-solid fa-trash-can text-[10px]"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($tab === 'kegiatan'): ?>
                <?php if ($edit_keg): ?>
                    <div class="bg-white rounded-3xl border border-amber-200 shadow-md p-6 mb-8 bg-gradient-to-b from-amber-50/30 to-white">
                        <div class="flex justify-between items-center mb-4">
                            <h4 class="text-xs font-black text-[#1F3D68] uppercase tracking-wider"><i class="fa-solid fa-file-pen mr-2"></i> Edit Konten Kegiatan</h4>
                            <a href="dashboard_admin.php?tab=kegiatan" class="text-xs font-bold text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark"></i> Batal</a>
                        </div>
                        <form action="dashboard_admin.php?tab=kegiatan" method="POST" class="space-y-4">
                            <input type="hidden" name="id_konten" value="<?= $edit_keg['id_konten'] ?>">
                            <div>
                                <label class="text-[10px] font-bold text-gray-500 uppercase">Judul Kegiatan</label>
                                <input type="text" name="judul_kegiatan" value="<?= htmlspecialchars($edit_keg['judul_kegiatan']) ?>" required class="w-full mt-1 p-2.5 bg-white border border-gray-200 rounded-xl text-xs font-semibold focus:outline-none">
                            </div>
                            <div>
                                <label class="text-[10px] font-bold text-gray-500 uppercase">Isi / Deskripsi Kegiatan</label>
                                <textarea name="isi_kegiatan" rows="4" required class="w-full mt-1 p-2.5 bg-white border border-gray-200 rounded-xl text-xs font-semibold focus:outline-none"><?= htmlspecialchars($edit_keg['isi_kegiatan']) ?></textarea>
                            </div>
                            <div class="flex justify-end">
                                <button type="submit" name="update_kegiatan" class="bg-[#1F3D68] hover:bg-[#152a48] text-white font-bold text-xs px-5 py-2.5 rounded-xl transition">
                                    <i class="fa-solid fa-floppy-disk mr-1"></i> Perbarui Konten
                                </button>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>

                <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6">
                    <div class="mb-5"><h4 class="text-sm font-black text-[#1F3D68] uppercase tracking-wider">Manajemen Konten Kegiatan</h4></div>
                    <div class="overflow-x-auto rounded-xl border border-gray-200">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-200 text-gray-500 font-bold uppercase tracking-wider">
                                    <th class="py-3.5 px-4">Judul Kegiatan</th>
                                    <th class="py-3.5 px-4">Organisasi Pengunggah</th>
                                    <th class="py-3.5 px-4">Tanggal Upload</th>
                                    <th class="py-3.5 px-4 text-center w-36">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 font-semibold text-gray-700">
                                <?php foreach ($data_kegiatan as $k): ?>
                                    <tr class="hover:bg-gray-50/60 transition">
                                        <td class="py-4 px-4 font-bold text-[#1F3D68]"><?= htmlspecialchars($k['judul_kegiatan']) ?></td>
                                        <td class="py-4 px-4"><?= htmlspecialchars($k['nama_organisasi'] ?? '-') ?></td>
                                        <td class="py-4 px-4 text-gray-500"><?= htmlspecialchars($k['tanggal_upload']) ?></td>
                                        <td class="py-4 px-4 text-center space-x-1">
                                            <a href="dashboard_admin.php?tab=kegiatan&action=edit_kegiatan&id=<?= $k['id_konten'] ?>" class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-100 transition shadow-sm">
                                                <i class="fa-solid fa-pen text-[10px]"></i>
                                            </a>
                                            <a href="dashboard_admin.php?tab=kegiatan&action=hapus_kegiatan&id=<?= $k['id_konten'] ?>" onclick="return confirm('Hapus konten kegiatan ini?')" class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-100 transition shadow-sm">
                                                <i class="fa-solid fa-trash-can text-[10px]"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($tab === 'pendaftaran'): ?>
                <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6">
                    <div class="mb-5"><h4 class="text-sm font-black text-[#1F3D68] uppercase tracking-wider">Pendaftaran Mahasiswa pada Kegiatan</h4></div>
                    <div class="overflow-x-auto rounded-xl border border-gray-200">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-200 text-gray-500 font-bold uppercase tracking-wider">
                                    <th class="py-3.5 px-4">Mahasiswa (NIM)</th>
                                    <th class="py-3.5 px-4">Mendaftar Kegiatan</th>
                                    <th class="py-3.5 px-4">Organisasi</th>
                                    <th class="py-3.5 px-4 text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 font-semibold text-gray-700">
                                <?php foreach ($data_pendaftaran as $dftr): ?>
                                    <tr class="hover:bg-gray-50/60 transition">
                                        <td class="py-4 px-4 font-bold text-[#1F3D68]"><?= htmlspecialchars($dftr['nama']) ?><br><span class="text-[10px] text-gray-400 font-normal"><?= htmlspecialchars($dftr['nim']) ?></span></td>
                                        <td class="py-4 px-4"><?= htmlspecialchars($dftr['judul_kegiatan'] ?? '-') ?></td>
                                        <td class="py-4 px-4 text-gray-500"><?= htmlspecialchars($dftr['nama_organisasi'] ?? '-') ?></td>
                                        <td class="py-4 px-4 text-center">
                                            <span class="px-2.5 py-1 bg-gray-100 text-gray-700 rounded-lg text-[10px] font-bold uppercase"><?= htmlspecialchars($dftr['status_pendaftaran']) ?></span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($tab === 'moderasi_aspirasi'): ?>
                <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6">
                    <div class="mb-6"><h4 class="text-sm font-black text-[#1F3D68] uppercase tracking-wider">Moderasi Aspirasi Kampus</h4></div>
                    <div class="overflow-x-auto rounded-xl border border-gray-200">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-200 text-gray-500 font-bold uppercase tracking-wider">
                                    <th class="py-3.5 px-4 w-48">Pengirim</th>
                                    <th class="py-3.5 px-4">Isi Aspirasi</th>
                                    <th class="py-3.5 px-4 text-center w-28">Interaksi</th>
                                    <th class="py-3.5 px-4 text-center w-28">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 font-semibold text-gray-700">
                                <?php foreach ($data_aspirasi as $asp): ?>
                                    <tr class="hover:bg-gray-50/60 transition">
                                        <td class="py-4 px-4 align-top">
                                            <p class="font-bold text-[#1F3D68]"><?= htmlspecialchars($asp['nama'] ?? 'Anonim') ?></p>
                                            <span class="text-[10px] text-gray-400 block mt-0.5"><?= htmlspecialchars($asp['nim'] ?? '-') ?></span>
                                        </td>
                                        <td class="py-4 px-4 align-top text-gray-600 leading-relaxed font-medium">
                                            <?= nl2br(htmlspecialchars($asp['isi_aspirasi'])) ?>
                                            <span class="text-[10px] text-gray-400 block mt-2 font-bold"><i class="fa-regular fa-clock mr-1"></i> Diposting: <?= $asp['tanggal'] ?? '-' ?></span>
                                        </td>
                                        <td class="py-4 px-4 align-top text-center space-y-1">
                                            <span class="inline-flex items-center gap-1 bg-red-50 text-red-700 px-2 py-0.5 rounded text-[10px] font-bold"><i class="fa-solid fa-heart text-[9px]"></i> <?= $asp['total_like'] ?> Like</span>
                                            <span class="inline-flex items-center gap-1 bg-blue-50 text-blue-700 px-2 py-0.5 rounded text-[10px] font-bold"><i class="fa-solid fa-comment text-[9px]"></i> <?= $asp['total_komentar'] ?> Komen</span>
                                        </td>
                                        <td class="py-4 px-4 align-top text-center">
                                            <a href="dashboard_admin.php?tab=moderasi_aspirasi&action=hapus_aspirasi&id=<?= $asp['id_aspirasi'] ?>" onclick="return confirm('Hapus aspirasi ini?')" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1.5 rounded-lg text-[10px] font-bold inline-block shadow-sm transition">
                                                <i class="fa-solid fa-trash-can mr-1"></i> Hapus
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($tab === 'moderasi_interaksi'): ?>
                <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6">
                    <div class="mb-6"><h4 class="text-sm font-black text-[#1F3D68] uppercase tracking-wider">Moderasi Komentar & Obrolan</h4></div>
                    <div class="overflow-x-auto rounded-xl border border-gray-200">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-200 text-gray-500 font-bold uppercase tracking-wider">
                                    <th class="py-3.5 px-4 w-48">Komentator</th>
                                    <th class="py-3.5 px-4">Konten Komentar</th>
                                    <th class="py-3.5 px-4 w-60">Pada Aspirasi</th>
                                    <th class="py-3.5 px-4 text-center w-24">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 font-semibold text-gray-700">
                                <?php foreach ($data_komentar as $kom): ?>
                                    <tr class="hover:bg-gray-50/60 transition">
                                        <td class="py-4 px-4 align-top">
                                            <p class="font-bold text-[#1F3D68]"><?= htmlspecialchars($kom['nama_komentator'] ?? 'Pengguna Tidak Diketahui') ?></p>
                                            <span class="text-[10px] text-emerald-500 font-bold block mt-0.5 uppercase tracking-wide border border-emerald-200 bg-emerald-50 w-max px-1.5 rounded"><?= htmlspecialchars($kom['level_user']) ?></span>
                                        </td>
                                        <td class="py-4 px-4 align-top text-gray-700 font-medium leading-relaxed">
                                            <div class="bg-gray-50 p-3 rounded-xl border border-gray-100 italic text-gray-600">"<?= htmlspecialchars($kom['isi_komentar'] ?? '') ?>"</div>
                                        </td>
                                        <td class="py-4 px-4 align-top text-gray-400 text-[11px] leading-snug">
                                            <span class="font-bold block text-gray-500 mb-1">Potongan Aspirasi:</span>
                                            <?= htmlspecialchars(mb_strimwidth($kom['isi_aspirasi'] ?? 'Aspirasi telah dihapus', 0, 80, "...")) ?>
                                        </td>
                                        <td class="py-4 px-4 align-top text-center">
                                            <a href="dashboard_admin.php?tab=moderasi_interaksi&action=hapus_komentar&id=<?= $kom['id_komentar'] ?>" onclick="return confirm('Hapus komentar tidak pantas ini?')" class="text-red-500 hover:text-red-700 px-2 py-1 rounded border border-red-200 bg-red-50/40 hover:bg-red-50 text-[11px] font-bold inline-block transition">
                                                <i class="fa-regular fa-trash-can"></i> Hapus
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>

        </main>
    </div>

    <div id="profileModal" class="hidden fixed inset-0 bg-black/50 z-[9999] items-center justify-center p-4 backdrop-blur-sm transition-opacity">
        <div class="bg-white w-full max-w-[400px] rounded-[24px] shadow-2xl overflow-hidden modal-animate relative">
            <div class="bg-gradient-to-r from-[#1F3D68] to-[#2b538c] p-6 text-white flex justify-between items-center relative overflow-hidden">
                <div class="absolute -right-6 -top-6 text-white/10 text-9xl"><i class="fa-solid fa-shield-halved"></i></div>
                <div class="relative z-10 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-white text-[#1F3D68] flex items-center justify-center font-black text-xl shadow-md">
                        <?= strtoupper(substr($admin_data['username'] ?? 'A', 0, 1)) ?>
                    </div>
                    <div>
                        <h3 class="m-0 text-lg font-black tracking-wide">Rincian Akun</h3>
                        <p class="m-0 text-[11px] text-blue-200 font-bold uppercase tracking-widest mt-0.5">Sistem Administrator</p>
                    </div>
                </div>
                <button onclick="closeProfileModal()" class="relative z-10 w-8 h-8 flex items-center justify-center bg-white/10 hover:bg-white/20 hover:rotate-90 rounded-full transition duration-300">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <div class="p-8 flex flex-col gap-5 bg-gray-50/50">
                <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex flex-col gap-1.5">
                    <label class="text-[10px] font-extrabold text-gray-400 uppercase tracking-widest flex items-center gap-2"><i class="fa-regular fa-id-badge text-[#1F3D68]"></i> Nama Lengkap</label>
                    <div class="text-[#1F3D68] font-bold text-sm"><?= htmlspecialchars($admin_data['nama_lengkap'] ?? '-') ?></div>
                </div>
                <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex flex-col gap-1.5">
                    <label class="text-[10px] font-extrabold text-gray-400 uppercase tracking-widest flex items-center gap-2"><i class="fa-regular fa-user text-[#1F3D68]"></i> Username</label>
                    <div class="text-[#1F3D68] font-bold text-sm"><?= htmlspecialchars($admin_data['username'] ?? '-') ?></div>
                </div>
                <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex flex-col gap-1.5">
                    <label class="text-[10px] font-extrabold text-gray-400 uppercase tracking-widest flex items-center gap-2"><i class="fa-brands fa-whatsapp text-emerald-500"></i> Nomor WhatsApp</label>
                    <div class="text-[#1F3D68] font-mono text-sm font-bold"><?= htmlspecialchars($admin_data['no_hp'] ?? '-') ?></div>
                </div>
                <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex flex-col gap-1.5">
                    <label class="text-[10px] font-extrabold text-gray-400 uppercase tracking-widest flex items-center gap-2"><i class="fa-solid fa-lock text-[#1F3D68]"></i> Kata Sandi (Password)</label>
                    <div class="text-gray-400 font-bold text-lg tracking-[0.3em] mt-1 leading-none">••••••••</div>
                </div>
            </div>

            <div class="p-5 border-t border-gray-100 bg-white flex justify-end gap-3">
                <button onclick="closeProfileModal()" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-600 font-bold rounded-xl text-xs transition duration-200">
                    Tutup
                </button>
                <button onclick="openEditProfileModal()" class="px-5 py-2.5 bg-[#1F3D68] hover:bg-[#152a48] text-white font-bold rounded-xl text-xs shadow-md transition duration-200 flex items-center gap-2">
                    <i class="fa-solid fa-pen-to-square"></i> Edit Profil
                </button>
            </div>
        </div>
    </div>

    <div id="editProfileModal" class="hidden fixed inset-0 bg-black/50 z-[9999] items-center justify-center p-4 backdrop-blur-sm transition-opacity">
        <div class="bg-white w-full max-w-[450px] rounded-[24px] shadow-2xl overflow-hidden modal-animate relative">
            <div class="bg-gradient-to-r from-[#1F3D68] to-[#2b538c] p-6 text-white flex justify-between items-center">
                <h3 class="m-0 text-lg font-black tracking-wide flex items-center gap-2"><i class="fa-solid fa-user-pen"></i> Edit Profil Admin</h3>
                <button onclick="closeEditProfileModal()" class="w-8 h-8 flex items-center justify-center bg-white/10 hover:bg-white/20 hover:rotate-90 rounded-full transition duration-300">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form action="dashboard_admin.php" method="POST">
                <div class="p-8 flex flex-col gap-4 bg-gray-50/50">
                    <div>
                        <label class="text-[11px] font-extrabold text-gray-500 uppercase tracking-wider mb-1.5 block">Nama Lengkap</label>
                        <input type="text" name="nama_lengkap" value="<?= htmlspecialchars($admin_data['nama_lengkap'] ?? '') ?>" required class="w-full px-4 py-3 rounded-xl border border-gray-200 font-semibold focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 text-sm text-[#1F3D68]">
                    </div>
                    <div>
                        <label class="text-[11px] font-extrabold text-gray-500 uppercase tracking-wider mb-1.5 block">Username</label>
                        <input type="text" name="username" value="<?= htmlspecialchars($admin_data['username'] ?? '') ?>" required class="w-full px-4 py-3 rounded-xl border border-gray-200 font-semibold focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 text-sm text-[#1F3D68]">
                    </div>
                    <div>
                        <label class="text-[11px] font-extrabold text-gray-500 uppercase tracking-wider mb-1.5 block">Nomor WhatsApp</label>
                        <input type="text" name="no_hp" value="<?= htmlspecialchars($admin_data['no_hp'] ?? '') ?>" required class="w-full px-4 py-3 rounded-xl border border-gray-200 font-semibold focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 text-sm text-[#1F3D68]">
                    </div>
                    <div>
                        <label class="text-[11px] font-extrabold text-gray-500 uppercase tracking-wider mb-1.5 block">Kata Sandi Baru</label>
                        <input type="password" name="password" placeholder="Kosongkan jika tidak diubah" class="w-full px-4 py-3 rounded-xl border border-gray-200 font-semibold focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 text-sm text-[#1F3D68] placeholder-gray-400">
                        <span class="text-[10px] text-red-400 mt-1.5 block font-medium">*Isi hanya jika Anda ingin mengganti password login.</span>
                    </div>
                </div>

                <div class="p-5 border-t border-gray-100 bg-white flex justify-end gap-3">
                    <button type="button" onclick="closeEditProfileModal()" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-600 font-bold rounded-xl text-xs transition duration-200">
                        Batal
                    </button>
                    <button type="submit" name="update_akun" class="px-5 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white font-bold rounded-xl text-xs shadow-md transition duration-200 flex items-center gap-2">
                        <i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Fungsi Toggle Dropdown Menu
        function toggleProfileDropdown() {
            const dropdown = document.getElementById('profileDropdown');
            if (dropdown.classList.contains('hidden')) {
                dropdown.classList.remove('hidden');
            } else {
                dropdown.classList.add('hidden');
            }
        }

        // Buka Modal Profil (Read-Only)
        function openProfileModal() {
            document.getElementById('profileModal').classList.remove('hidden');
            document.getElementById('profileModal').classList.add('flex');
            document.getElementById('profileDropdown').classList.add('hidden'); 
        }

        // Tutup Modal Profil
        function closeProfileModal() {
            document.getElementById('profileModal').classList.add('hidden');
            document.getElementById('profileModal').classList.remove('flex');
        }

        // Buka Modal Edit Profil
        function openEditProfileModal() {
            closeProfileModal(); // Sembunyikan modal rincian profil dahulu
            document.getElementById('editProfileModal').classList.remove('hidden');
            document.getElementById('editProfileModal').classList.add('flex');
        }

        // Tutup Modal Edit Profil
        function closeEditProfileModal() {
            document.getElementById('editProfileModal').classList.add('hidden');
            document.getElementById('editProfileModal').classList.remove('flex');
        }

        // Menutup menu/modal otomatis jika user klik di luar area box
        window.addEventListener('click', function(e) {
            // Tutup dropdown jika area luarnya diklik
            if (!e.target.closest('.dropdown-container')) {
                const dropdown = document.getElementById('profileDropdown');
                if (dropdown && !dropdown.classList.contains('hidden')) {
                    dropdown.classList.add('hidden');
                }
            }
            
            // Tutup modal profil (box hitam luar)
            const modalProfile = document.getElementById('profileModal');
            if (e.target === modalProfile) closeProfileModal();

            // Tutup modal edit (box hitam luar)
            const modalEdit = document.getElementById('editProfileModal');
            if (e.target === modalEdit) closeEditProfileModal();
        });
    </script>
</body>
</html>
<?php
require_once '../config/database.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Menampung input (bisa berupa Email, Username, NIM, atau No HP)
    $login_input = trim($_POST['email']); 
    $password = $_POST['password']; 

    if (empty($login_input) || empty($password)) {
        header("Location: login.php?error=" . urlencode("Semua kolom wajib diisi"));
        exit();
    }

    try {
        // ==========================================
        // 1. CEK SEBAGAI ADMINISTRATOR
        // ==========================================
        $stmt = $pdo->prepare("SELECT * FROM administrator WHERE username = ?");
        $stmt->execute([$login_input]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($admin) {
            // Mendukung password_verify, MD5, atau Teks Biasa (Plain Text)
            if (password_verify($password, $admin['password']) || md5($password) === $admin['password'] || $password === $admin['password']) {
                
                $_SESSION['user_id'] = $admin['id_admin']; 
                $_SESSION['peran']   = 'admin';
                $_SESSION['nama']    = $admin['nama_lengkap'];
                
                header("Location: ../dashboard/admin/index.php"); 
                exit();
            }
        }

        // ==========================================
        // 2. CEK SEBAGAI PENGURUS ORGANISASI (Menggunakan No HP)
        // ==========================================
        $stmt = $pdo->prepare("SELECT * FROM pengurus_organisasi WHERE no_hp = ?");
        $stmt->execute([$login_input]);
        $pengurus = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($pengurus) {
            // PERBAIKAN: Mendukung password_verify ATAU Teks Biasa (Plain Text)
            if (password_verify($password, $pengurus['password']) || $password === $pengurus['password']) {
                
                // Cek status verifikasi pengurus
                if ($pengurus['status_verifikasi'] !== 'verified' && $pengurus['status_verifikasi'] !== 'Sudah') {
                    header("Location: login.php?error=" . urlencode("Akun pengurus belum diverifikasi oleh admin"));
                    exit();
                }

                $_SESSION['user_id']       = $pengurus['id_pengurus'];
                $_SESSION['peran']         = 'pengurus';
                $_SESSION['nama']          = $pengurus['nama_pengurus'];
                $_SESSION['id_organisasi'] = $pengurus['id_organisasi'];
                $_SESSION['jabatan']       = $pengurus['jabatan'];

                header("Location: ../dashboard/pengurus/index.php");
                exit();
            }
        }

        // ==========================================
        // 3. CEK SEBAGAI MAHASISWA
        // ==========================================
        $stmt = $pdo->prepare("SELECT * FROM tbmahasiswa WHERE email = ? OR nim = ?");
        $stmt->execute([$login_input, $login_input]);
        $mahasiswa = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($mahasiswa) {
            // PERBAIKAN: Mendukung password_verify ATAU Teks Biasa (Plain Text)
            if (password_verify($password, $mahasiswa['password']) || $password === $mahasiswa['password']) {
                
                // Cek status verifikasi akun
                if ($mahasiswa['is_verified'] === '0') {
                    header("Location: login.php?error=" . urlencode("Akun mahasiswa Anda belum melakukan verifikasi"));
                    exit();
                }

                $_SESSION['user_id'] = $mahasiswa['id_mahasiswa'];
                $_SESSION['peran']   = 'mahasiswa';
                $_SESSION['nama']    = $mahasiswa['nama'];
                $_SESSION['nim']     = $mahasiswa['nim'];

                header("Location: ../dashboard/mahasiswa/index.php");
                exit();
            }
        }

        // Jika gagal melewati semua pengecekan di atas
        header("Location: login.php?error=" . urlencode("Username/Email/No HP atau password salah"));
        exit();

    } catch (PDOException $e) {
        header("Location: login.php?error=" . urlencode("Terjadi kesalahan sistem: " . $e->getMessage()));
        exit();
    }
} else {
    header("Location: login.php");
    exit();
}
?>
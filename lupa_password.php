<?php
session_start();
require 'koneksi.php';

// Fungsi Kirim WA via Fonnte
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
            'Authorization: KjgzbjYq9r4TE32YtJvY' // GANTI DENGAN TOKEN FONNTE ANDA
        ),
    ));
    $response = curl_exec($curl);
    curl_close($curl);
    return $response;
}

$step = isset($_GET['step']) ? $_GET['step'] : 'request';
$role = isset($_GET['role']) ? $_GET['role'] : 'mahasiswa';
$error = '';
$success = '';

// ==========================================
// TAHAP 1: REQUEST OTP
// ==========================================
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['request_otp'])) {
    $identifier = trim($_POST['identifier']);
    $user = null;

    // Menyesuaikan query dengan struktur database asli
    if ($role == 'mahasiswa') {
        // Mahasiswa: Cek berdasarkan Email atau NIM
        $stmt = $pdo->prepare("SELECT id_mahasiswa as id, email, nama FROM tbmahasiswa WHERE email = ? OR nim = ?");
        $stmt->execute([$identifier, $identifier]);
        $user = $stmt->fetch();

    } elseif ($role == 'pengurus') {
        // Pengurus: Cek berdasarkan ID Akses
        $stmt = $pdo->prepare("SELECT id_pengurus as id, no_hp as kontak, nama FROM pengurus_organisasi WHERE id_akses = ?");
        $stmt->execute([$identifier]);
        $user = $stmt->fetch();

    } elseif ($role == 'admin') {
        // Admin: Cek berdasarkan ID Akses
        $stmt = $pdo->prepare("SELECT id_admin as id, no_hp as kontak, nama_lengkap as nama FROM administrator WHERE id_akses = ?");
        $stmt->execute([$identifier]);
        $user = $stmt->fetch();
    }

    if ($user) {
        $otp = rand(100000, 999999);
        $expires = date("Y-m-d H:i:s", strtotime('+15 minutes'));
    if ($role == 'mahasiswa') {
        $update = $pdo->prepare("UPDATE tbmahasiswa SET reset_token = ?, reset_expires = ? WHERE id_mahasiswa = ?");
    } elseif ($role == 'pengurus') {
        $update = $pdo->prepare("UPDATE pengurus_organisasi SET reset_token = ?, reset_expires = ? WHERE id_pengurus = ?");
    } else {
        $update = $pdo->prepare("UPDATE administrator SET reset_token = ?, reset_expires = ? WHERE id_admin = ?");
    }
    $update->execute([$otp, $expires, $user['id']]);
        
        // Simpan data reset ke Session sementara
        $_SESSION['reset_otp'] = $otp;
        $_SESSION['reset_id'] = $user['id'];
        $_SESSION['reset_role'] = $role;

        // Kirim OTP berdasarkan Role
        if ($role == 'mahasiswa') {
            require 'PHPMailer/src/Exception.php';
            require 'PHPMailer/src/PHPMailer.php';
            require 'PHPMailer/src/SMTP.php';
            
            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com'; 
                $mail->SMTPAuth   = true;
                $mail->Username   = 'adminmasagena@gmail.com'; 
                $mail->Password   = 'dwhh atlo qerk bccu';     
                $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                $mail->setFrom('adminmasagena@gmail.com', 'Admin Masagena ITH');
                $mail->addAddress($user['kontak'], $user['nama']);
                $mail->isHTML(true);
                $mail->Subject = 'Kode OTP Reset Password - MASAGENA ITH';
                $mail->Body    = "Halo <b>{$user['nama']}</b>,<br>Kode OTP untuk melakukan reset password Anda adalah: <h2 style='color:blue;'>{$otp}</h2>Jangan bagikan kode ini kepada siapapun.";
                $mail->send();
            } catch (Exception $e) {
                $error = "Gagal kirim email OTP.";
            }
        } else {
            // Kirim via WA (Admin & Pengurus) menggunakan nomor hp dari database
            $pesanWA = "Halo *{$user['nama']}*,\n\nBerikut adalah kode OTP untuk verifikasi Lupa Password akun Anda: *{$otp}*\n\nJangan berikan kode ini kepada siapapun demi keamanan akun.";
            kirimWA($user['kontak'], $pesanWA);
        }

        if(empty($error)) {
            header("Location: lupa_password.php?step=verify&role=" . $role);
            exit;
        }
    } else {
        $error = "Akun tidak ditemukan! Periksa kembali identitas yang dimasukkan.";
    }
}

// ==========================================
// TAHAP 2: VERIFIKASI OTP
// ==========================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['verify_otp'])) {
    if (isset($_SESSION['reset_otp']) && $_POST['otp'] == $_SESSION['reset_otp']) {
        header("Location: lupa_password.php?step=reset&role=" . $role);
        exit;
    } else {
        $error = "Kode OTP Salah atau Kadaluarsa!";
    }
}

// ==========================================
// TAHAP 3: RESET PASSWORD BARU
// ==========================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reset_password'])) {
    $pass1 = $_POST['pass1'];
    $pass2 = $_POST['pass2'];

    if ($pass1 === $pass2) {
        $new_hash = password_hash($pass1, PASSWORD_DEFAULT);
        $id = $_SESSION['reset_id'];
        $role_reset = $_SESSION['reset_role'];

        if ($role_reset == 'mahasiswa') {
            $stmt = $pdo->prepare("UPDATE tbmahasiswa SET password = ? WHERE id_mahasiswa = ?");
        } elseif ($role_reset == 'pengurus') {
            $stmt = $pdo->prepare("UPDATE pengurus_organisasi SET password = ? WHERE id_pengurus = ?");
        } elseif ($role_reset == 'admin') {
            $stmt = $pdo->prepare("UPDATE administrator SET password = ? WHERE id_admin = ?");
        }

        $stmt->execute([$new_hash, $id]);
        
        // Hapus session reset
        $target_redirect = ($role_reset == 'admin') ? 'login_admin.php' : 'login.php';
        unset($_SESSION['reset_otp'], $_SESSION['reset_id'], $_SESSION['reset_role']);
        
        echo "<script>alert('Password berhasil diperbarui! Silakan login kembali.'); window.location.href='{$target_redirect}';</script>";
        exit;
    } else {
        $error = "Konfirmasi password baru tidak cocok!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password - Masagena ITH</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">

    <div class="bg-white p-8 rounded-xl shadow-md w-full max-w-md mx-4">
        <h2 class="text-2xl font-bold text-center mb-2 text-[#1F3D68]">Lupa Password</h2>
        <p class="text-center text-xs font-bold text-gray-400 uppercase tracking-widest mb-6">Role: <?= $role ?></p>
        
        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 text-sm font-semibold text-center"><?= $error ?></div>
        <?php endif; ?>

        <?php if ($step == 'request'): ?>
            <p class="text-sm text-gray-600 mb-4 text-center">Masukkan identitas Anda untuk menerima OTP via <span class="font-bold text-blue-600"><?= ($role=='mahasiswa') ? 'Email Kelulusan/NIM' : 'WhatsApp' ?></span>.</p>
            <form method="POST">
                <input type="text" name="identifier" required placeholder="<?= ($role=='mahasiswa') ? 'Masukkan NIM atau Email' : (($role=='pengurus') ? 'Masukkan Nama Pengurus' : 'Masukkan Username Admin') ?>" class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-[#1F3D68] mb-4 text-sm font-semibold text-gray-700">
                <button type="submit" name="request_otp" class="w-full bg-[#1F3D68] text-white font-bold py-3 rounded-lg hover:bg-blue-900 transition text-xs tracking-wider">KIRIM KODE OTP</button>
            </form>

        <?php elseif ($step == 'verify'): ?>
            <p class="text-sm text-gray-600 mb-4 text-center">Masukkan 6 digit kode OTP yang dikirimkan ke kontak terdaftar Anda.</p>
            <form method="POST">
                <input type="number" name="otp" required placeholder="000000" class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-[#F59E0B] mb-4 text-center text-2xl font-bold tracking-widest text-gray-700">
                <button type="submit" name="verify_otp" class="w-full bg-[#F59E0B] text-white font-bold py-3 rounded-lg hover:bg-yellow-600 transition text-xs tracking-wider">VERIFIKASI OTP</button>
            </form>

        <?php elseif ($step == 'reset'): ?>
            <p class="text-sm text-gray-600 mb-4 text-center">Silakan masukkan password baru Anda.</p>
            <form method="POST">
                <input type="password" name="pass1" required placeholder="Password Baru" class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-green-600 mb-3 text-sm font-semibold">
                <input type="password" name="pass2" required placeholder="Ulangi Password Baru" class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-green-600 mb-4 text-sm font-semibold">
                <button type="submit" name="reset_password" class="w-full bg-green-600 text-white font-bold py-3 rounded-lg hover:bg-green-700 transition text-xs tracking-wider">SIMPAN PASSWORD BARU</button>
            </form>
        <?php endif; ?>

        <div class="text-center mt-6">
            <a href="<?= ($role == 'admin') ? 'login_admin.php' : 'login.php' ?>" class="text-xs text-gray-500 hover:text-red-500 hover:underline font-bold">Kembali ke Halaman Login</a>
        </div>
    </div>

</body>
</html>
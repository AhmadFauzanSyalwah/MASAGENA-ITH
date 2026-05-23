<?php
session_start();
require 'koneksi.php';

// Cek apakah ada sesi dari halaman login. Jika tidak ada, kembalikan ke login.
if (!isset($_SESSION['id_belum_verifikasi'])) {
    header("Location: index.php");
    exit;
}

$message = "";
$messageType = "";

// Proses ketika tombol diverifikasi ditekan
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['verify_otp'])) {
    $otp = $_POST['otp'];
    $id_mahasiswa = $_SESSION['id_belum_verifikasi'];

    if (!empty($otp)) {
        try {
            // Cek apakah OTP yang dimasukkan cocok
            $sql = "SELECT * FROM tbmahasiswa WHERE id_mahasiswa = :id AND verification_token = :otp LIMIT 1";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':id' => $id_mahasiswa,
                ':otp' => $otp
            ]);
            
            $user = $stmt->fetch();

            if ($user) {
                // Jika OTP benar, update status
                $updateSql = "UPDATE tbmahasiswa SET is_verified = '1', verification_token = NULL WHERE id_mahasiswa = :id";
                $updateStmt = $pdo->prepare($updateSql);
                $updateStmt->execute([':id' => $id_mahasiswa]);

                // Verifikasi sukses! Set session login utama
                $_SESSION['id_mahasiswa'] = $user['id_mahasiswa'];
                $_SESSION['nama'] = $user['nama'];
                $_SESSION['nim'] = $user['nim'];

                // Hapus sesi verifikasi sementara
                unset($_SESSION['id_belum_verifikasi']);

                // Arahkan ke halaman dashboard
                header("Location: dashboard.php");
                exit;
            } else {
                $message = "Kode OTP tidak valid atau salah!";
                $messageType = "error";
            }
        } catch(PDOException $e) {
            // Menangkap error database agar layar tidak blank putih
            $message = "Error Database: Pastikan kolom verification_token sudah ditambahkan. (" . $e->getMessage() . ")";
            $messageType = "error";
        }
    } else {
        $message = "Harap masukkan kode OTP!";
        $messageType = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Akun - MASAGENA ITH</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-[#ededed] min-h-screen flex flex-col relative z-0">

    <div class="absolute top-0 left-0 w-[22%] h-[55%] bg-[#1F3D68] z-[-1]"></div>
    <div class="absolute top-[55%] left-0 w-[22%] h-[45%] bg-[#F59E0B] z-[-1]"></div>

    <div class="flex-grow flex items-center justify-center p-4 md:p-8">
        
        <div class="bg-white rounded-[24px] shadow-[15px_15px_0px_0px_#d4d4d4] flex flex-col md:flex-row max-w-[900px] w-full min-h-[500px] overflow-hidden">
            
            <div class="md:w-[42%] flex flex-col">
                <div class="flex-grow bg-white flex items-center justify-center p-6 md:p-8">
                    <img src="asset/logo-masagena.png" alt="Masagena ITH Logo" class="w-[90%] max-w-[280px] object-contain">
                </div>
                <div class="bg-[#1F3D68] h-[35%] flex items-center justify-center p-6">
                    <div class="text-white text-[13px] text-center font-semibold leading-relaxed space-y-1.5">
                        <p>Media Akses Seputar</p>
                        <p>Agenda dan Kegiatan</p>
                        <p>Institut Teknologi</p>
                        <p>Bacharuddin Jusuf Habibie</p>
                    </div>
                </div>
            </div>

            <div class="md:w-[58%] px-12 py-10 flex flex-col justify-center bg-white">
                <div class="text-center mb-8">
                    <h2 class="text-[28px] font-bold text-[#1F3D68]">Verifikasi Akun</h2>
                    <div class="text-gray-500 text-[13px] mt-3 font-semibold leading-relaxed">
                        <p>Silahkan melakukan verifikasi</p>
                        <p>Dengan masukkan kode otp</p>
                        <p>Yang akan dikirim ke email</p>
                    </div>
                </div>

                <?php if($message != ""): ?>
                    <div class="<?= $messageType == 'success' ? 'bg-green-100 border-green-400 text-green-700' : 'bg-red-100 border-red-400 text-red-700' ?> border px-4 py-2 rounded-lg mb-6 text-center text-sm font-semibold">
                        <?= $message; ?>
                    </div>
                <?php endif; ?>

                <form action="" method="POST" class="space-y-6">
                    <div class="relative w-4/5 mx-auto">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fa-regular fa-user text-gray-500"></i>
                        </div>
                        <input type="text" name="otp" required placeholder="OTP" class="w-full pl-11 pr-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-[#1F3D68] text-sm font-bold text-gray-700 placeholder-gray-500 transition">
                    </div>

                    <div class="w-4/5 mx-auto mt-6">
                        <button type="submit" name="verify_otp" class="w-full bg-[#F59E0B] hover:bg-[#d98b09] text-white font-bold py-3.5 px-4 rounded-lg focus:outline-none text-xs transition duration-200 uppercase tracking-wide">
                            LOGIN
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="bg-[#1F3D68] text-white flex flex-col md:flex-row justify-between items-center px-8 py-4 w-full relative z-10 border-t-2 border-[#152e52]">
        <div class="text-xs font-semibold mb-2 md:mb-0">
            &copy; 2026 MASAGENA ITH. All rights reserved
        </div>
        <div class="font-bold text-lg tracking-widest flex items-center">
            MASAGENA &nbsp;<span class="text-[#F59E0B]">ITH</span>
        </div>
    </div>

</body>
</html>
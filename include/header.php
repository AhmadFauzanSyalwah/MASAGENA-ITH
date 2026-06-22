<?php
// include/header.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$peran = $_SESSION['peran'] ?? 'guest';
$nama  = $_SESSION['nama'] ?? '';
$level = $_SESSION['level'] ?? '';
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MASAGENA-ITH | <?= ucfirst($peran) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Montserrat:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="/masagena-ith/assets/css/style.css">
    <link rel="stylesheet" href="/masagena-ith/assets/css/pendaftaran-kegiatan.css">
    <link rel="stylesheet" href="/masagena-ith/assets/css/aspirasi.css">
</head>
<body>

<header class="main-header">
    <!-- BARIS ATAS: logo, search, profil -->
    <div class="header-top">
        <div class="brand-area">
            <a href="/MASAGENA-ITH/index.php" class="logo-link">
                <img src="/masagena-ith/assets/img/logo.png" alt="Logo MASAGENA-ITH" class="logo-img">
            </a>
            <div class="brand-text">
                <span class="brand-title">MASAGENA-ITH</span>
                <span class="brand-subtitle">Media Akses Seputar Agenda dan Kegiatan Mahasiswa<br>Institut Teknologi Bacharuddin Jusuf Habibie</span>
            </div>
        </div>

        <div class="search-area">
            <form action="/MASAGENA-ITH/pencarian.php" method="GET" class="search-form">
                <input type="text" name="q" placeholder="Cari agenda hari ini..." aria-label="Cari">
                <button type="submit"><i class="fas fa-search"></i></button>
            </form>
        </div>

        <div class="profile-area">
            <?php if ($peran !== 'guest'): ?>
                <div class="profile-dropdown">
                    <i class="fas fa-user-circle"></i>
                    <span><?= htmlspecialchars($nama) ?></span>
                    <div class="dropdown-content">
                        <!-- Data Diri (profil) -->
                        <a href="/MASAGENA-ITH/dashboard/<?= $peran ?>/profil.php"><i class="fas fa-id-card"></i> Data Diri</a>
                        <!-- Riwayat Kegiatan (pendaftaran) -->
                        <?php if ($peran === 'mahasiswa'): ?>
                            <a href="/MASAGENA-ITH/dashboard/mahasiswa/pendaftaran.php"><i class="fas fa-history"></i> Riwayat Kegiatan</a>
                        <?php elseif ($peran === 'pengurus'): ?>
                            <a href="/MASAGENA-ITH/dashboard/pengurus/pendaftaran.php"><i class="fas fa-history"></i> Riwayat Pendaftaran</a>
                        <?php else: ?>
                            <a href="/MASAGENA-ITH/dashboard/admin/kelola_user.php"><i class="fas fa-history"></i> Log Aktivitas</a>
                        <?php endif; ?>
                        <hr>               
                        <?php
                            // Menentukan link logout berdasarkan peran
                            $logout_link = ($peran === 'admin') ? "../../auth/logout_admin.php" : "../../auth/logout.php";
                        ?>
                        <a href="<?= $logout_link; ?>" onclick="return confirm('Apakah Anda yakin ingin keluar?');">
                            <i class="fa-solid fa-right-from-bracket"></i> Logout
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <a href="/MASAGENA-ITH/auth/login.php" class="btn-login"><i class="fas fa-key"></i> Login</a>
            <?php endif; ?>
        </div>
    </div>

    <!-- BARIS BAWAH: menu navigasi full-width -->
    <div class="header-bottom">
        <nav class="nav-area">
            <ul class="nav-menu">
                <?php if ($peran === 'mahasiswa'): ?>
                    <li><a href="/MASAGENA-ITH/dashboard/mahasiswa/index.php" class="<?= $current_page == 'index.php' ? 'active' : '' ?>">Beranda</a></li>
                    <li><a href="/MASAGENA-ITH/dashboard/mahasiswa/agenda.php">Agenda</a></li>
                    <li><a href="/MASAGENA-ITH/dashboard/mahasiswa/kegiatan.php">Kegiatan</a></li>
                    <li><a href="/MASAGENA-ITH/dashboard/mahasiswa/organisasi.php">Organisasi</a></li>
                    <li><a href="/MASAGENA-ITH/dashboard/mahasiswa/aspirasi.php">Aspirasi</a></li>

                <?php elseif ($peran === 'pengurus'): ?>
                    <li><a href="/MASAGENA-ITH/dashboard/pengurus/index.php" class="<?= $current_page == 'index.php' ? 'active' : '' ?>">Dashboard</a></li>
                    <li><a href="/MASAGENA-ITH/dashboard/pengurus/kelola_konten.php">Kelola Konten</a></li>
                    <li><a href="/MASAGENA-ITH/dashboard/pengurus/pendaftaran.php">Pendaftaran</a></li>
                    <li><a href="/MASAGENA-ITH/dashboard/pengurus/aspirasi_masuk.php">Aspirasi Masuk</a></li>
                    <?php if ($level === 'inti'): ?>
                        <li><a href="/MASAGENA-ITH/dashboard/pengurus/profil_organisasi.php">Profil Organisasi</a></li>
                        <li><a href="/MASAGENA-ITH/dashboard/pengurus/manajemen_pengurus.php">Manajemen Pengurus</a></li>
                    <?php endif; ?>

                <?php elseif ($peran === 'admin'): ?>
                    <li><a href="/MASAGENA-ITH/dashboard/admin/index.php" class="<?= $current_page == 'index.php' ? 'active' : '' ?>">Dashboard</a></li>
                    <li><a href="/MASAGENA-ITH/dashboard/admin/manajemen_organisasi.php">Manajemen Organisasi</a></li>
                    <li><a href="/MASAGENA-ITH/dashboard/admin/manajemen_pengurus.php">Manajemen Pengurus</a></li>
                    <li><a href="/MASAGENA-ITH/dashboard/admin/verifikasi_akun.php">Verifikasi Akun</a></li>
                    <li><a href="/MASAGENA-ITH/dashboard/admin/pengawasan_konten.php">Pengawasan Konten</a></li>
                    <li><a href="/MASAGENA-ITH/dashboard/admin/kelola_user.php">Kelola User</a></li>

                <?php else: // guest ?>
                    <li><a href="/MASAGENA-ITH/index.php" class="<?= $current_page == 'index.php' ? 'active' : '' ?>">Beranda</a></li>
                    <li><a href="/MASAGENA-ITH/agenda.php">Agenda</a></li>
                    <li><a href="/MASAGENA-ITH/organisasi.php">Organisasi</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</header>

<main class="main-container">
<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$peran = $_SESSION['peran'] ?? 'guest';
$nama  = $_SESSION['nama'] ?? '';
$level = $_SESSION['level'] ?? '';
$current_page = basename($_SERVER['PHP_SELF']);
$user_id = $_SESSION['user_id'] ?? null;

// ============================================================
// Tentukan apakah pengurus adalah inti
// ============================================================
$is_inti = ($level === 'Pengurus Inti' || $level === 'inti');

// ============================================================
// AMBIL INISIAL NAMA (2 huruf pertama)
// ============================================================
$inisial = strtoupper(substr($nama, 0, 2));

// Variabel context default
$page_context = $page_context ?? 'beranda';

// ============================================================
// GRUP HALAMAN UNTUK MENU AKTIF (MAHASISWA)
// ============================================================
$kegiatan_pages = ['kegiatan.php', 'detail_kegiatan.php', 'form_pendaftaran_kegiatan.php', 'daftar_kegiatan.php'];
$organisasi_pages = ['organisasi.php', 'detail_organisasi.php'];

// ============================================================
// GRUP HALAMAN UNTUK MENU AKTIF (PENGURUS)
// ============================================================
$profil_organisasi_pages = ['profil_organisasi.php', 'edit_profil_organisasi.php'];
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

    <style>
        /* =============================================
           OVERRIDE / TAMBAHAN UNTUK HEADER
           ============================================= */

        /* Profile Area - Klik toggle */
        .profile-area {
            position: relative;
            flex-shrink: 0;
        }

        /* Trigger profil (avatar + chevron) - TANPA NAMA */
        .profile-trigger {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            background-color: transparent;
            padding: 0.2rem 0.4rem 0.2rem 0.2rem;
            border-radius: 40px;
            cursor: pointer;
            color: var(--white);
            transition: background 0.2s;
            border: none;
            user-select: none;
        }
        .profile-trigger:hover {
            background-color: rgba(255, 255, 255, 0.08);
        }

        /* Avatar lingkaran KUNING dengan inisial */
        .profile-trigger .avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: var(--accent); /* #FFA007 */
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-weight: 700;
            font-size: 0.95rem;
            color: var(--primary); /* #071C34 */
            transition: transform 0.2s;
        }
        .profile-trigger:hover .avatar {
            transform: scale(1.05);
        }

        .profile-trigger .chevron {
            font-size: 0.7rem;
            color: rgba(255,255,255,0.6);
            transition: transform 0.3s;
            margin-left: 0.2rem;
        }
        .profile-trigger.open .chevron {
            transform: rotate(180deg);
        }

        /* Dropdown (muncul saat diklik) */
        .profile-dropdown-menu {
            display: none;
            position: absolute;
            top: calc(100% + 10px);
            right: 0;
            background: var(--white);
            border-radius: var(--radius-sm);
            box-shadow: var(--shadow-lg);
            min-width: 210px;
            padding: 0.5rem 0;
            z-index: 1001;
            border: 1px solid var(--border);
        }
        .profile-dropdown-menu.open {
            display: block;
        }

        .profile-dropdown-menu .dropdown-item {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            padding: 0.6rem 1.2rem;
            color: var(--primary);
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 500;
            transition: 0.2s;
            border: none;
            background: none;
            width: 100%;
            text-align: left;
            cursor: pointer;
        }
        .profile-dropdown-menu .dropdown-item:hover {
            background: var(--bg-body);
            color: var(--accent);
        }
        .profile-dropdown-menu .dropdown-item i {
            width: 20px;
            color: var(--text-muted);
            font-size: 1rem;
            text-align: center;
        }
        .profile-dropdown-menu .dropdown-item:hover i {
            color: var(--accent);
        }
        .profile-dropdown-menu .dropdown-divider {
            height: 1px;
            background: var(--border);
            margin: 0.3rem 1rem;
        }
        .profile-dropdown-menu .dropdown-item.logout {
            color: var(--danger);
        }
        .profile-dropdown-menu .dropdown-item.logout i {
            color: var(--danger);
        }
        .profile-dropdown-menu .dropdown-item.logout:hover {
            background: #fef2f2;
            color: var(--danger);
        }
        .profile-dropdown-menu .dropdown-item.logout:hover i {
            color: var(--danger);
        }

        /* Tombol Login (tetap dari style.css) */
        .btn-login {
            background-color: var(--accent);
            color: var(--primary);
            padding: 0.5rem 1.2rem;
            border-radius: 40px;
            text-decoration: none;
            font-weight: 600;
            transition: background 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        .btn-login:hover {
            background-color: var(--accent-dark);
        }

        /* Responsive untuk header */
        @media (max-width: 768px) {
            .header-top {
                flex-direction: column;
                align-items: stretch;
                padding: 0.6rem 1rem;
            }
            .brand-area {
                justify-content: center;
            }
            .brand-text .brand-subtitle {
                display: none;
            }
            .search-area {
                max-width: 100%;
                order: 3;
            }
            .profile-area {
                align-self: flex-end;
                margin-top: -2.8rem;
            }
            .profile-dropdown-menu {
                right: 0;
                left: auto;
            }
        }
        @media (max-width: 480px) {
            .brand-area .logo-img {
                height: 35px;
            }
            .brand-text .brand-title {
                font-size: 1rem;
            }
            .profile-trigger .avatar {
                width: 32px;
                height: 32px;
                font-size: 0.8rem;
            }
        }

        /* Search form - pakai style dari style.css */
        .search-form {
            display: flex;
            background-color: var(--white);
            border-radius: 50px;
            overflow: hidden;
            box-shadow: var(--shadow-sm);
        }
        .search-form input {
            flex: 1;
            padding: 0.6rem 1rem;
            border: none;
            font-size: 0.95rem;
            outline: none;
            font-family: 'Inter', sans-serif;
        }
        .search-form button {
            background-color: var(--accent);
            border: none;
            padding: 0 1.2rem;
            cursor: pointer;
            color: var(--primary);
            transition: background 0.3s;
        }
        .search-form button:hover {
            background-color: var(--accent-dark);
        }
    </style>
</head>
<body>

<header class="main-header">
    <!-- BARIS ATAS -->
    <div class="header-top">
        <!-- Brand -->
        <div class="brand-area">
            <a href="/MASAGENA-ITH/index.php" class="logo-link">
                <img src="/MASAGENA-ITH/assets/img/logo.png" alt="Logo MASAGENA-ITH" class="logo-img">
            </a>
            <div class="brand-text">
                <span class="brand-title">MASAGENA-ITH</span>
                <span class="brand-subtitle">Media Akses Seputar Agenda dan Kegiatan Mahasiswa<br>Institut Teknologi Bacharuddin Jusuf Habibie</span>
            </div>
        </div>

        <!-- Search -->
        <div class="search-area">
            <form action="" method="GET" class="search-form">
                <input type="hidden" name="context" value="<?= $page_context ?>">
                <input type="text" name="q" placeholder="Cari agenda, kegiatan, organisasi..." 
                       value="<?= isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '' ?>">
                <button type="submit"><i class="fas fa-search"></i></button>
            </form>
        </div>

        <!-- Profile Area -->
        <div class="profile-area" id="profileArea">
            <?php if ($peran !== 'guest'): ?>
                <!-- Trigger: avatar kuning + chevron -->
                <div class="profile-trigger" id="profileTrigger">
                    <div class="avatar">
                        <?= htmlspecialchars($inisial) ?>
                    </div>
                    <span class="chevron"><i class="fas fa-chevron-down"></i></span>
                </div>

                <!-- Dropdown Menu -->
                <div class="profile-dropdown-menu" id="profileDropdown">
                    <a href="/MASAGENA-ITH/dashboard/<?= $peran ?>/profil.php" class="dropdown-item">
                        <i class="fas fa-id-card"></i> Data Diri
                    </a>
                    <?php if ($peran === 'mahasiswa'): ?>
                        <a href="/MASAGENA-ITH/dashboard/mahasiswa/pendaftaran.php" class="dropdown-item">
                            <i class="fas fa-history"></i> Riwayat Kegiatan
                        </a>
                    <?php elseif ($peran === 'pengurus'): ?>
                        <a href="/MASAGENA-ITH/dashboard/pengurus/pendaftaran.php" class="dropdown-item">
                            <i class="fas fa-history"></i> Riwayat Pendaftaran
                        </a>
                    <?php else: ?>
                        <a href="/MASAGENA-ITH/dashboard/admin/kelola_user.php" class="dropdown-item">
                            <i class="fas fa-history"></i> Log Aktivitas
                        </a>
                    <?php endif; ?>
                    <div class="dropdown-divider"></div>
                    <?php
                        $logout_link = ($peran === 'admin') ? "../../auth/logout_admin.php" : "../../auth/logout.php";
                    ?>
                    <a href="<?= $logout_link; ?>" class="dropdown-item logout" onclick="return confirm('Apakah Anda yakin ingin keluar?');">
                        <i class="fa-solid fa-right-from-bracket"></i> Logout
                    </a>
                </div>

            <?php else: ?>
                <a href="/MASAGENA-ITH/auth/login.php" class="btn-login"><i class="fas fa-key"></i> Login</a>
            <?php endif; ?>
        </div>
    </div>

    <!-- BARIS BAWAH: navigasi -->
    <div class="header-bottom">
        <div class="nav-area">
            <ul class="nav-menu">
                <?php if ($peran === 'mahasiswa'): ?>
                    <li><a href="/MASAGENA-ITH/dashboard/mahasiswa/index.php" class="<?= ($current_page == 'index.php') ? 'active' : '' ?>">Beranda</a></li>
                    <li><a href="/MASAGENA-ITH/dashboard/mahasiswa/agenda.php" class="<?= ($current_page == 'agenda.php') ? 'active' : '' ?>">Agenda</a></li>
                    <li><a href="/MASAGENA-ITH/dashboard/mahasiswa/kegiatan.php" class="<?= in_array($current_page, $kegiatan_pages) ? 'active' : '' ?>">Kegiatan</a></li>
                    <li><a href="/MASAGENA-ITH/dashboard/mahasiswa/organisasi.php" class="<?= in_array($current_page, $organisasi_pages) ? 'active' : '' ?>">Organisasi</a></li>
                    <li><a href="/MASAGENA-ITH/dashboard/mahasiswa/aspirasi.php" class="<?= ($current_page == 'aspirasi.php') ? 'active' : '' ?>">Aspirasi</a></li>

                <?php elseif ($peran === 'pengurus'): ?>
                    <!-- ===== MENU UNTUK SEMUA PENGURUS ===== -->
                    <li><a href="/MASAGENA-ITH/dashboard/pengurus/index.php" class="<?= ($current_page == 'index.php') ? 'active' : '' ?>">Dashboard</a></li>
                    <li><a href="/MASAGENA-ITH/dashboard/pengurus/kelola_konten.php" class="<?= ($current_page == 'kelola_konten.php') ? 'active' : '' ?>">Kelola Konten</a></li>
                    <li><a href="/MASAGENA-ITH/dashboard/pengurus/pendaftaran.php" class="<?= ($current_page == 'pendaftaran.php') ? 'active' : '' ?>">Pendaftaran</a></li>
                    <li><a href="/MASAGENA-ITH/dashboard/pengurus/kelola_aspirasi.php" class="<?= ($current_page == 'kelola_aspirasi.php') ? 'active' : '' ?>">Aspirasi Masuk</a></li>
                    <li><a href="/MASAGENA-ITH/dashboard/pengurus/profil_organisasi.php" class="<?= (in_array($current_page, $profil_organisasi_pages)) ? 'active' : '' ?>">Profil Organisasi</a></li>

                    <!-- ===== MENU KHUSUS PENGURUS INTI ===== -->
                    <?php if ($is_inti): ?>
                        <li><a href="/MASAGENA-ITH/dashboard/pengurus/manajemen_pengurus.php" class="<?= ($current_page == 'manajemen_pengurus.php') ? 'active' : '' ?>">Manajemen Pengurus</a></li>
                    <?php endif; ?>

                <?php elseif ($peran === 'admin'): ?>
                    <li><a href="/MASAGENA-ITH/dashboard/admin/index.php" class="<?= ($current_page == 'index.php') ? 'active' : '' ?>">Dashboard</a></li>
                    <li><a href="/MASAGENA-ITH/dashboard/admin/manajemen_organisasi.php" class="<?= ($current_page == 'manajemen_organisasi.php') ? 'active' : '' ?>">Manajemen Organisasi</a></li>
                    <li><a href="/MASAGENA-ITH/dashboard/admin/manajemen_pengurus.php" class="<?= ($current_page == 'manajemen_pengurus.php') ? 'active' : '' ?>">Manajemen Pengurus</a></li>
                    <li><a href="/MASAGENA-ITH/dashboard/admin/verifikasi_akun.php" class="<?= ($current_page == 'verifikasi_akun.php') ? 'active' : '' ?>">Verifikasi Akun</a></li>
                    <li><a href="/MASAGENA-ITH/dashboard/admin/pengawasan_konten.php" class="<?= ($current_page == 'pengawasan_konten.php') ? 'active' : '' ?>">Pengawasan Konten</a></li>
                    <li><a href="/MASAGENA-ITH/dashboard/admin/kelola_user.php" class="<?= ($current_page == 'kelola_user.php') ? 'active' : '' ?>">Kelola User</a></li>

                <?php else: ?>
                    <li><a href="/MASAGENA-ITH/index.php" class="<?= ($current_page == 'index.php') ? 'active' : '' ?>">Beranda</a></li>
                    <li><a href="/MASAGENA-ITH/agenda.php" class="<?= ($current_page == 'agenda.php') ? 'active' : '' ?>">Agenda</a></li>
                    <li><a href="/MASAGENA-ITH/organisasi.php" class="<?= ($current_page == 'organisasi.php') ? 'active' : '' ?>">Organisasi</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</header>

<main class="main-container">

<script>
// =============================================
// TOGGLE PROFILE DROPDOWN ON CLICK
// =============================================
document.addEventListener('DOMContentLoaded', function() {
    const trigger = document.getElementById('profileTrigger');
    const dropdown = document.getElementById('profileDropdown');
    const profileArea = document.getElementById('profileArea');

    if (trigger && dropdown) {
        trigger.addEventListener('click', function(e) {
            e.stopPropagation();
            dropdown.classList.toggle('open');
            trigger.classList.toggle('open');
        });

        document.addEventListener('click', function(e) {
            if (!profileArea.contains(e.target)) {
                dropdown.classList.remove('open');
                trigger.classList.remove('open');
            }
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                dropdown.classList.remove('open');
                trigger.classList.remove('open');
            }
        });
    }
});
</script>
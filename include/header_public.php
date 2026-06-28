<?php
// include/header_public.php - HEADER FINAL (menu kecil, tidak hilang)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$current_page = basename($_SERVER['PHP_SELF']);
$peran = $_SESSION['peran'] ?? 'guest';
$nama = $_SESSION['nama'] ?? '';

$nav_links = [
    '#beranda' => 'Beranda',
    '#tentang' => 'Tentang',
    '#layanan' => 'Layanan',
    '#fitur' => 'Fitur',
    '#teknologi' => 'Teknologi',
    '#tim' => 'Tim',
    '#kontak' => 'Kontak',
    '#syarat' => 'Syarat & Ketentuan',
    '#faq' => 'FAQ', // Tambahkan ini
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MASAGENA-ITH</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Montserrat:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="/masagena-ith/assets/css/style.css">
    <style>
        /* ============================================================
           HEADER - MENU KECIL, TIDAK HILANG
           ============================================================ */
        .public-header {
            background: #071C34;
            border-bottom: 2px solid #FFA007;
            position: sticky;
            top: 0;
            z-index: 1000;
            padding: 0.7rem 2rem;
        }
        .public-header .container {
            max-width: 1300px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 0.5rem 1rem;
        }

        /* ===== LOGO ===== */
        .public-header .logo {
            display: flex;
            align-items: center;
            gap: 0.7rem;
            text-decoration: none;
            flex-shrink: 0;
        }
        .public-header .logo img {
            height: 40px;
            width: auto;
            object-fit: contain;
        }
        .public-header .logo .brand-text .title {
            font-family: 'Montserrat', sans-serif;
            font-weight: 700;
            font-size: 1rem;
            color: #FFA007;
        }
        .public-header .logo .brand-text .sub {
            font-size: 0.5rem;
            color: rgba(255,255,255,0.4);
        }

        /* ===== MENU (TEKS KECIL) ===== */
        .public-header .nav-wrapper {
            display: flex;
            align-items: center;
            flex: 1;
            justify-content: center;
            flex-wrap: wrap;
            gap: 0.3rem;
        }

        .public-header .nav-menu {
            display: flex;
            align-items: center;
            gap: 0.1rem;
            list-style: none;
            margin: 0;
            padding: 0;
            flex-wrap: wrap;
        }
        .public-header .nav-menu li a {
            display: block;
            padding: 0.25rem 0.7rem;
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            font-size: 0.78rem; /* lebih kecil */
            font-weight: 500;
            white-space: nowrap;
            transition: color 0.15s ease;
        }
        .public-header .nav-menu li a:hover {
            color: #FFA007;
        }
        .public-header .nav-menu li a.active {
            color: #ffffff;
            font-weight: 600;
        }

        /* ===== HAMBURGER ===== */
        .public-header .hamburger {
            display: none;
            background: none;
            border: none;
            color: #fff;
            font-size: 1.4rem;
            cursor: pointer;
            padding: 0.2rem 0.5rem;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 1024px) {
            .public-header .nav-menu li a {
                font-size: 0.72rem;
                padding: 0.2rem 0.5rem;
            }
        }

        @media (max-width: 992px) {
            .public-header {
                padding: 0.5rem 1rem;
            }
            .public-header .container {
                flex-wrap: wrap;
            }
            .public-header .nav-wrapper {
                order: 3;
                width: 100%;
                display: none;
                flex-direction: column;
                align-items: stretch;
                background: #071C34;
                padding: 0.5rem 0;
                border-top: 1px solid rgba(255,255,255,0.05);
                margin-top: 0.3rem;
                gap: 0.3rem;
            }
            .public-header .nav-wrapper.open {
                display: flex;
            }
            .public-header .nav-menu {
                flex-direction: column;
                align-items: stretch;
                gap: 0.1rem;
                width: 100%;
            }
            .public-header .nav-menu li a {
                padding: 0.5rem 1rem;
                text-align: center;
                font-size: 0.9rem;
            }
            .public-header .hamburger {
                display: block;
                order: 2;
            }
            .public-header .logo {
                order: 1;
            }
        }

        @media (max-width: 480px) {
            .public-header .logo img {
                height: 32px;
            }
            .public-header .logo .brand-text .title {
                font-size: 0.85rem;
            }
            .public-header .logo .brand-text .sub {
                display: none;
            }
        }
    </style>
</head>
<body>
<header class="public-header">
    <div class="container">
        <!-- Logo -->
        <a href="/MASAGENA-ITH/index.php" class="logo">
            <img src="/masagena-ith/assets/img/logo.png" alt="MASAGENA-ITH">
            <div class="brand-text">
                <span class="title">MASAGENA-ITH</span>
                <span class="sub">Media Akses Agenda &amp; Kegiatan Mahasiswa</span>
            </div>
        </a>

        <!-- Hamburger -->
        <button class="hamburger" onclick="toggleMenu()">
            <i class="fas fa-bars"></i>
        </button>

        <!-- Navigasi -->
        <div class="nav-wrapper" id="navWrapper">
            <ul class="nav-menu" id="navMenu">
                <?php foreach ($nav_links as $href => $label): ?>
                    <li><a href="<?= $href ?>" class="<?= ($current_page == basename($href)) ? 'active' : '' ?>"><?= $label ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</header>

<script>
// ============================================================
// 1. TOGGLE HAMBURGER
// ============================================================
function toggleMenu() {
    document.getElementById('navWrapper').classList.toggle('open');
}
document.querySelectorAll('.nav-menu a').forEach(function(link) {
    link.addEventListener('click', function() {
        document.getElementById('navWrapper').classList.remove('open');
    });
});

// ============================================================
// 2. ACTIVE MENU BERDASARKAN SCROLL (tidak pernah hilang)
// ============================================================
document.addEventListener('DOMContentLoaded', function() {
    var path = window.location.pathname;
    var isIndex = path === '/' || path === '/index.php' || path.endsWith('/MASAGENA-ITH/') || path.endsWith('/MASAGENA-ITH/index.php');
    if (!isIndex) return;

    var sections = document.querySelectorAll('section[id]');
    var navLinks = document.querySelectorAll('.nav-menu a');

    if (sections.length === 0 || navLinks.length === 0) return;

    // Fungsi untuk menentukan section aktif
    function getActiveSection() {
        var scrollPos = window.scrollY + 130;
        var activeId = 'beranda';

        sections.forEach(function(section) {
            var top = section.offsetTop;
            var height = section.offsetHeight;
            var id = section.getAttribute('id');

            // Jika posisi scroll berada di dalam section atau di atasnya
            if (scrollPos >= top && scrollPos < top + height) {
                activeId = id;
            }
            // Jika scroll sudah melewati section, tetap gunakan section terakhir yang dilewati
            if (scrollPos >= top + height) {
                activeId = id;
            }
        });

        return activeId;
    }

    // Fungsi update menu
    function updateActiveMenu() {
        var activeId = getActiveSection();

        navLinks.forEach(function(link) {
            link.classList.remove('active');
            if (link.getAttribute('href') === '#' + activeId) {
                link.classList.add('active');
            }
        });
    }

    // Jalankan saat scroll dengan throttle
    var ticking = false;
    window.addEventListener('scroll', function() {
        if (!ticking) {
            window.requestAnimationFrame(function() {
                updateActiveMenu();
                ticking = false;
            });
            ticking = true;
        }
    });

    // Jalankan pertama kali
    updateActiveMenu();

    // Jalankan ulang saat resize (agar offset tetap akurat)
    var resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            updateActiveMenu();
        }, 100);
    });
});
</script>

<main class="main-container" style="margin-top:80px;">
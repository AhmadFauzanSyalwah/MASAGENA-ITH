<?php
if (session_status() == PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config/database.php';

// Tentukan dashboard URL sesuai peran
$dashboard_url = BASE_URL . '/dashboard/mahasiswa.php';
if (isset($_SESSION['peran'])) {
    if ($_SESSION['peran'] === 'admin') {
        $dashboard_url = BASE_URL . '/dashboard/admin.php';
    } elseif ($_SESSION['peran'] === 'pengurus') {
        $dashboard_url = BASE_URL . '/dashboard/pengurus.php';
    }
}

// Cek halaman saat ini untuk menentukan active
$current_page = basename($_SERVER['PHP_SELF']);
$active_dashboard = false;
if (isset($_SESSION['peran'])) {
    if ($_SESSION['peran'] === 'mahasiswa' && $current_page === 'mahasiswa.php') $active_dashboard = true;
    elseif ($_SESSION['peran'] === 'pengurus' && $current_page === 'pengurus.php') $active_dashboard = true;
    elseif ($_SESSION['peran'] === 'admin' && $current_page === 'admin.php') $active_dashboard = true;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MASAGENA-ITH</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #1B4C85;
            --accent: #FFA007;
        }
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .navbar { box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .navbar-brand img { height: 40px; }
        .navbar-nav .nav-link {
            font-weight: 500;
            color: var(--primary) !important;
            border-radius: 8px;
            padding: 0.5rem 1rem;
            transition: all 0.2s ease;
        }
        .navbar-nav .nav-link:hover {
            color: var(--accent) !important;
            background-color: transparent;
            transform: translateY(-1px);
        }
        .navbar-nav .nav-link.active {
            background-color: var(--primary);
            color: white !important;
            transform: translateY(-1px);
        }
        .btn-outline-primary {
            border-color: var(--primary);
            color: var(--primary);
        }
        .btn-outline-primary:hover {
            background: var(--primary);
            color: white;
        }
        .profile-link {
            color: #6c757d;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }
        .profile-link:hover {
            color: var(--primary);
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg bg-white sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold" href="<?= $dashboard_url ?>">
            <img src="<?= BASE_URL ?>/assets/img/logo.png" alt="Logo">
            MASAGENA<span style="color: var(--accent);">-ITH</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto me-3">
                <li class="nav-item">
                    <a class="nav-link <?= $active_dashboard ? 'active' : '' ?>" href="<?= $dashboard_url ?>">Beranda</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>/kegiatan/index.php">Kegiatan</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>/organisasi/index.php">Organisasi</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link disabled" href="#">Aspirasi</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>/lomba/index.php">Lomba</a>
                </li>
            </ul>
            <div class="d-flex align-items-center">
                <a href="<?= BASE_URL ?>/profil.php" class="profile-link me-3 d-none d-md-block">
                    <?= htmlspecialchars($_SESSION['nama'] ?? 'Profil') ?>
                </a>
                <a href="<?= BASE_URL ?>/auth/logout.php" class="btn btn-outline-primary btn-sm rounded-pill">Keluar</a>
            </div>
        </div>
    </div>
</nav>
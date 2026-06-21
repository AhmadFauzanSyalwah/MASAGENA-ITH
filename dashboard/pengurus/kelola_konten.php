<?php
// dashboard/pengurus/kelola_konten.php
session_start();

// 1. SISTEM KEAMANAN & AUTENTIKASI (Menggunakan file bawaan kelompok)
require_once '../../config/session_check.php';
require_once '../../config/database.php';

// Pastikan yang mengakses halaman ini benar-benar user dengan peran pengurus
if ($_SESSION['peran'] != 'pengurus') {
    header("Location: ../" . $_SESSION['peran'] . "/index.php");
    exit();
}

// Mengambil data organisasi dan tingkatan level pengurus dari session OTP
$id_organisasi = $_SESSION['id_organisasi'];
$level         = $_SESSION['level'] ?? 'biasa'; // Jika tidak ada, default ke 'biasa'

// 2. QUERY DATABASE (Mengambil konten kegiatan khusus untuk organisasi ini saja)
$stmt = $pdo->prepare("
    SELECT id_konten, judul, tanggal_kegiatan, status_publikasi, kategori 
    FROM konten_kegiatan 
    WHERE id_organisasi = ? 
    ORDER BY created_at DESC
");
$stmt->execute([$id_organisasi]);
$semua_konten = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Memanggil komponen header template aplikasi
include '../../include/header.php';
?>

<div class="container" style="padding: 2rem; background: white; border-radius: 12px; box-shadow: var(--shadow-sm); margin-top: 1rem;">
    
    <div class="header-title" style="margin-bottom: 1.5rem;">
        <h2 style="border-left: 4px solid var(--accent); padding-left: 0.75rem; margin-bottom: 0.5rem;">Kelola Semua Konten Kegiatan</h2>
        <p style="color: var(--text-muted); margin: 0; font-size: 0.9rem;">
            Tingkatan Anda: <strong><?= $level == 'inti' ? 'Pengurus Inti (Akses Penuh)' : 'Pengurus Biasa (Akses Terbatas)' ?></strong>
        </p>
    </div>
    
    <div style="margin-bottom: 1.5rem;">
        <a href="form_tambah.php" class="btn" style="background: var(--accent); color: white; padding: 0.5rem 1rem; border-radius: 6px; text-decoration: none; font-weight: 500; display: inline-block;">
            ➕ Tambah Kegiatan Baru
        </a>
    </div>

    <?php if (count($semua_konten) > 0): ?>
        <table style="width: 100%; border-collapse: collapse; text-align: left; margin-top: 1rem;">
            <thead>
                <tr style="border-bottom: 2px solid var(--border); background: #f8f9fa;">
                    <th style="padding: 0.75rem;">Judul Kegiatan</th>
                    <th style="padding: 0.75rem;">Kategori</th>
                    <th style="padding: 0.75rem;">Tanggal Pelaksanaan</th>
                    <th style="padding: 0.75rem;">Status Publikasi</th>
                    <th style="padding: 0.75rem; text-align: center;">Aksi Konten</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($semua_konten as $k): ?>
                    <tr style="border-bottom: 1px solid var(--border);">
                        <td style="padding: 0.75rem;"><strong><?= htmlspecialchars($k['judul']) ?></strong></td>
                        <td style="padding: 0.75rem;"><span class="badge" style="background: #eccc68; padding: 2px 8px; border-radius: 4px; font-size: 0.8rem;"><?= ucfirst($k['kategori']) ?></span></td>
                        <td style="padding: 0.75rem;"><?= date('d/m/Y', strtotime($k['tanggal_kegiatan'])) ?></td>
                        <td style="padding: 0.75rem;">
                            <span style="color: <?= $k['status_publikasi'] == 'publish' ? '#2ed573' : '#ffa502'; ?>; font-weight: bold;">
                                <?= ucfirst($k['status_publikasi']) ?>
                            </span>
                        </td>
                        <td style="padding: 0.75rem; text-align: center;">
                            <a href="edit_konten.php?id=<?= $k['id_konten'] ?>" style="text-decoration: none; color: var(--primary); margin-right: 15px; font-weight: 500;">📝 Edit</a>
                            
                            <?php if ($level === 'inti'): ?>
                                <a href="proses_hapus.php?id=<?= $k['id_konten'] ?>" 
                                   onclick="return confirm('Apakah Anda yakin ingin menghapus konten [<?= htmlspecialchars($k['judul']) ?>] secara permanen?')" 
                                   style="text-decoration: none; color: #ff4757; font-weight: bold;">
                                   🗑️ Hapus
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div style="text-align: center; padding: 2rem; color: var(--text-muted);">
            <p>Belum ada data konten kegiatan yang dibuat untuk organisasi Anda.</p>
        </div>
    <?php endif; ?>
    
    <div style="margin-top: 2rem; border-top: 1px solid var(--border); padding-top: 1rem;">
        <a href="index.php" style="text-decoration: none; color: var(--text-muted); font-size: 0.9rem;">&larr; Kembali ke Dashboard Utama</a>
    </div>
</div>

<?php 
// Memanggil komponen footer template aplikasi
include '../../include/footer.php'; 
?>
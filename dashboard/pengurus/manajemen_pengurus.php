<?php
// dashboard/pengurus/manajemen_pengurus.php

require_once '../../config/session_check.php';
require_once '../../config/database.php';

// 🔒 GEMBOK KEAMANAN: Cek apakah dia pengurus, dan apakah dia Pengurus Inti?
if ($_SESSION['peran'] != 'pengurus' || $_SESSION['level'] != 'Pengurus Inti') {
    // Jika levelnya cuma "Pengurus Departemen", tendang balik ke index!
    header("Location: index.php");
    exit();
}

$id_organisasi = $_SESSION['id_organisasi'];

// Ambil data semua pengurus di organisasi yang sama
$stmt = $pdo->prepare("
    SELECT id_pengurus, nama_pengurus, jabatan, level, no_hp, status_verifikasi 
    FROM pengurus_organisasi 
    WHERE id_organisasi = ? 
    ORDER BY level ASC, nama_pengurus ASC
");
$stmt->execute([$id_organisasi]);
$daftar_pengurus = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../../include/header.php';
?>

<div class="dashboard-welcome" style="margin-bottom: 2rem;">
    <h1>Manajemen Pengurus</h1>
    <p>Kelola anggota kepengurusan khusus untuk organisasi Anda.</p>
</div>

<div style="background: white; border-radius: 12px; padding: 1.5rem; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h3 style="margin: 0; border-left: 4px solid #F59E0B; padding-left: 10px;">Daftar Pengurus</h3>
        <button style="background-color: #1F3D68; color: white; border: none; padding: 8px 16px; border-radius: 6px; cursor: pointer;">
            <i class="fas fa-plus"></i> Tambah Pengurus
        </button>
    </div>

    <table style="width: 100%; border-collapse: collapse; text-align: left;">
        <thead>
            <tr style="background-color: #f3f4f6; border-bottom: 2px solid #e5e7eb;">
                <th style="padding: 12px;">No</th>
                <th style="padding: 12px;">Nama Pengurus</th>
                <th style="padding: 12px;">Jabatan</th>
                <th style="padding: 12px;">Tingkatan (Level)</th>
                <th style="padding: 12px;">No HP</th>
                <th style="padding: 12px;">Status</th>
                <th style="padding: 12px;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($daftar_pengurus) > 0): ?>
                <?php $no = 1; foreach ($daftar_pengurus as $p): ?>
                    <tr style="border-bottom: 1px solid #e5e7eb;">
                        <td style="padding: 12px;"><?= $no++ ?></td>
                        <td style="padding: 12px; font-weight: 600;"><?= htmlspecialchars($p['nama_pengurus']) ?></td>
                        <td style="padding: 12px;"><?= htmlspecialchars($p['jabatan']) ?></td>
                        <td style="padding: 12px;">
                            <?php if ($p['level'] == 'Pengurus Inti'): ?>
                                <span style="background-color: #dbeafe; color: #1e40af; padding: 4px 8px; border-radius: 4px; font-size: 0.8rem; font-weight: bold;">Inti</span>
                            <?php else: ?>
                                <span style="background-color: #f3f4f6; color: #4b5563; padding: 4px 8px; border-radius: 4px; font-size: 0.8rem;">Departemen</span>
                            <?php endif; ?>
                        </td>
                        <td style="padding: 12px;"><?= htmlspecialchars($p['no_hp'] ?: '-') ?></td>
                        <td style="padding: 12px;">
                            <?php if ($p['status_verifikasi'] == 'Terverifikasi'): ?>
                                <span style="color: green; font-weight: 600; font-size: 0.85rem;"><i class="fas fa-check-circle"></i> Aktif</span>
                            <?php else: ?>
                                <span style="color: red; font-weight: 600; font-size: 0.85rem;"><i class="fas fa-times-circle"></i> Belum Terverifikasi</span>
                            <?php endif; ?>
                        </td>
                        <td style="padding: 12px;">
                            <a href="#" style="color: #3b82f6; margin-right: 10px;" title="Edit"><i class="fas fa-edit"></i></a>
                            <a href="#" style="color: #ef4444;" title="Hapus"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" style="text-align: center; padding: 20px; color: #6b7280;">Tidak ada data pengurus ditemukan.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include '../../include/footer.php'; ?>
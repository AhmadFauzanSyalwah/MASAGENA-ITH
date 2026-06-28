<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../include/components.php';
require_once __DIR__ . '/../../include/pendaftaran-helper.php';
require_once '../../config/session_check.php';

$nim = trim($_GET['nim'] ?? '');
$email = trim($_GET['email'] ?? '');
$data = [];
$pesan = '';

if ($nim !== '' && $email !== '') {
    try {
        $stmt = $pdo->prepare("
            SELECT
                p.id_pendaftaran,
                p.tanggal_daftar,
                p.status_pendaftaran,
                m.nama,
                m.nim,
                m.prodi,
                m.kontak,
                m.email,
                k.judul,
                k.tanggal_kegiatan,
                k.kategori
            FROM pendaftaran p
            JOIN tbmahasiswa m ON m.id_mahasiswa = p.id_mahasiswa
            JOIN konten_kegiatan k ON k.id_konten = p.id_konten
            WHERE m.nim = ?
            AND m.email = ?
            ORDER BY p.tanggal_daftar DESC
        ");

        $stmt->execute([$nim, $email]);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($data) === 0) {
            $pesan = 'Data pendaftaran tidak ditemukan. Pastikan NIM dan email sesuai dengan data mahasiswa.';
        }
    } catch (PDOException $e) {
        die('Query cek status gagal: ' . $e->getMessage());
    }
}

require_once __DIR__ . '/../../include/header.php';
?>

<!-- Banner Welcome seperti halaman organisasi -->
<div class="dashboard-welcome">
    <h1>Status Pendaftaran</h1>
    <p>Pantau dan cek status persetujuan dari setiap kegiatan yang telah Anda daftarkan di sini.</p>
</div>

<!-- KONTEN UTAMA -->
<div class="main-container">
    <div class="main-content">
        
        <!-- Heading Halaman -->
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 2rem;">
            <h2 style="margin: 0; font-size: 1.5rem; border-left: 4px solid var(--accent); padding-left: 0.75rem;">Cek Status Pendaftaran</h2>
            <a href="kegiatan.php" class="btn-sm" style="background-color: var(--primary); color: var(--white); text-decoration: none;">Kembali ke Kegiatan &rarr;</a>
        </div>

        <?php if (isset($_GET['success'])) { ?>
            <div class="alert alert-success" style="background-color: #dcfce7; color: var(--success); border-left: 4px solid var(--success); padding: 1rem; border-radius: var(--radius-sm); margin-bottom: 1.5rem; font-weight: 500;">
                Pendaftaran berhasil dikirim. Status awal masih menunggu.
            </div>
        <?php } ?>

        <!-- Form Pencarian dengan style Card -->
        <form method="GET" action="cek_status_pendaftaran.php" class="card" style="margin-bottom: 2rem;">
            <label style="display: block; margin-bottom: 1rem; font-weight: 600; color: var(--primary);">Masukkan NIM dan email mahasiswa yang terdaftar</label>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; align-items: center;">
                <input type="text" name="nim" value="<?= h($nim); ?>" placeholder="Masukkan NIM..." required style="width: 100%; padding: 0.8rem 1rem; border: 1px solid var(--border); border-radius: var(--radius-sm); font-family: inherit; font-size: 0.95rem; outline: none;">
                <input type="email" name="email" value="<?= h($email); ?>" placeholder="Masukkan Email..." required style="width: 100%; padding: 0.8rem 1rem; border: 1px solid var(--border); border-radius: var(--radius-sm); font-family: inherit; font-size: 0.95rem; outline: none;">
                <button type="submit" class="btn" style="padding: 0.8rem 1.2rem; white-space: nowrap; font-size: 0.95rem;">Cek Status</button>
            </div>
        </form>

        <!-- Hasil Pencarian -->
        <?php if ($nim === '' || $email === '') { ?>
            <div class="card" style="text-align: center; padding: 4rem 2rem;">
                <h3 style="color: var(--primary); margin-bottom: 0.5rem;">Belum ada data yang dicek</h3>
                <p style="color: var(--text-muted); margin: 0;">Masukkan NIM dan email di atas untuk melihat status pendaftaran kegiatan.</p>
            </div>
        <?php } elseif ($pesan !== '') { ?>
            <div class="card" style="text-align: center; padding: 4rem 2rem;">
                <h3 style="color: var(--primary); margin-bottom: 0.5rem;">Data tidak ditemukan</h3>
                <p style="color: var(--text-muted); margin: 0;"><?= h($pesan); ?></p>
            </div>
        <?php } else { ?>
            <div class="card" style="padding: 0; overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; text-align: left; min-width: 700px;">
                    <thead>
                        <tr style="background-color: var(--primary); color: var(--white);">
                            <th style="padding: 1rem; font-weight: 500;">No</th>
                            <th style="padding: 1rem; font-weight: 500;">Kegiatan</th>
                            <th style="padding: 1rem; font-weight: 500;">Tanggal Kegiatan</th>
                            <th style="padding: 1rem; font-weight: 500;">Kategori</th>
                            <th style="padding: 1rem; font-weight: 500;">Tanggal Daftar</th>
                            <th style="padding: 1rem; font-weight: 500;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data as $index => $row) { ?>
                            <tr style="border-bottom: 1px solid var(--border);">
                                <td style="padding: 1rem;"><?= $index + 1; ?></td>
                                <td style="padding: 1rem;">
                                    <strong><?= h($row['judul']); ?></strong><br>
                                    <small style="color: var(--text-muted);"><?= h($row['nama']); ?> / <?= h($row['nim']); ?></small>
                                </td>
                                <td style="padding: 1rem; color: var(--text-muted); font-size: 0.95rem;"><?= h(rupiah_date($row['tanggal_kegiatan'])); ?></td>
                                <td style="padding: 1rem; color: var(--text-muted); font-size: 0.95rem;"><?= h($row['kategori'] ?: '-'); ?></td>
                                <td style="padding: 1rem; color: var(--text-muted); font-size: 0.95rem;"><?= h(rupiah_date($row['tanggal_daftar'])); ?></td>
                                <td style="padding: 1rem;"><?= pendaftaran_status_badge($row['status_pendaftaran']); ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        <?php } ?>
        
    </div>
</div>

<?php
require_once __DIR__ . '/../../include/footer.php';
?>
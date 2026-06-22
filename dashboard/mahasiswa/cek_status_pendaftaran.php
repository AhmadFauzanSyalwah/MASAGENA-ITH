<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../include/components.php';
require_once __DIR__ . '/../../include/pendaftaran-helper.php';

$nim = trim($_GET['nim'] ?? '');
$email = trim($_GET['email'] ?? '');
$data = [];
$pesan = '';

if ($nim !== '' && $email !== '') {
    $stmt = mysqli_prepare($conn, "
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

    if (!$stmt) {
        die('Prepare cek status gagal: ' . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, 'ss', $nim, $email);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }

    mysqli_stmt_close($stmt);

    if (count($data) === 0) {
        $pesan = 'Data pendaftaran tidak ditemukan. Pastikan NIM dan email sesuai dengan data mahasiswa.';
    }
}

require_once __DIR__ . '/../../include/header.php';
?>

<div class="page-light">
    <section class="table-page">
        <div class="table-heading">
            <div>
                <p class="eyebrow">Status Pendaftaran</p>
                <h2>Cek Status Pendaftaran</h2>
            </div>

            <a href="kegiatan.php" class="btn-outline wide-small">Kembali ke Kegiatan</a>
        </div>

        <?php if (isset($_GET['success'])) { ?>
            <div class="alert alert-success">
                Pendaftaran berhasil dikirim. Status awal masih menunggu.
            </div>
        <?php } ?>

        <form method="GET" action="cek_status_pendaftaran.php" class="search-box status-search-box">
            <label>Masukkan NIM dan email mahasiswa yang terdaftar</label>

            <div class="status-search-grid">
                <input 
                    type="text" 
                    name="nim" 
                    value="<?= h($nim); ?>" 
                    placeholder="NIM" 
                    required
                >

                <input 
                    type="email" 
                    name="email" 
                    value="<?= h($email); ?>" 
                    placeholder="Email" 
                    required
                >

                <button type="submit" class="btn-primary">Cek Status</button>
            </div>
        </form>

        <?php if ($nim === '' || $email === '') { ?>
            <div class="empty-state compact">
                <h3>Belum ada data yang dicek</h3>
                <p>Masukkan NIM dan email untuk melihat status pendaftaran kegiatan.</p>
            </div>

        <?php } elseif ($pesan !== '') { ?>
            <div class="empty-state compact">
                <h3>Data tidak ditemukan</h3>
                <p><?= h($pesan); ?></p>
            </div>

        <?php } else { ?>
            <div class="table-card">
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kegiatan</th>
                            <th>Tanggal Kegiatan</th>
                            <th>Kategori</th>
                            <th>Tanggal Daftar</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($data as $index => $row) { ?>
                            <tr>
                                <td><?= $index + 1; ?></td>

                                <td>
                                    <strong><?= h($row['judul']); ?></strong><br>
                                    <small><?= h($row['nama']); ?> / <?= h($row['nim']); ?></small>
                                </td>

                                <td><?= h(rupiah_date($row['tanggal_kegiatan'])); ?></td>
                                <td><?= h($row['kategori'] ?: '-'); ?></td>
                                <td><?= h(rupiah_date($row['tanggal_daftar'])); ?></td>
                                <td><?= pendaftaran_status_badge($row['status_pendaftaran']); ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        <?php } ?>
    </section>
</div>

<?php
require_once __DIR__ . '/../../include/footer.php';
?>
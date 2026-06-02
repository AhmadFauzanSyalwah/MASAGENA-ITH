<?php
require_once 'connection.php';
require_once 'components.php';

$nim = trim($_GET['nim'] ?? '');
$email = trim($_GET['email'] ?? '');
$data = [];
$pesan = '';

if ($nim !== '' && $email !== '') {
    $stmt = mysqli_prepare($conn, "
        SELECT
            p.id_pendaftaran_kegiatan,
            p.nama_lengkap,
            p.nim,
            p.program_studi,
            p.no_hp,
            p.email,
            p.catatan_tambahan,
            p.tanggal_daftar,
            p.status_pendaftaran,
            k.judul_kegiatan,
            k.tanggal_kegiatan,
            k.lokasi
        FROM pendaftaran_kegiatan p
        JOIN konten_kegiatan k ON k.id_konten = p.id_konten
        WHERE p.nim = ?
        AND p.email = ?
        ORDER BY p.tanggal_daftar DESC
    ");
    mysqli_stmt_bind_param($stmt, 'ss', $nim, $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }

    mysqli_stmt_close($stmt);

    if (count($data) === 0) {
        $pesan = 'Data pendaftaran tidak ditemukan. Pastikan NIM dan email yang dimasukkan sama dengan data saat mendaftar.';
    }
}

render_header('Cek Status Pendaftaran - MASAGENA-ITH', 'pengumuman');
?>

<main class="page-light">
    <section class="table-page">
        <div class="table-heading">
            <div>
                <p class="eyebrow">Status Pendaftaran</p>
                <h2>Cek Status Pendaftaran</h2>
            </div>
            <a href="kegiatan.php" class="btn-outline wide-small">Kembali ke Kegiatan</a>
        </div>

        <?php if (isset($_GET['success'])) { ?>
            <div class="alert alert-success">Pendaftaran berhasil dikirim. Status awal masih pending.</div>
        <?php } ?>

        <form method="GET" class="search-box status-search-box">
            <label>Masukkan data yang sama seperti saat mendaftar</label>
            <div class="status-search-grid">
                <input type="text" name="nim" value="<?= h($nim); ?>" placeholder="NIM" required>
                <input type="email" name="email" value="<?= h($email); ?>" placeholder="Email" required>
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
                            <th>Lokasi</th>
                            <th>Tanggal Daftar</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data as $index => $row) { ?>
                            <tr>
                                <td><?= $index + 1; ?></td>
                                <td>
                                    <strong><?= h($row['judul_kegiatan']); ?></strong><br>
                                    <small><?= h($row['nama_lengkap']); ?> / <?= h($row['nim']); ?></small>
                                </td>
                                <td><?= h(rupiah_date($row['tanggal_kegiatan'])); ?></td>
                                <td><?= h($row['lokasi'] ?: '-'); ?></td>
                                <td><?= h(rupiah_date($row['tanggal_daftar'])); ?></td>
                                <td><?= status_badge($row['status_pendaftaran']); ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        <?php } ?>
    </section>
</main>

<?php render_footer(); ?>

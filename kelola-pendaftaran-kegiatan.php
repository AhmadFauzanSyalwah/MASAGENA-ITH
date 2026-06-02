<?php
require_once 'connection.php';
require_once 'components.php';

$status = $_GET['status'] ?? 'semua';
$allowedStatus = ['semua', 'pending', 'diterima', 'ditolak'];
if (!in_array($status, $allowedStatus, true)) {
    $status = 'semua';
}

if ($status === 'semua') {
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
            k.judul_kegiatan
        FROM pendaftaran_kegiatan p
        JOIN konten_kegiatan k ON k.id_konten = p.id_konten
        ORDER BY p.tanggal_daftar DESC
    ");
} else {
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
            k.judul_kegiatan
        FROM pendaftaran_kegiatan p
        JOIN konten_kegiatan k ON k.id_konten = p.id_konten
        WHERE p.status_pendaftaran = ?
        ORDER BY p.tanggal_daftar DESC
    ");
    mysqli_stmt_bind_param($stmt, 's', $status);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

render_header('Kelola Pendaftaran Kegiatan - MASAGENA-ITH', 'pengumuman');
?>

<main class="page-light">
    <section class="table-page wide">
        <div class="table-heading">
            <div>
                <p class="eyebrow">Pengurus / Admin</p>
                <h2>Kelola Pendaftaran Kegiatan</h2>
            </div>
            <a href="kegiatan.php" class="btn-outline wide-small">Lihat Kegiatan</a>
        </div>

        <?php if (isset($_GET['updated'])) { ?>
            <div class="alert alert-success">Status pendaftaran berhasil diperbarui.</div>
        <?php } ?>

        <form method="GET" class="filter-tabs">
            <button name="status" value="semua" class="<?= $status === 'semua' ? 'active' : ''; ?>">Semua</button>
            <button name="status" value="pending" class="<?= $status === 'pending' ? 'active' : ''; ?>">Pending</button>
            <button name="status" value="diterima" class="<?= $status === 'diterima' ? 'active' : ''; ?>">Diterima</button>
            <button name="status" value="ditolak" class="<?= $status === 'ditolak' ? 'active' : ''; ?>">Ditolak</button>
        </form>

        <div class="table-card">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Mahasiswa</th>
                        <th>Kontak</th>
                        <th>Kegiatan</th>
                        <th>Prodi</th>
                        <th>Tanggal Daftar</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($result) === 0) { ?>
                        <tr>
                            <td colspan="8" class="text-center">Belum ada data pendaftaran.</td>
                        </tr>
                    <?php } ?>

                    <?php $no = 1; while ($row = mysqli_fetch_assoc($result)) { ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td>
                                <strong><?= h($row['nama_lengkap']); ?></strong><br>
                                <small>NIM: <?= h($row['nim']); ?></small>
                            </td>
                            <td>
                                <small><?= h($row['no_hp']); ?></small><br>
                                <small><?= h($row['email']); ?></small>
                            </td>
                            <td><?= h($row['judul_kegiatan']); ?></td>
                            <td><?= h($row['program_studi']); ?></td>
                            <td><?= h(rupiah_date($row['tanggal_daftar'])); ?></td>
                            <td><?= status_badge($row['status_pendaftaran']); ?></td>
                            <td>
                                <div class="action-inline">
                                    <form action="update-status-pendaftaran.php" method="POST">
                                        <input type="hidden" name="id" value="<?= (int) $row['id_pendaftaran_kegiatan']; ?>">
                                        <input type="hidden" name="status" value="diterima">
                                        <button type="submit" class="mini-btn accept">Terima</button>
                                    </form>

                                    <form action="update-status-pendaftaran.php" method="POST">
                                        <input type="hidden" name="id" value="<?= (int) $row['id_pendaftaran_kegiatan']; ?>">
                                        <input type="hidden" name="status" value="ditolak">
                                        <button type="submit" class="mini-btn reject">Tolak</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </section>
</main>

<?php
mysqli_stmt_close($stmt);
render_footer();
?>

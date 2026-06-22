<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../include/components.php';
require_once __DIR__ . '/../../include/pendaftaran-helper.php';

$status = $_GET['status'] ?? 'semua';

$allowedStatus = ['semua', 'menunggu', 'diterima', 'ditolak'];

if (!in_array($status, $allowedStatus, true)) {
    $status = 'semua';
}

if ($status === 'semua') {
    $stmt = mysqli_prepare($conn, "
        SELECT
            p.id_pendaftaran,
            p.tanggal_daftar,
            p.status_pendaftaran,
            p.kuota_maks,
            m.nama,
            m.nim,
            m.prodi,
            m.kontak,
            m.email,
            k.judul
        FROM pendaftaran p
        JOIN tbmahasiswa m 
            ON m.id_mahasiswa = p.id_mahasiswa
        JOIN konten_kegiatan k 
            ON k.id_konten = p.id_konten
        ORDER BY p.tanggal_daftar DESC
    ");
} else {
    $stmt = mysqli_prepare($conn, "
        SELECT
            p.id_pendaftaran,
            p.tanggal_daftar,
            p.status_pendaftaran,
            p.kuota_maks,
            m.nama,
            m.nim,
            m.prodi,
            m.kontak,
            m.email,
            k.judul
        FROM pendaftaran p
        JOIN tbmahasiswa m 
            ON m.id_mahasiswa = p.id_mahasiswa
        JOIN konten_kegiatan k 
            ON k.id_konten = p.id_konten
        WHERE p.status_pendaftaran = ?
        ORDER BY p.tanggal_daftar DESC
    ");
}

if (!$stmt) {
    die('Prepare kelola pendaftaran gagal: ' . mysqli_error($conn));
}

if ($status !== 'semua') {
    mysqli_stmt_bind_param($stmt, 's', $status);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

require_once __DIR__ . '/../../include/header.php';
?>

<div class="page-light">
    <section class="table-page wide">
        <div class="table-heading">
            <div>
                <p class="eyebrow">Pengurus / Admin</p>
                <h2>Kelola Pendaftaran Kegiatan</h2>
            </div>

            <a href="../mahasiswa/kegiatan.php" class="btn-outline wide-small">
                Lihat Kegiatan
            </a>
        </div>

        <?php if (isset($_GET['updated'])) { ?>
            <div class="alert alert-success">
                Status pendaftaran berhasil diperbarui.
            </div>
        <?php } ?>

        <?php if (isset($_GET['error'])) { ?>
            <div class="alert alert-error">
                Aksi tidak valid atau data pendaftaran tidak ditemukan.
            </div>
        <?php } ?>

        <form method="GET" action="kelola_pendaftaran_kegiatan.php" class="filter-tabs">
            <button 
                type="submit"
                name="status" 
                value="semua" 
                class="<?= $status === 'semua' ? 'active' : ''; ?>"
            >
                Semua
            </button>

            <button 
                type="submit"
                name="status" 
                value="menunggu" 
                class="<?= $status === 'menunggu' ? 'active' : ''; ?>"
            >
                Menunggu
            </button>

            <button 
                type="submit"
                name="status" 
                value="diterima" 
                class="<?= $status === 'diterima' ? 'active' : ''; ?>"
            >
                Diterima
            </button>

            <button 
                type="submit"
                name="status" 
                value="ditolak" 
                class="<?= $status === 'ditolak' ? 'active' : ''; ?>"
            >
                Ditolak
            </button>
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
                    <?php if (!$result || mysqli_num_rows($result) === 0) { ?>
                        <tr>
                            <td colspan="8" class="text-center">
                                Belum ada data pendaftaran.
                            </td>
                        </tr>
                    <?php } ?>

                    <?php $no = 1; ?>
                    <?php while ($result && $row = mysqli_fetch_assoc($result)) { ?>
                        <tr>
                            <td><?= $no++; ?></td>

                            <td>
                                <strong><?= h($row['nama']); ?></strong><br>
                                <small>NIM: <?= h($row['nim']); ?></small>
                            </td>

                            <td>
                                <small><?= h($row['kontak'] ?: '-'); ?></small><br>
                                <small><?= h($row['email']); ?></small>
                            </td>

                            <td><?= h($row['judul']); ?></td>

                            <td><?= h($row['prodi'] ?: '-'); ?></td>

                            <td><?= h(rupiah_date($row['tanggal_daftar'])); ?></td>

                            <td>
                                <?= pendaftaran_status_badge($row['status_pendaftaran']); ?>
                            </td>

                            <td>
                                <div class="action-inline">
                                    <form action="update_status_pendaftaran.php" method="POST">
                                        <input 
                                            type="hidden" 
                                            name="id" 
                                            value="<?= (int) $row['id_pendaftaran']; ?>"
                                        >

                                        <input 
                                            type="hidden" 
                                            name="status" 
                                            value="diterima"
                                        >

                                        <button type="submit" class="mini-btn accept">
                                            Terima
                                        </button>
                                    </form>

                                    <form action="update_status_pendaftaran.php" method="POST">
                                        <input 
                                            type="hidden" 
                                            name="id" 
                                            value="<?= (int) $row['id_pendaftaran']; ?>"
                                        >

                                        <input 
                                            type="hidden" 
                                            name="status" 
                                            value="ditolak"
                                        >

                                        <button 
                                            type="submit" 
                                            class="mini-btn reject"
                                            onclick="return confirm('Yakin ingin menolak pendaftaran ini?');"
                                        >
                                            Tolak
                                        </button>
                                    </form>

                                    <form action="update_status_pendaftaran.php" method="POST">
                                        <input 
                                            type="hidden" 
                                            name="id" 
                                            value="<?= (int) $row['id_pendaftaran']; ?>"
                                        >

                                        <input 
                                            type="hidden" 
                                            name="status" 
                                            value="menunggu"
                                        >

                                        <button type="submit" class="mini-btn">
                                            Reset
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </section>
</div>

<?php
mysqli_stmt_close($stmt);
require_once __DIR__ . '/../../include/footer.php';
?>
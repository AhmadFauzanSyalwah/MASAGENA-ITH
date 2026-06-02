<?php
require_once 'connection.php';
require_once 'components.php';

$id_konten = isset($_GET['id_konten']) ? (int) $_GET['id_konten'] : 0;
$mahasiswa = current_mahasiswa($conn);

$selected = null;
if ($id_konten > 0) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM konten_kegiatan WHERE id_konten = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, 'i', $id_konten);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $selected = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
}

$kegiatanQuery = mysqli_query($conn, "
    SELECT
        k.id_konten,
        k.judul_kegiatan,
        k.tanggal_kegiatan,
        k.kuota,
        COUNT(p.id_pendaftaran_kegiatan) AS jumlah_peserta
    FROM konten_kegiatan k
    LEFT JOIN pendaftaran_kegiatan p
        ON p.id_konten = k.id_konten
        AND p.status_pendaftaran != 'ditolak'
    GROUP BY k.id_konten
    ORDER BY k.tanggal_upload DESC
");

render_header('Form Pendaftaran Kegiatan - MASAGENA-ITH', 'pengumuman');
?>

<main class="registration-page">
    <section class="registration-shell">
        <aside class="registration-info">
            <h2>Ayo Bergabung!</h2>
            <p>Pastikan data yang kamu masukkan valid untuk mempermudah proses verifikasi nantinya.</p>

            <div class="info-image">
                <img src="assets/img/form-pendaftaran.png" alt="Form Pendaftaran Kegiatan">
            </div>

            <h3><?= h($selected['judul_kegiatan'] ?? 'Pilih kegiatan yang ingin kamu ikuti'); ?></h3>
            <p class="info-bottom">
                <?= h($selected ? short_text($selected['isi_kegiatan'], 130) : 'Pilih kegiatan pada formulir, lalu kirim data pendaftaran.'); ?>
            </p>
        </aside>

        <section class="registration-form-area">
            <?php if (isset($_GET['error'])) { ?>
                <div class="alert alert-error"><?= h($_GET['error']); ?></div>
            <?php } ?>

            <form action="proses-pendaftaran-kegiatan.php" method="POST" class="registration-form">
                <div class="form-row two-col">
                    <div class="form-group">
                        <label>Nama Lengkap</label>
                        <input type="text" name="nama_lengkap" value="<?= h($mahasiswa['nama'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label>NIM</label>
                        <input type="text" name="nim" value="<?= h($mahasiswa['nim'] ?? ''); ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Program Studi</label>
                        <input type="text" name="program_studi" placeholder="Contoh: Ilmu Komputer" required>
                    </div>
                </div>

                <div class="form-row two-col">
                    <div class="form-group">
                        <label>No HP (WhatsApp)</label>
                        <input type="text" name="no_hp" placeholder="08xxxxxxxxxx" required>
                    </div>

                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" value="<?= h($mahasiswa['email'] ?? ''); ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Pilih Kegiatan</label>
                        <select name="id_konten" required>
                            <option value="">-- Pilih Kegiatan --</option>
                            <?php while ($row = mysqli_fetch_assoc($kegiatanQuery)) { ?>
                                <?php
                                    $kuota = (int) $row['kuota'];
                                    $jumlah = (int) $row['jumlah_peserta'];
                                    $penuh = $kuota > 0 && $jumlah >= $kuota;
                                ?>
                                <option value="<?= (int) $row['id_konten']; ?>"
                                    <?= $id_konten === (int) $row['id_konten'] ? 'selected' : ''; ?>
                                    <?= $penuh ? 'disabled' : ''; ?>>
                                    <?= h($row['judul_kegiatan']); ?> <?= $penuh ? '(Kuota Penuh)' : ''; ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Catatan Tambahan</label>
                        <textarea name="catatan_tambahan" rows="6" placeholder="Opsional"></textarea>
                    </div>
                </div>

                <div class="form-actions centered">
                    <button type="submit" class="btn-primary submit-btn">Submit Pendaftaran</button>
                </div>
            </form>
        </section>
    </section>
</main>

<?php render_footer(); ?>

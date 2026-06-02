<?php
include 'connection.php';
include 'components.php';

render_header('Kirim Aspirasi dan Kritik', 'aspirasi');
$mahasiswa = active_mahasiswa($conn);
$schemaReady = aspirasi_schema_ready($conn);

$organisasi = mysqli_query($conn, "SELECT id_organisasi, nama_organisasi FROM organisasi ORDER BY nama_organisasi ASC");
?>

<main class="page-light">
    <section class="section-heading-row">
        <div>
            <h2>Aspirasi dan Kritik</h2>
            <div class="heading-line"></div>
            <p class="lead-text">Sampaikan masukan, kritik, keluhan, atau saran kepada organisasi kampus.</p>
        </div>
        <div class="heading-actions">
            <a href="cek-status-aspirasi.php" class="btn-heading btn-status">Cek Status</a>
            <a href="aspirasi-saya.php" class="btn-heading">Aspirasi Saya</a>
        </div>
    </section>

    <?php if (!$schemaReady) { schema_warning(); } ?>

    <section class="form-shell">
        <div class="info-panel">
            <div class="info-icon">!</div>
            <h3>Catatan Alur</h3>
            <p>Status awal aspirasi adalah <strong>proses</strong>. Setelah pengurus/admin meninjau, status dapat berubah menjadi <strong>selesai</strong> atau <strong>ditolak</strong>.</p>
            <p>Jika memilih anonim, identitas pengirim tidak ditampilkan dan aspirasi dicek memakai kode aspirasi.</p>

            <?php if ($mahasiswa) { ?>
                <div class="mini-profile">
                    <span>Mode mahasiswa</span>
                    <strong><?= h($mahasiswa['nama']); ?></strong>
                    <small><?= h($mahasiswa['nim']); ?> · <?= h($mahasiswa['email']); ?></small>
                </div>
            <?php } else { ?>
                <div class="mini-profile warning">
                    <span>Data mahasiswa belum ada</span>
                    <strong>Isi tabel tbmahasiswa dulu</strong>
                    <small>Atau sambungkan dengan login mahasiswa.</small>
                </div>
            <?php } ?>
        </div>

        <form class="aspirasi-form" action="proses-aspirasi.php" method="POST">
            <label>Organisasi Tujuan</label>
            <select name="id_organisasi">
                <option value="">Umum / Semua Organisasi</option>
                <?php if ($organisasi) { while ($org = mysqli_fetch_assoc($organisasi)) { ?>
                    <option value="<?= (int) $org['id_organisasi']; ?>"><?= h($org['nama_organisasi']); ?></option>
                <?php }} ?>
            </select>

            <label>Kategori</label>
            <select name="kategori" required>
                <option value="">Pilih kategori</option>
                <option value="Kritik">Kritik</option>
                <option value="Saran">Saran</option>
                <option value="Keluhan">Keluhan</option>
                <option value="Apresiasi">Apresiasi</option>
                <option value="Lainnya">Lainnya</option>
            </select>

            <label>Judul Aspirasi</label>
            <input type="text" name="judul" placeholder="Contoh: Perbaikan informasi kegiatan organisasi" maxlength="255" required>

            <label>Isi Aspirasi / Kritik</label>
            <textarea name="isi_aspirasi" rows="8" placeholder="Tulis aspirasi secara jelas dan spesifik..." required></textarea>

            <label class="checkbox-row">
                <input type="checkbox" name="is_anonim" value="1">
                <span>Kirim sebagai anonim</span>
            </label>

            <button type="submit" class="btn-submit" <?= (!$schemaReady || !$mahasiswa) ? 'disabled' : ''; ?>>Kirim Aspirasi</button>
        </form>
    </section>
</main>

<?php render_footer(); ?>

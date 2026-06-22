<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../include/components.php';
require_once __DIR__ . '/../../include/pendaftaran-helper.php';

$id_konten = isset($_GET['id_konten']) ? (int) $_GET['id_konten'] : 0;
$mahasiswa = pendaftaran_current_mahasiswa($conn);

if (!$mahasiswa) {
    $mahasiswa = mysqli_fetch_assoc(
        mysqli_query(
            $conn,
            "SELECT id_mahasiswa,nim,nama,prodi,kontak,email 
             FROM tbmahasiswa 
             ORDER BY id_mahasiswa ASC 
             LIMIT 1"
        )
    );
}
$defaultKuota = (int) pendaftaran_default_kuota();

$selected = null;

if ($id_konten > 0) {
    $stmt = mysqli_prepare($conn, "
        SELECT id_konten, judul, deskripsi, tanggal_kegiatan, kategori, lampiran
        FROM konten_kegiatan
        WHERE id_konten = ?
        AND status_publikasi = 'publish'
        LIMIT 1
    ");

    if (!$stmt) {
        die('Prepare detail kegiatan gagal: ' . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, 'i', $id_konten);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $selected = mysqli_fetch_assoc($result);

    mysqli_stmt_close($stmt);
}

$kegiatanQuery = mysqli_query($conn, "
    SELECT
        k.id_konten,
        k.judul,
        k.tanggal_kegiatan,
        COALESCE(
            (
                SELECT MAX(NULLIF(p2.kuota_maks, 0))
                FROM pendaftaran p2
                WHERE p2.id_konten = k.id_konten
            ),
            $defaultKuota
        ) AS kuota,
        (
            SELECT COUNT(*)
            FROM pendaftaran p
            WHERE p.id_konten = k.id_konten
            AND p.status_pendaftaran != 'ditolak'
        ) AS jumlah_peserta
    FROM konten_kegiatan k
    WHERE k.status_publikasi = 'publish'
    ORDER BY k.created_at DESC
");

if (!$kegiatanQuery) {
    die('Query kegiatan gagal: ' . mysqli_error($conn));
}

require_once __DIR__ . '/../../include/header.php';
?>

<div class="registration-page">
    <section class="registration-shell">
        <aside class="registration-info">
            <h2>Ayo Bergabung!</h2>


            <div class="info-image">
                <img src="/masagena-ith/assets/img/form_pendaftaran.png" alt="Form Pendaftaran Kegiatan">
            </div>

            <h3><?= h($selected['judul'] ?? 'Pilih kegiatan yang ingin kamu ikuti'); ?></h3>

            <p class="info-bottom">
                <?= h($selected ? short_text($selected['deskripsi'], 130) : 'Pilih kegiatan pada formulir, lalu kirim data pendaftaran.'); ?>
            </p>
        </aside>

        <section class="registration-form-area">
            <?php if (isset($_GET['error'])) { ?>
                <div class="alert alert-error"><?= h($_GET['error']); ?></div>
            <?php } ?>

            <form action="proses_pendaftaran_kegiatan.php" method="POST" class="registration-form">
                <div class="form-row two-col">
                    <div class="form-group">
                        <label>Nama Mahasiswa</label>
                        <input type="text" value="<?= h($mahasiswa['nama'] ?? ''); ?>" readonly>
                    </div>

                    <div class="form-group">
                        <label>NIM</label>
                        <input type="text" name="nim" value="<?= h($mahasiswa['nim'] ?? ''); ?>" readonly>
                    </div>
                </div>

                <div class="form-row two-col">
                    <div class="form-group">
                        <label>Program Studi</label>
                        <input type="text" value="<?= h($mahasiswa['prodi'] ?? ''); ?>" readonly>
                    </div>

                    <div class="form-group">
                        <label>No HP / Kontak</label>
                        <input type="text" value="<?= h($mahasiswa['kontak'] ?? '-'); ?>" readonly>
                    </div>
                </div>

                <div class="form-row two-col">
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" value="<?= h($mahasiswa['email'] ?? ''); ?>" readonly>
                    </div>

                    <div class="form-group">
                        <label>Pilih Kegiatan</label>
                        <select name="id_konten" required>
                            <option value="">-- Pilih Kegiatan --</option>

                            <?php while ($row = mysqli_fetch_assoc($kegiatanQuery)) { ?>
                                <?php
                                    $kuota = (int) $row['kuota'];
                                    $jumlah = (int) $row['jumlah_peserta'];
                                    $penuh = $kuota > 0 && $jumlah >= $kuota;
                                    $isSelected = $id_konten === (int) $row['id_konten'];
                                ?>

                                <option value="<?= (int) $row['id_konten']; ?>"
                                    <?= $isSelected ? 'selected' : ''; ?>
                                    <?= $penuh ? 'disabled' : ''; ?>>
                                    <?= h($row['judul']); ?> — <?= $jumlah; ?>/<?= $kuota; ?> <?= $penuh ? '(Kuota Penuh)' : ''; ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Catatan</label>

                            <textarea 
                                name="catatan"
                                rows="5"
                                placeholder="Tuliskan alasan kamu mengikuti kegiatan ini..."
                                required
                            ></textarea>
                        
                    </div>
                </div>

                <div class="form-actions centered">
                    <button type="submit" class="btn-primary submit-btn">Submit Pendaftaran</button>
                </div>
            </form>
        </section>
    </section>
</div>

<?php
require_once __DIR__ . '/../../include/footer.php';
?>
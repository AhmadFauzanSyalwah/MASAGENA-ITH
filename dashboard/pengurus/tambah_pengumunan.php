<?php
session_start();
require_once '../../config/session_check.php';
require_once '../../config/database.php';

$peran = $_SESSION['peran'];
$id_user = $_SESSION['user_id'];

if (!in_array($peran, ['pengurus', 'admin'])) {
    header('Location: ../../index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $judul = trim($_POST['judul']);
    $isi = trim($_POST['isi']);
    $status = $_POST['status'] ?? 'publik';

    if (empty($judul) || empty($isi)) {
        $error = "Judul dan isi harus diisi.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO pengumuman (judul, isi, id_pembuat, peran_pembuat, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$judul, $isi, $id_user, $peran, $status]);
        $_SESSION['success'] = "Pengumuman berhasil ditambahkan.";
        header('Location: kelola_pengumuman.php');
        exit;
    }
}

include '../../include/header.php';
?>
<style>
.form-container { max-width: 700px; margin: 0 auto; padding: 1rem; }
.form-group { margin-bottom: 1.2rem; }
.form-group label { font-weight: 600; display: block; margin-bottom: 0.3rem; }
.form-group input, .form-group textarea, .form-group select { width: 100%; padding: 0.6rem; border: 1.5px solid #e2e8f0; border-radius: 8px; font-size: 1rem; }
.form-group textarea { min-height: 200px; resize: vertical; }
.btn-submit { background: #FFA007; color: #071C34; padding: 0.5rem 2rem; border: none; border-radius: 50px; font-weight: 700; cursor: pointer; }
.btn-submit:hover { background: #071C34; color: #fff; }
.btn-batal { background: #e2e8f0; color: #071C34; padding: 0.5rem 2rem; border: none; border-radius: 50px; text-decoration: none; display: inline-block; margin-left: 0.5rem; }
</style>

<div class="form-container">
    <h2><i class="fa-regular fa-pen-to-square"></i> Tambah Pengumuman</h2>
    <?php if (isset($error)): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
    <form method="post">
        <div class="form-group">
            <label>Judul</label>
            <input type="text" name="judul" required>
        </div>
        <div class="form-group">
            <label>Isi Pengumuman</label>
            <textarea name="isi" required></textarea>
        </div>
        <div class="form-group">
            <label>Status</label>
            <select name="status">
                <option value="publik">Publik (langsung tampil)</option>
                <option value="draft">Draft (tidak tampil)</option>
            </select>
        </div>
        <button type="submit" class="btn-submit">Simpan</button>
        <a href="kelola_pengumuman.php" class="btn-batal">Batal</a>
    </form>
</div>

<?php include '../../include/footer.php'; ?>
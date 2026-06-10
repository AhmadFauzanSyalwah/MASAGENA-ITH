<?php
session_start();
require_once __DIR__ . '/../config/database.php';
if (!isset($_SESSION['peran']) || $_SESSION['peran'] !== 'admin') {
    header('Location: ' . BASE_URL . '/auth/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama'] ?? '';
    $deskripsi = $_POST['deskripsi'] ?? '';
    $stmt = $conn->prepare("INSERT INTO organisasi (nama_organisasi, deskripsi) VALUES (:nama, :desk)");
    $stmt->execute([':nama' => $nama, ':desk' => $deskripsi]);
    header('Location: index.php');
    exit;
}
require_once __DIR__ . '/../include/header.php';
?>

    <div class="container mt-4">
        <h2 style="color: #1B4C85;">Tambah Organisasi</h2>
        <form method="POST">
            <div class="mb-3">
                <label>Nama Organisasi</label>
                <input type="text" name="nama" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Deskripsi</label>
                <textarea name="deskripsi" class="form-control"></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Simpan</button>
        </form>
    </div>

<?php require_once __DIR__ . '/../include/footer.php'; ?>
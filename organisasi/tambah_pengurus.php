<?php
session_start();
require_once __DIR__ . '/../config/database.php';

$id_org = $_GET['id'] ?? 0;

// Cek akses
$allowed = false;
if (isset($_SESSION['peran']) && $_SESSION['peran'] === 'admin') {
    $allowed = true;
} elseif (isset($_SESSION['id_pengurus'], $_SESSION['id_organisasi']) && $_SESSION['id_organisasi'] == $id_org) {
    $stmt = $conn->prepare("SELECT jabatan FROM pengurus_organisasi WHERE id_pengurus = :id");
    $stmt->execute([':id' => $_SESSION['id_pengurus']]);
    $jabatan = $stmt->fetchColumn();
    if ($jabatan && stripos($jabatan, 'Ketua') !== false) {
        $allowed = true;
    }
}

if (!$allowed) {
    header('Location: ' . BASE_URL . '/organisasi/index.php');
    exit;
}

$stmt = $conn->prepare("SELECT * FROM organisasi WHERE id_organisasi = :id");
$stmt->execute([':id' => $id_org]);
$org = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$org) {
    header('Location: ' . BASE_URL . '/organisasi/index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama_pengurus'] ?? '';
    $jabatan = $_POST['jabatan'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($nama && $jabatan && $password) {
        $stmt = $conn->prepare("INSERT INTO pengurus_organisasi (id_organisasi, nama_pengurus, jabatan, password) VALUES (:org, :nama, :jab, :pass)");
        $stmt->execute([':org' => $id_org, ':nama' => $nama, ':jab' => $jabatan, ':pass' => $password]);
        header('Location: kelola_pengurus.php?id=' . $id_org);
        exit;
    }
}

require_once __DIR__ . '/../include/header.php';
?>

    <div class="container mt-4">
        <h2 style="color: #1B4C85;">Tambah Pengurus - <?= htmlspecialchars($org['nama_organisasi']) ?></h2>
        <form method="POST">
            <div class="mb-3">
                <label>Nama Pengurus</label>
                <input type="text" name="nama_pengurus" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Jabatan</label>
                <input type="text" name="jabatan" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="kelola_pengurus.php?id=<?= $id_org ?>" class="btn btn-secondary">Batal</a>
        </form>
    </div>

<?php require_once __DIR__ . '/../include/footer.php'; ?>
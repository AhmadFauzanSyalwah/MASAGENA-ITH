<?php
session_start();
require_once __DIR__ . '/../config/database.php';

$id_pengurus = $_GET['id'] ?? 0;

$stmt = $conn->prepare("SELECT * FROM pengurus_organisasi WHERE id_pengurus = :id");
$stmt->execute([':id' => $id_pengurus]);
$pengurus = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pengurus) {
    header('Location: ' . BASE_URL . '/organisasi/index.php');
    exit;
}

$id_org = $pengurus['id_organisasi'];

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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama_pengurus'] ?? '';
    $jabatan = $_POST['jabatan'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($nama && $jabatan) {
        if (!empty($password)) {
            $stmt = $conn->prepare("UPDATE pengurus_organisasi SET nama_pengurus = :nama, jabatan = :jab, password = :pass WHERE id_pengurus = :id");
            $stmt->execute([':nama' => $nama, ':jab' => $jabatan, ':pass' => $password, ':id' => $id_pengurus]);
        } else {
            $stmt = $conn->prepare("UPDATE pengurus_organisasi SET nama_pengurus = :nama, jabatan = :jab WHERE id_pengurus = :id");
            $stmt->execute([':nama' => $nama, ':jab' => $jabatan, ':id' => $id_pengurus]);
        }
        header('Location: kelola_pengurus.php?id=' . $id_org);
        exit;
    }
}

$stmt = $conn->prepare("SELECT * FROM organisasi WHERE id_organisasi = :id");
$stmt->execute([':id' => $id_org]);
$org = $stmt->fetch(PDO::FETCH_ASSOC);

require_once __DIR__ . '/../include/header.php';
?>

    <div class="container mt-4">
        <h2 style="color: #1B4C85;">Edit Pengurus - <?= htmlspecialchars($org['nama_organisasi']) ?></h2>
        <form method="POST">
            <div class="mb-3">
                <label>Nama Pengurus</label>
                <input type="text" name="nama_pengurus" class="form-control" required value="<?= htmlspecialchars($pengurus['nama_pengurus']) ?>">
            </div>
            <div class="mb-3">
                <label>Jabatan</label>
                <input type="text" name="jabatan" class="form-control" required value="<?= htmlspecialchars($pengurus['jabatan']) ?>">
            </div>
            <div class="mb-3">
                <label>Password <small class="text-muted">(kosongkan jika tidak ingin mengubah)</small></label>
                <input type="password" name="password" class="form-control">
            </div>
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="kelola_pengurus.php?id=<?= $id_org ?>" class="btn btn-secondary">Batal</a>
        </form>
    </div>

<?php require_once __DIR__ . '/../include/footer.php'; ?>
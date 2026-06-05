<?php
session_start();

// Mengatur zona waktu otomatis (Asia/Makassar untuk WITA)
date_default_timezone_set('Asia/Makassar');

// Simulasi login jika session belum ada
if (!isset($_SESSION['id_mahasiswa'])) {
    $_SESSION['id_mahasiswa'] = 1; // Contoh ID Mahasiswa login
}

$id_mahasiswa = $_SESSION['id_mahasiswa'];

// Koneksi ke database
$conn = new mysqli("localhost", "root", "", "masagena-ith");
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// ==========================================
// FITUR PROSES LIKE (ASPIRASI & KOMENTAR)
// ==========================================
if (isset($_GET['aksi']) && isset($_GET['id'])) {
    $id_target = $_GET['id'];
    
    if ($_GET['aksi'] == 'like_aspirasi') {
        $stmt_like = $conn->prepare("UPDATE aspirasi SET likes = likes + 1 WHERE id_aspirasi = ?");
        $stmt_like->bind_param("i", $id_target);
        $stmt_like->execute();
        $stmt_like->close();
        header("Location: aktivitas_pengguna.php");
        exit;
    }
    
    if ($_GET['aksi'] == 'like_komentar') {
        $stmt_like_k = $conn->prepare("UPDATE komentar SET likes = likes + 1 WHERE id_komentar = ?");
        $stmt_like_k->bind_param("i", $id_target);
        $stmt_like_k->execute();
        $stmt_like_k->close();
        header("Location: aktivitas_pengguna.php");
        exit;
    }
}

// 1. PROSES SIMPAN ASPIRASI (Insert)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['simpan_aspirasi'])) {
    $judul = $_POST['judul'];
    $kategori = $_POST['kategori'];
    $tanggal = date("Y-m-d H:i:s"); 
    $status = "selesai";             
    
    // SOLUSI ERROR: Membuat kode unik acak sepanjang 5 karakter agar tidak bentrok (duplicate entry)
    $kode_aspirasi = "ASP-" . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 5));

    $stmt_insert = $conn->prepare("INSERT INTO aspirasi (id_mahasiswa, tanggal, judul, kategori, status, kode_aspirasi) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt_insert->bind_param("isssss", $id_mahasiswa, $tanggal, $judul, $kategori, $status, $kode_aspirasi);

    if ($stmt_insert->execute()) {
        echo "<script>alert('Aspirasi berhasil dikirim dengan kode: $kode_aspirasi'); window.location.href = 'aktivitas_pengguna.php';</script>";
        exit;
    }
    $stmt_insert->close();
}

// 2. PROSES UPDATE ASPIRASI (Edit)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_aspirasi'])) {
    $id_aspirasi = $_POST['id_aspirasi'];
    $judul = $_POST['judul'];
    $kategori = $_POST['kategori'];

    $stmt_update = $conn->prepare("UPDATE aspirasi SET judul = ?, kategori = ? WHERE id_aspirasi = ? AND id_mahasiswa = ?");
    $stmt_update->bind_param("ssii", $judul, $kategori, $id_aspirasi, $id_mahasiswa);

    if ($stmt_update->execute()) {
        echo "<script>alert('Aspirasi berhasil diperbarui!'); window.location.href = 'aktivitas_pengguna.php';</script>";
        exit;
    }
    $stmt_update->close();
}

// 3. PROSES SIMPAN KOMENTAR (Insert)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['simpan_komentar'])) {
    $id_aspirasi = $_POST['id_aspirasi'];
    $isi_komentar = $_POST['isi_komentar'];
    $tanggal = date("Y-m-d H:i:s");
    $level_user = "mahasiswa";

    $stmt_comment = $conn->prepare("INSERT INTO komentar (id_aspirasi, id_user, level_user, tanggal, isi_komentar) VALUES (?, ?, ?, ?, ?)");
    $stmt_comment->bind_param("iisss", $id_aspirasi, $id_mahasiswa, $level_user, $tanggal, $isi_komentar);

    if ($stmt_comment->execute()) {
        echo "<script>alert('Komentar berhasil ditambahkan!'); window.location.href = 'aktivitas_pengguna.php';</script>";
        exit;
    }
    $stmt_comment->close();
}

// 4. PROSES UPDATE KOMENTAR (Edit)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_komentar'])) {
    $id_komentar = $_POST['id_komentar'];
    $isi_komentar = $_POST['isi_komentar'];

    $stmt_update_kmtr = $conn->prepare("UPDATE komentar SET isi_komentar = ? WHERE id_komentar = ? AND id_user = ? AND level_user = 'mahasiswa'");
    $stmt_update_kmtr->bind_param("sii", $isi_komentar, $id_komentar, $id_mahasiswa);

    if ($stmt_update_kmtr->execute()) {
        echo "<script>alert('Komentar berhasil diperbarui!'); window.location.href = 'aktivitas_pengguna.php';</script>";
        exit;
    }
    $stmt_update_kmtr->close();
}

// 5. PROSES SIMPAN PENDAFTARAN
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['simpan_pendaftaran'])) {
    $id_organisasi = $_POST['id_organisasi'];
    $id_konten = $_POST['id_konten'];
    $tanggal_daftar = date("Y-m-d H:i:s");
    $status_pendaftaran = "selesai"; 

    $stmt_reg = $conn->prepare("INSERT INTO pendaftaran (id_mahasiswa, id_organisasi, id_konten, tanggal_daftar, status_pendaftaran) VALUES (?, ?, ?, ?, ?)");
    $stmt_reg->bind_param("iiiss", $id_mahasiswa, $id_organisasi, $id_konten, $tanggal_daftar, $status_pendaftaran);

    if ($stmt_reg->execute()) {
        echo "<script>alert('Pendaftaran berhasil dikirim!'); window.location.href = 'aktivitas_pengguna.php';</script>";
        exit;
    }
    $stmt_reg->close();
}

// PROSES DELETE
if (isset($_GET['aksi']) && isset($_GET['id'])) {
    $id_target = $_GET['id'];
    $aksi_hapus = $_GET['aksi'];

    if ($aksi_hapus == 'hapus') {
        $stmt_delete = $conn->prepare("DELETE FROM aspirasi WHERE id_aspirasi = ? AND id_mahasiswa = ?");
        $stmt_delete->bind_param("ii", $id_target, $id_mahasiswa);
        if ($stmt_delete->execute()) {
            echo "<script>alert('Aspirasi berhasil dihapus!'); window.location.href = 'aktivitas_pengguna.php';</script>";
            exit;
        }
        $stmt_delete->close();
    } elseif ($aksi_hapus == 'hapus_komentar') {
        $stmt_delete_kmtr = $conn->prepare("DELETE FROM komentar WHERE id_komentar = ? AND id_user = ? AND level_user = 'mahasiswa'");
        $stmt_delete_kmtr->bind_param("ii", $id_target, $id_mahasiswa);
        if ($stmt_delete_kmtr->execute()) {
            echo "<script>alert('Komentar berhasil dihapus!'); window.location.href = 'aktivitas_pengguna.php';</script>";
            exit;
        }
        $stmt_delete_kmtr->close();
    } elseif ($aksi_hapus == 'hapus_pendaftaran') {
        $stmt_delete_daftar = $conn->prepare("DELETE FROM pendaftaran WHERE id_pendaftaran = ? AND id_mahasiswa = ?");
        $stmt_delete_daftar->bind_param("ii", $id_target, $id_mahasiswa);
        if ($stmt_delete_daftar->execute()) {
            echo "<script>alert('Pendaftaran berhasil dibatalkan!'); window.location.href = 'aktivitas_pengguna.php';</script>";
            exit;
        }
        $stmt_delete_daftar->close();
    }
}

$aksi = isset($_GET['aksi']) ? $_GET['aksi'] : '';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aktivitas Saya - Masagena ITH</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">
    <style>
        :root {
            --primary-blue: #0A4D68;
            --secondary-blue: #05bfdb;
            --light-blue: #EBF4F6;
            --orange-accent: #FF6B35;
            --orange-hover: #E85A28;
        }
        body { background-color: #f4f7f9; font-family: 'Segoe UI', sans-serif; color: #333; }
        .card { border: 1px solid rgba(10, 77, 104, 0.08); box-shadow: 0 10px 30px rgba(10, 77, 104, 0.04); border-radius: 14px; background-color: #ffffff; }
        .nav-title { font-weight: 700; color: var(--primary-blue); }
        .custom-card-header { background: linear-gradient(135deg, var(--primary-blue), #083D54); color: #ffffff; padding: 15px 20px; border-radius: 14px 14px 0 0 !important; }
        .custom-card-header h5 { color: #ffffff !important; font-weight: 600; }
        .table th { font-weight: 600; color: var(--primary-blue); background-color: var(--light-blue); border-bottom: 2px solid rgba(10, 77, 104, 0.1); }
        .btn-orange { background-color: var(--orange-accent); color: #ffffff; border: none; transition: all 0.3s ease; }
        .btn-orange:hover { background-color: var(--orange-hover); color: #ffffff; transform: translateY(-2px); }
        .btn-blue { background-color: var(--primary-blue); color: #ffffff; border: none; transition: all 0.3s ease; }
        .btn-blue:hover { background-color: #063549; color: #ffffff; transform: translateY(-2px); }
        .btn-outline-blue { border: 2px solid var(--primary-blue); color: var(--primary-blue); background-color: transparent; font-weight: 600; }
        .btn-outline-blue:hover { background-color: var(--primary-blue); color: #ffffff; }
        .badge-selesai { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; font-weight: 600; }
        .badge-pending { background-color: #fff3cd; color: #856404; border: 1px solid #ffeeba; font-weight: 600; }
        .btn-delete-action { color: #dc3545; background-color: #fff5f5; border-radius: 8px; transition: all 0.2s; }
        .btn-delete-action:hover { color: #ffffff; background-color: #dc3545; }
        .btn-edit-action { color: #ffc107; background-color: #fffbeb; border-radius: 8px; transition: all 0.2s; }
        .btn-edit-action:hover { color: #333; background-color: #ffc107; }
        .btn-like { background-color: #ffeef2; color: #e91e63; border: 1px solid #ffccd8; border-radius: 8px; font-weight: 600; transition: all 0.2s; }
        .btn-like:hover { background-color: #e91e63; color: #ffffff; }
    </style>
</head>
<body>

    <div class="container py-5">
        
        <?php if ($aksi == 'tambah'): ?>
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card p-0 overflow-hidden">
                        <div class="custom-card-header">
                            <h5 class="m-0">Sampaikan Aspirasi Baru</h5>
                        </div>
                        <div class="p-4">
                            <form action="aktivitas_pengguna.php" method="POST">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold text-secondary">Judul Aspirasi</label>
                                    <input type="text" name="judul" class="form-control" required placeholder="Contoh: Lampu Ruang Kuliah Mati">
                                </div>
                                <div class="mb-4">
                                    <label class="form-label fw-semibold text-secondary">Kategori</label>
                                    <select name="kategori" class="form-select" required>
                                        <option value="Sarana & Prasarana">Sarana & Prasarana</option>
                                        <option value="Kesejahteraan">Kesejahteraan</option>
                                        <option value="Akademik">Akademik</option>
                                    </select>
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="submit" name="simpan_aspirasi" class="btn btn-orange w-100 fw-semibold py-2">Kirim Aspirasi</button>
                                    <a href="aktivitas_pengguna.php" class="btn btn-light border w-100 py-2">Kembali</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        <?php elseif ($aksi == 'edit_aspirasi' && isset($_GET['id'])): 
            // FORM EDIT ASPIRASI
            $id_edit = $_GET['id'];
            $stmt_get = $conn->prepare("SELECT judul, kategori FROM aspirasi WHERE id_aspirasi = ? AND id_mahasiswa = ?");
            $stmt_get->bind_param("ii", $id_edit, $id_mahasiswa);
            $stmt_get->execute();
            $res_edit = $stmt_get->get_result()->fetch_assoc();
            $stmt_get->close();
            if($res_edit):
        ?>
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card p-0 overflow-hidden">
                        <div class="custom-card-header">
                            <h5 class="m-0">Edit Aspirasi</h5>
                        </div>
                        <div class="p-4">
                            <form action="aktivitas_pengguna.php" method="POST">
                                <input type="hidden" name="id_aspirasi" value="<?php echo $id_edit; ?>">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold text-secondary">Judul Aspirasi</label>
                                    <input type="text" name="judul" class="form-control" required value="<?php echo htmlspecialchars($res_edit['judul']); ?>">
                                </div>
                                <div class="mb-4">
                                    <label class="form-label fw-semibold text-secondary">Kategori</label>
                                    <select name="kategori" class="form-select" required>
                                        <option value="Sarana & Prasarana" <?php echo $res_edit['kategori'] == 'Sarana & Prasarana' ? 'selected' : ''; ?>>Sarana & Prasarana</option>
                                        <option value="Kesejahteraan" <?php echo $res_edit['kategori'] == 'Kesejahteraan' ? 'selected' : ''; ?>>Kesejahteraan</option>
                                        <option value="Akademik" <?php echo $res_edit['kategori'] == 'Akademik' ? 'selected' : ''; ?>>Akademik</option>
                                    </select>
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="submit" name="update_aspirasi" class="btn btn-orange w-100 fw-semibold py-2">Simpan Perubahan</button>
                                    <a href="aktivitas_pengguna.php" class="btn btn-light border w-100 py-2">Kembali</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

        <?php elseif ($aksi == 'komentar'): ?>
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card p-0 overflow-hidden">
                        <div class="custom-card-header">
                            <h5 class="m-0">Beri Komentar Baru</h5>
                        </div>
                        <div class="p-4">
                            <form action="aktivitas_pengguna.php" method="POST">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold text-secondary">Pilih Aspirasi</label>
                                    <select name="id_aspirasi" class="form-select" required>
                                        <?php
                                        $list_asp = $conn->query("SELECT id_aspirasi, judul FROM aspirasi");
                                        while($asp = $list_asp->fetch_assoc()) {
                                            echo "<option value='{$asp['id_aspirasi']}'>" . htmlspecialchars($asp['judul']) . "</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label fw-semibold text-secondary">Isi Komentar</label>
                                    <textarea name="isi_komentar" class="form-control" rows="4" required placeholder="Tulis tanggapan..."></textarea>
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="submit" name="simpan_komentar" class="btn btn-orange w-100 fw-semibold py-2">Kirim Komentar</button>
                                    <a href="aktivitas_pengguna.php" class="btn btn-light border w-100 py-2">Kembali</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        <?php elseif ($aksi == 'edit_komentar' && isset($_GET['id'])): 
            // FORM EDIT KOMENTAR
            $id_edit_kmtr = $_GET['id'];
            $stmt_get_kmtr = $conn->prepare("SELECT isi_komentar FROM komentar WHERE id_komentar = ? AND id_user = ? AND level_user = 'mahasiswa'");
            $stmt_get_kmtr->bind_param("ii", $id_edit_kmtr, $id_mahasiswa);
            $stmt_get_kmtr->execute();
            $res_edit_kmtr = $stmt_get_kmtr->get_result()->fetch_assoc();
            $stmt_get_kmtr->close();
            if($res_edit_kmtr):
        ?>
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card p-0 overflow-hidden">
                        <div class="custom-card-header">
                            <h5 class="m-0">Edit Komentar</h5>
                        </div>
                        <div class="p-4">
                            <form action="aktivitas_pengguna.php" method="POST">
                                <input type="hidden" name="id_komentar" value="<?php echo $id_edit_kmtr; ?>">
                                <div class="mb-4">
                                    <label class="form-label fw-semibold text-secondary">Isi Komentar</label>
                                    <textarea name="isi_komentar" class="form-control" rows="4" required><?php echo htmlspecialchars($res_edit_kmtr['isi_komentar']); ?></textarea>
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="submit" name="update_komentar" class="btn btn-orange w-100 fw-semibold py-2">Simpan Perubahan</button>
                                    <a href="aktivitas_pengguna.php" class="btn btn-light border w-100 py-2">Kembali</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

        <?php elseif ($aksi == 'daftar'): ?>
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card p-0 overflow-hidden">
                        <div class="custom-card-header">
                            <h5 class="m-0">Form Pendaftaran</h5>
                        </div>
                        <div class="p-4">
                            <form action="aktivitas_pengguna.php" method="POST">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold text-secondary">Pilih Organisasi</label>
                                    <select name="id_organisasi" class="form-select" required>
                                        <option value="" disabled selected>-- Pilih Organisasi --</option>
                                        <option value="991">Habibie Coding Club</option>
                                        <option value="992">Robotika</option>
                                        
                                        <?php
                                        $list_org = $conn->query("SELECT id_organisasi, nama_organisasi FROM organisasi");
                                        if ($list_org && $list_org->num_rows > 0) {
                                            while($org = $list_org->fetch_assoc()) {
                                                if ($org['nama_organisasi'] != 'Habibie Coding Club' && $org['nama_organisasi'] != 'Robotika') {
                                                    echo "<option value='{$org['id_organisasi']}'>" . htmlspecialchars($org['nama_organisasi']) . "</option>";
                                                }
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label fw-semibold text-secondary">Pilih Kegiatan</label>
                                    <select name="id_konten" class="form-select" required>
                                        <?php
                                        $list_kgt = $conn->query("SELECT id_konten, judul_kegiatan FROM konten_kegiatan");
                                        while($kgt = $list_kgt->fetch_assoc()) {
                                            echo "<option value='{$kgt['id_konten']}'>" . htmlspecialchars($kgt['judul_kegiatan']) . "</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="submit" name="simpan_pendaftaran" class="btn btn-orange w-100 fw-semibold py-2">Daftar Sekarang</button>
                                    <a href="aktivitas_pengguna.php" class="btn btn-light border w-100 py-2">Kembali</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        <?php else: ?>
            <div class="d-flex justify-content-between align-items-center mb-4 row g-3">
                <div class="col-md-6 text-center text-md-start">
                    <h2 class="nav-title m-0"><span style="color: var(--orange-accent)">Riwayat</span> Aktivitas Saya</h2>
                    <p class="text-muted m-0 small">Pantau semua log aspirasi, komentar, dan pendaftaran Anda di sini.</p>
                </div>
                <div class="col-md-6 d-flex gap-2 justify-content-center justify-content-md-end">
                    <a href="aktivitas_pengguna.php?aksi=tambah" class="btn btn-blue fw-semibold shadow-sm btn-sm px-3 py-2"> + Aspirasi</a>
                    <a href="aktivitas_pengguna.php?aksi=komentar" class="btn btn-outline-blue fw-semibold shadow-sm btn-sm px-3 py-2"> + Komentar</a>
                    <a href="aktivitas_pengguna.php?aksi=daftar" class="btn btn-orange fw-semibold shadow-sm btn-sm px-3 py-2"> + Daftar Kegiatan</a>
                </div>
            </div>

            <div class="card mb-4 overflow-hidden">
                <div class="custom-card-header d-flex align-items-center">
                    <h5 class="m-0">Aspirasi</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4" style="width: 140px;">Tanggal</th>
                                <th>Judul Aspirasi</th>
                                <th>Kategori</th>
                                <th style="width: 100px; text-align: center;">Suka</th>
                                <th style="width: 130px;">Status</th>
                                <th class="text-center" style="width: 150px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt1 = $conn->prepare("SELECT id_aspirasi, tanggal, judul, kategori, status, likes FROM aspirasi WHERE id_mahasiswa = ? ORDER BY tanggal DESC");
                            $stmt1->bind_param("i", $id_mahasiswa);
                            $stmt1->execute();
                            $result1 = $stmt1->get_result();

                            if ($result1->num_rows > 0) {
                                while($row = $result1->fetch_assoc()) {
                                    $status_clean = strtolower($row['status']);
                                    $badge_class = ($status_clean == 'selesai' || $status_clean == 'lulus') ? 'badge-selesai' : 'badge-pending';
                                    $only_date = date('Y-m-d', strtotime($row['tanggal']));

                                    echo "<tr>
                                            <td class='ps-4 text-muted small'>{$only_date}</td>
                                            <td class='fw-semibold text-dark'>" . htmlspecialchars($row['judul']) . "</td>
                                            <td><span class='badge bg-light text-secondary border'>" . htmlspecialchars($row['kategori']) . "</span></td>
                                            <td class='text-center'><span class='text-danger fw-bold'><i class='bi bi-heart-fill me-1'></i>" . ($row['likes'] ?: 0) . "</span></td>
                                            <td><span class='badge {$badge_class} px-2 py-1.5 w-100 text-center'>" . strtoupper($row['status'] ?: 'PENDING') . "</span></td>
                                            <td class='text-center'>
                                                <div class='d-inline-flex gap-1'>
                                                    <a href='aktivitas_pengguna.php?aksi=like_aspirasi&id={$row['id_aspirasi']}' class='btn btn-sm btn-like px-2' title='Suka'>
                                                        <i class='bi bi-hand-thumbs-up'></i> Like
                                                    </a>
                                                    <a href='aktivitas_pengguna.php?aksi=edit_aspirasi&id={$row['id_aspirasi']}' class='btn btn-sm btn-edit-action border-0 px-2' title='Edit'>
                                                        Edit
                                                    </a>
                                                    <a href='aktivitas_pengguna.php?aksi=hapus&id={$row['id_aspirasi']}' class='btn btn-sm btn-delete-action border-0 px-2' onclick='return confirm(\"Apakah Anda yakin menghapus?\")' title='Hapus'>
                                                        Hapus
                                                    </a>
                                                </div>
                                            </td>
                                          </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='6' class='text-center py-4 text-muted'>Belum ada data.</td></tr>";
                            }
                            $stmt1->close();
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card mb-4 overflow-hidden">
                <div class="custom-card-header d-flex align-items-center">
                    <h5 class="m-0">Komentar</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4" style="width: 140px;">Tanggal</th>
                                <th style="width: 250px;">Pada Aspirasi (Judul)</th>
                                <th>Isi Komentar</th>
                                <th style="width: 100px; text-align: center;">Suka</th>
                                <th class="text-center" style="width: 150px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt2 = $conn->prepare("SELECT k.id_komentar, k.tanggal, k.isi_komentar, k.likes, a.judul 
                                                     FROM komentar k 
                                                     JOIN aspirasi a ON k.id_aspirasi = a.id_aspirasi 
                                                     WHERE k.id_user = ? AND k.level_user = 'mahasiswa' 
                                                     ORDER BY k.tanggal DESC");
                            $stmt2->bind_param("i", $id_mahasiswa);
                            $stmt2->execute();
                            $result2 = $stmt2->get_result();

                            if ($result2->num_rows > 0) {
                                while($row = $result2->fetch_assoc()) {
                                    $only_date = date('Y-m-d', strtotime($row['tanggal']));

                                    echo "<tr>
                                            <td class='ps-4 text-muted small'>{$only_date}</td>
                                            <td class='text-truncate fw-semibold text-secondary' style='max-width: 250px;'>" . htmlspecialchars($row['judul']) . "</td>
                                            <td class='text-dark'>" . htmlspecialchars($row['isi_komentar']) . "</td>
                                            <td class='text-center'><span class='text-danger fw-bold'><i class='bi bi-heart-fill me-1'></i>" . ($row['likes'] ?: 0) . "</span></td>
                                            <td class='text-center'>
                                                <div class='d-inline-flex gap-1'>
                                                    <a href='aktivitas_pengguna.php?aksi=like_komentar&id={$row['id_komentar']}' class='btn btn-sm btn-like px-2' title='Suka'>
                                                        <i class='bi bi-hand-thumbs-up'></i> Like
                                                    </a>
                                                    <a href='aktivitas_pengguna.php?aksi=edit_komentar&id={$row['id_komentar']}' class='btn btn-sm btn-edit-action border-0 px-2' title='Edit'>
                                                        Edit
                                                    </a>
                                                    <a href='aktivitas_pengguna.php?aksi=hapus_komentar&id={$row['id_komentar']}' class='btn btn-sm btn-delete-action border-0 px-2' onclick='return confirm(\"Apakah Anda yakin menghapus?\")' title='Hapus'>
                                                        Hapus
                                                    </a>
                                                </div>
                                            </td>
                                          </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='5' class='text-center py-4 text-muted'>Belum ada data komentar.</td></tr>";
                            }
                            $stmt2->close();
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card overflow-hidden">
                <div class="custom-card-header d-flex align-items-center">
                    <h5 class="m-0">Pendaftaran Kegiatan / Organisasi</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4" style="width: 140px;">Tanggal Daftar</th>
                                <th>Nama Organisasi</th>
                                <th>Kegiatan</th>
                                <th style="width: 130px;">Status Seleksi</th>
                                <th class="text-center" style="width: 100px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt3 = $conn->prepare("SELECT p.id_pendaftaran, p.tanggal_daftar, p.status_pendaftaran, p.id_organisasi, o.nama_organisasi, k.judul_kegiatan 
                                                     FROM pendaftaran p
                                                     LEFT JOIN organisasi o ON p.id_organisasi = o.id_organisasi
                                                     JOIN konten_kegiatan k ON p.id_konten = k.id_konten
                                                     WHERE p.id_mahasiswa = ? 
                                                     ORDER BY p.tanggal_daftar DESC");
                            $stmt3->bind_param("i", $id_mahasiswa);
                            $stmt3->execute();
                            $result3 = $stmt3->get_result();

                            if ($result3->num_rows > 0) {
                                while($row = $result3->fetch_assoc()) {
                                    $status_pendaftaran = strtolower($row['status_pendaftaran']);
                                    $badge_pendaftaran = ($status_pendaftaran == 'selesai' || $status_pendaftaran == 'lulus') ? 'badge-selesai' : 'badge-pending';
                                    $only_date = date('Y-m-d', strtotime($row['tanggal_daftar']));

                                    $nama_org_tampil = $row['nama_organisasi'];
                                    if ($row['id_organisasi'] == 991) {
                                        $nama_org_tampil = "Habibie Coding Club";
                                    } elseif ($row['id_organisasi'] == 992) {
                                        $nama_org_tampil = "Robotika";
                                    }

                                    echo "<tr>
                                            <td class='ps-4 text-muted small'>{$only_date}</td>
                                            <td class='fw-semibold text-dark'>" . htmlspecialchars($nama_org_tampil ?: '-') . "</td>
                                            <td class='text-secondary'>" . htmlspecialchars($row['judul_kegiatan']) . "</td>
                                            <td><span class='badge {$badge_pendaftaran} px-2 py-1.5 w-100 text-center'>" . strtoupper($row['status_pendaftaran'] ?: 'PENDING') . "</span></td>
                                            <td class='text-center'>
                                                <a href='aktivitas_pengguna.php?aksi=hapus_pendaftaran&id={$row['id_pendaftaran']}' class='btn btn-sm btn-delete-action border-0 px-2' onclick='return confirm(\"Apakah Anda yakin membatalkan pendaftaran?\")'>
                                                    Batal
                                                </a>
                                            </td>
                                          </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='5' class='text-center py-4 text-muted'>Belum ada riwayat pendaftaran.</td></tr>";
                            }
                            $stmt3->close();
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $conn->close(); ?>
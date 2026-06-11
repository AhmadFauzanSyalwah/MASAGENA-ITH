<?php
session_start();
include 'connection.php';

// Pastikan ada parameter NIM yang dikirim lewat URL
if (!isset($_GET['nim'])) {
    header("Location: pencarian_pengguna.php");
    exit();
}

$nim = mysqli_real_escape_string($conn, $_GET['nim']);

// Ambil data mahasiswa berdasarkan NIM
$result = $conn->query("SELECT * FROM mahasiswa WHERE nim = '$nim'");
if ($result->num_rows == 0) {
    echo "<script>alert('Data mahasiswa tidak ditemukan!'); window.location='pencarian_pengguna.php';</script>";
    exit();
}
$data = $result->fetch_assoc();

// Proses Update Data saat tombol simpan diklik
if (isset($_POST['update'])) {
    $nama_lengkap = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    $program_studi = mysqli_real_escape_string($conn, $_POST['program_studi']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $no_hp = mysqli_real_escape_string($conn, $_POST['no_hp']);

    $update_query = "UPDATE mahasiswa SET 
                    nama_lengkap = '$nama_lengkap', 
                    program_studi = '$program_studi', 
                    email = '$email', 
                    no_hp = '$no_hp' 
                    WHERE nim = '$nim'";
    
    if ($conn->query($update_query)) {
        echo "<script>alert('Data mahasiswa berhasil diperbarui!'); window.location='pencarian_pengguna.php';</script>";
        exit();
    } else {
        echo "<script>alert('Gagal memperbarui data: " . $conn->error . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Mahasiswa - Masagena ITH</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">
    
    <style>
        :root { 
            --primary-blue: #0A4D68; 
            --secondary-blue: #083D54;
            --orange-accent: #FF6B35;
            --bg-body: #F8FAFC;
        }
        body { 
            background-color: var(--bg-body); 
            font-family: 'Inter', sans-serif;
        }
        .custom-card { 
            border: none; 
            box-shadow: 0 10px 30px rgba(10, 77, 104, 0.05); 
            border-radius: 16px; 
        }
        .custom-card-header { 
            background: linear-gradient(135deg, var(--primary-blue), var(--secondary-blue)); 
            color: #ffffff; 
            padding: 20px 24px; 
            border-radius: 16px 16px 0 0 !important; 
        }
        .btn-gradient { 
            background: linear-gradient(135deg, var(--primary-blue), var(--secondary-blue)); 
            color: #ffffff; 
            border: none;
            border-radius: 10px;
            padding: 11px 24px;
            font-weight: 500;
        }
        .btn-gradient:hover { color: #ffffff; opacity: 0.95; }
        .form-control:focus {
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 3px rgba(10, 77, 104, 0.15);
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="mb-4 text-center">
            <h2><span style="color: var(--orange-accent)">Ubah</span> Data Mahasiswa</h2>
            <p class="text-muted small">Perbarui informasi profil mahasiswa terdaftar di bawah ini.</p>
        </div>

        <div class="card custom-card col-md-6 mx-auto bg-white overflow-hidden">
            <div class="custom-card-header">
                <h5 class="mb-0"><i class="bi bi-pencil-square me-2"></i>Form Edit Informasi</h5>
            </div>
            <div class="card-body p-4">
                <form action="" method="POST">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-muted">NIM (Nomor Induk Mahasiswa)</label>
                        <input type="text" class="form-control bg-light" value="<?php echo htmlspecialchars($data['nim']); ?>" disabled>
                        <div class="form-text text-danger">* NIM bersifat permanen dan tidak dapat diubah.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nama Lengkap</label>
                        <input type="text" name="nama_lengkap" class="form-control" value="<?php echo htmlspecialchars($data['nama_lengkap']); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Program Studi</label>
                        <input type="text" name="program_studi" class="form-control" value="<?php echo htmlspecialchars($data['program_studi']); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Email Institusi</label>
                        <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($data['email']); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">No. HP / WhatsApp</label>
                        <input type="text" name="no_hp" class="form-control" value="<?php echo htmlspecialchars($data['no_hp']); ?>">
                    </div>

                    <hr class="my-4" style="opacity: 0.1;">

                    <div class="d-flex gap-2 justify-content-end">
                        <a href="pencarian_pengguna.php" class="btn btn-light border px-4" style="border-radius: 10px;">Batal</a>
                        <button type="submit" name="update" class="btn btn-gradient px-4">
                            <i class="bi bi-check-lg me-1"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
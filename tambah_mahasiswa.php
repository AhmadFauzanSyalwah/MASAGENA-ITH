<?php
session_start();
include 'connection.php';

if (isset($_POST['submit'])) {
    $nim = mysqli_real_escape_string($conn, $_POST['nim']);
    $nama_lengkap = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    $program_studi = mysqli_real_escape_string($conn, $_POST['program_studi']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $no_hp = mysqli_real_escape_string($conn, $_POST['no_hp']);

    // Validasi agar NIM tidak duplikat
    $cek_nim = $conn->query("SELECT nim FROM mahasiswa WHERE nim = '$nim'");
    if ($cek_nim->num_rows > 0) {
        echo "<script>alert('Error: NIM sudah terdaftar dalam sistem!'); window.history.back();</script>";
    } else {
        // Query Insert Data
        $query = "INSERT INTO mahasiswa (nim, nama_lengkap, program_studi, email, no_hp) 
                  VALUES ('$nim', '$nama_lengkap', '$program_studi', '$email', '$no_hp')";
        
        if ($conn->query($query)) {
            echo "<script>alert('Data mahasiswa berhasil ditambahkan!'); window.location='pencarian_pengguna.php';</script>";
            exit();
        } else {
            echo "<script>alert('Gagal menambahkan data: " . $conn->error . "');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Mahasiswa - Admin Masagena ITH</title>
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
            <h2><span style="color: var(--orange-accent)">Tambah</span> Pengguna Baru</h2>
            <p class="text-muted small">Daftarkan mahasiswa baru secara langsung ke dalam sistem Masagena ITH.</p>
        </div>

        <div class="card custom-card col-md-7 mx-auto bg-white overflow-hidden">
            <div class="custom-card-header">
                <h5 class="mb-0"><i class="bi bi-person-plus-fill me-2"></i>Formulir Data Mahasiswa</h5>
            </div>
            <div class="card-body p-4">
                <form action="" method="POST">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">NIM (Nomor Induk Mahasiswa)</label>
                            <input type="text" name="nim" class="form-control" placeholder="Contoh: 241011089" required>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nama Lengkap</label>
                            <input type="text" name="nama_lengkap" class="form-control" placeholder="Masukkan nama lengkap" required>
                        </div>
                        
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Program Studi</label>
                            <input type="text" name="program_studi" class="form-control" placeholder="Contoh: Teknik Informatika" required>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Email Institusi</label>
                            <input type="email" name="email" class="form-control" placeholder="nama@ith.ac.id" required>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">No. HP / WhatsApp</label>
                            <input type="text" name="no_hp" class="form-control" placeholder="Contoh: 08123456xxx">
                        </div>
                    </div>

                    <hr class="my-4" style="opacity: 0.1;">

                    <div class="d-flex gap-2 justify-content-end">
                        <a href="pencarian_pengguna.php" class="btn btn-light border px-4" style="border-radius: 10px;">Kembali</a>
                        <button type="submit" name="submit" class="btn btn-gradient px-4">
                            <i class="bi bi-plus-lg me-1"></i> Daftarkan Mahasiswa
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
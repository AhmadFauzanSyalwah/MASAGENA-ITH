<?php
// 1. KONEKSI KE DATABASE
$host     = "localhost";
$db_user  = "root";
$db_pass  = ""; // Kosongkan jika pakai XAMPP default
$db_name  = "masagena-ith"; 

$koneksi = mysqli_connect($host, $db_user, $db_pass, $db_name);

// Cek koneksi
if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// 2. PROSES INSERT DATA
$pesan = "";
if (isset($_POST['simpan'])) {
    $nim   = mysqli_real_escape_string($koneksi, $_POST['nim']);
    $nama  = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $prodi = mysqli_real_escape_string($koneksi, $_POST['prodi']);
    $no_hp = mysqli_real_escape_string($koneksi, $_POST['no_hp']);
    $email = mysqli_real_escape_string($koneksi, $_POST['email']);

    // 🔍 VALIDASI: Cek apakah NIM sudah ada di database sebelum insert
    $cek_nim  = mysqli_query($koneksi, "SELECT nim FROM mahasiswa WHERE nim = '$nim'");
    
    if (mysqli_num_rows($cek_nim) > 0) {
        // Jika NIM sudah terdaftar
        $pesan = "<div class='alert danger'>Gagal: NIM <strong>$nim</strong> sudah terdaftar di sistem!</div>";
    } else {
        // KEMBALI KE ASLI: Menggunakan nama kolom database Anda yang sebenarnya (nama & prodi)
        $query = "INSERT INTO mahasiswa (nim, nama, prodi, no_hp, email) 
                  VALUES ('$nim', '$nama', '$prodi', '$no_hp', '$email')";
        
        if (mysqli_query($koneksi, $query)) {
            $pesan = "<div class='alert success'>Data mahasiswa berhasil ditambahkan! Mengalihkan ke daftar...</div>";
            $pesan .= "<meta http-equiv='refresh' content='2;url=pencarian_pengguna.php'>";
        } else {
            $pesan = "<div class='alert danger'>Gagal menambah data: " . mysqli_error($koneksi) . "</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Mahasiswa</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f9; margin: 50px; }
        .container { max-width: 450px; background: white; padding: 25px; border-radius: 8px; box-shadow: 0px 0px 10px rgba(0,0,0,0.1); margin: 0 auto; }
        h2 { text-align: center; color: #333; margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; color: #666; font-weight: bold; }
        .form-group input, .form-group select { width: 100%; padding: 10px; box-sizing: border-box; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; }
        
        /* Pembungkus Tombol Aksi */
        .btn-group { display: flex; gap: 10px; margin-top: 20px; }
        
        button { flex: 1; padding: 12px; background-color: #007bff; border: none; color: white; font-size: 16px; border-radius: 4px; cursor: pointer; font-weight: bold; }
        button:hover { background-color: #0056b3; }
        
        /* Gaya Tombol Lihat Daftar */
        .btn-daftar { flex: 1; display: inline-block; text-align: center; padding: 12px; background-color: #28a745; color: white; font-size: 16px; border-radius: 4px; text-decoration: none; font-weight: bold; box-sizing: border-box; }
        .btn-daftar:hover { background-color: #218838; }

        .alert { padding: 10px; margin-bottom: 15px; border-radius: 4px; text-align: center; font-size: 14px; }
        .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .danger { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>

<div class="container">
    <h2>Tambah Mahasiswa Baru</h2>
    
    <?php echo $pesan; ?>

    <form action="" method="POST">
        <div class="form-group">
            <label>NIM</label>
            <input type="text" name="nim" required placeholder="Contoh: 220101001">
        </div>
        
        <div class="form-group">
            <label>Nama Lengkap</label>
            <input type="text" name="nama" required placeholder="Masukkan nama lengkap">
        </div>
        
        <div class="form-group">
            <label>Program Studi</label>
            <select name="prodi" required>
                <option value="">-- Pilih Prodi --</option>
                <option value="Ilmu Komputer">Ilmu Komputer</option>
                <option value="Sistem Informasi">Sistem Informasi</option>
                <option value="Sains Data">Sains Data</option>
                <option value="Matematika">Matematika</option>
            </select>
        </div>
        
        <div class="form-group">
            <label>No. HP</label>
            <input type="tel" name="no_hp" required placeholder="Contoh: 081234567xxx">
        </div>
        
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" required placeholder="Contoh: mhs@kampus.ac.id">
        </div>
        
        <div class="btn-group">
            <a href="pencarian_pengguna.php" class="btn-daftar">Lihat Daftar</a>
            <button type="submit" name="simpan">Simpan Data</button>
        </div>
    </form>
</div>

</body>
</html>
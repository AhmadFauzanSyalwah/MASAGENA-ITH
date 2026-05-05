<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Input Data Mahasiswa - Masagena ITH</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 50px; }
        .container { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); width: 400px; margin: auto; }
        h2 { text-align: center; color: #333; }
        label { display: block; margin: 10px 0 5px; }
        input, select { width: 100%; padding: 8px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; padding: 10px; background-color: #4CAF50; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; }
        button:hover { background-color: #45a049; }
        .back-link { display: block; text-align: center; margin-top: 15px; text-decoration: none; color: #666; font-size: 14px; }
    </style>
</head>
<body>

<div class="container">
    <h2>Form Input Mahasiswa</h2>
    
    <form action="" method="POST">
        <label>Nama Mahasiswa</label>
        <input type="text" name="nama_mahasiswa" placeholder="Masukkan nama lengkap" required>

        <label>Program Studi</label>
        <select name="prodi" required>
            <option value="">-- Pilih Prodi --</option>
            <option value="Informatika">Informatika</option>
            <option value="Sistem Informasi">Sistem Informasi</option>
            <option value="Teknik Komputer">Teknik Komputer</option>
        </select>

        <label>NIM</label>
        <input type="text" name="nim" placeholder="Masukkan NIM" required>

        <label>Password</label>
        <input type="password" name="pasword" placeholder="Masukkan password" required>

        <button type="submit" name="simpan">Simpan Data</button>
    </form>

    <?php
    if(isset($_POST['simpan'])){
        // Gunakan @ untuk meredam pesan error koneksi yang mengganggu layout
        include 'koneksi.php';

        $nama  = mysqli_real_escape_string($koneksi, $_POST['nama_mahasiswa']);
        $prodi = mysqli_real_escape_string($koneksi, $_POST['prodi']);
        $nim   = mysqli_real_escape_string($koneksi, $_POST['nim']);
        $pass  = mysqli_real_escape_string($koneksi, $_POST['pasword']);

        $sql = "INSERT INTO tbmahasiswa (nama_mahasiswa, prodi, nim, pasword) 
                VALUES ('$nama', '$prodi', '$nim', '$pass')";

        if(mysqli_query($koneksi, $sql)){
            // REVISI: Pastikan nama file ini sesuai dengan file tabel kamu
            echo "<script>
                    alert('Data Berhasil Disimpan!');
                    window.location.href='koneksi.php'; 
                  </script>";
        } else {
            echo "<p style='color:red;'>Gagal menyimpan: " . mysqli_error($koneksi) . "</p>";
        }
    }
    ?>
    
    <!-- Sesuaikan link ini dengan nama file tempat kamu menampilkan tabel -->
    <a href="koneksi.php" class="back-link">← Lihat Daftar Mahasiswa</a>
</div>

</body>
</html>
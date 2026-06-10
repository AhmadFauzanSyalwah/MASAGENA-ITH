<?php
require_once 'koneksi.php';

if (isset($_POST['submit'])) {
    $id_konten = rand(1000, 9999); // Mengakali non-auto_increment bawaan tabel Anda
    $judul     = $_POST['judul_kegiatan'];
    $isi       = $_POST['isi_kegiatan'];

    $arrayNamaFoto = [];

    if (!empty($_FILES['fotos']['name'][0])) {
        $uploadDir = "uploads/images/";
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        foreach ($_FILES['fotos']['name'] as $key => $val) {
            $namaFileAsli = $_FILES['fotos']['name'][$key];
            $tmpName      = $_FILES['fotos']['tmp_name'][$key];
            
            $namaFotoUnik = time() . "_" . rand(10,99) . "_" . basename($namaFileAsli);
            $targetPath   = $uploadDir . $namaFotoUnik;

            if (move_uploaded_file($tmpName, $targetPath)) {
                $arrayNamaFoto[] = $namaFotoUnik;
            }
        }
    }

    // Menggabungkan array nama file menjadi satu teks kalimat dipisahkan tanda koma (Sesuai SRS)
    $stringFotoDatabase = !empty($arrayNamaFoto) ? implode(',', $arrayNamaFoto) : null;

    try {
        $sql  = "INSERT INTO konten_kegiatan (id_konten, judul_kegiatan, isi_kegiatan, foto) VALUES (:id, :judul, :isi, :foto)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':id'    => $id_konten,
            ':judul' => $judul,
            ':isi'   => $isi,
            ':foto'  => $stringFotoDatabase
        ]);
        
        // Setelah sukses input, lempar kembali ke halaman utama dashboard Anda
        header("Location: index.php");
        exit();
    } catch (PDOException $e) {
        die("[SQL ERROR] Gagal simpan data: " . $e->getMessage());
    }
}
?>
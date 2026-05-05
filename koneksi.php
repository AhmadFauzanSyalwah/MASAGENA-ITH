<?php
$host     = "localhost";
$user     = "root";
$password = ""; 
$database = "masagena-ith";

$koneksi = mysqli_connect($host, $user, $password, $database);

if (!$koneksi) {
    die("Koneksi database gagal : " . mysqli_connect_error());
}

// --- BAGIAN MENAMPILKAN DATA ---

// 1. Ambil data dari tabel tbmahasiswa
$query = mysqli_query($koneksi, "SELECT * FROM tbmahasiswa");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Tampil Data Mahasiswa</title>
    <style>
        table { border-collapse: collapse; width: 50%; margin-top: 20px; }
        table, th, td { border: 1px solid black; }
        th, td { padding: 2px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>

    <h2>Daftar Mahasiswa dari Database</h2>

    <table>
        <tr>
            <th>ID</th>
            <th>Nama Mahasiswa</th>
            <th>Prodi</th>
            <th>NIM</th>
        </tr>

        <?php
        // 2. Gunakan perulangan while untuk menampilkan setiap baris data
        while($data = mysqli_fetch_array($query)) {
            echo "<tr>";
            echo "<td>" . $data['id_mahasiswa'] . "</td>";
            echo "<td>" . $data['nama_mahasiswa'] . "</td>";
            echo "<td>" . $data['prodi'] . ".</td>";
             echo "<td>" . $data['nim'] . ".</td>";
            echo "</tr>";
        }
        ?>
    </table>

</body>
</html>
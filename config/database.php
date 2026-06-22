<?php
// Koneksi database MASAGENA-ITH
// Jika project kamu sudah punya connection.php yang berjalan, kamu boleh tetap pakai file lama.

$host = 'localhost';
$user = 'root';
$pass = 'Bintang30';
$db   = 'masagena-ith';

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die('Koneksi database gagal: ' . mysqli_connect_error());
}

mysqli_set_charset($conn, 'utf8mb4');
?>

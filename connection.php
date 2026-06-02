<?php
// Koneksi ke database masagena-ith
$host = "localhost";
$user = "root";
$pass = "";
$db   = "masagena-ith";

$conn = new mysqli($host, $user, $pass, $db);

// Cek apakah koneksi berhasil atau gagal
if ($conn->connect_error) {
    die("Koneksi ke database gagal: " . $conn->connect_error);
}
?>
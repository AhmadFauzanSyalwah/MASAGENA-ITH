<?php
$host     = "localhost";
$database = "masagena-ith"; // SELESAI: Dikunci ke nama asli database Anda
$username = "root";
$password = "";

try {
    // Membuka koneksi menggunakan PDO khusus untuk menghandle nama dengan karakter khusus (-)
    $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("❌ Koneksi database gagal: " . $e->getMessage());
}
?>
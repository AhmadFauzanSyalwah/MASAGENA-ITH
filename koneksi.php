<?php
$host = "localhost";
$user = "root"; // Sesuaikan dengan username database Anda
$pass = ""; // Sesuaikan dengan password database Anda
$db   = "masagena-ith";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    // Set error mode ke exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Koneksi gagal: " . $e->getMessage());
}
?>
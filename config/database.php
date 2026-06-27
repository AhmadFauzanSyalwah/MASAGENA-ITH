<?php
<<<<<<< HEAD
// config/database.php
$host = 'localhost';
$dbname = 'masagena-ith'; // sesuaikan
$username = 'root';
$password = '';

// Koneksi PDO (untuk auth dan lainnya)
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi PDO gagal: " . $e->getMessage());
}

// Koneksi MySQLi (untuk file yang pakai mysqli)
$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Koneksi MySQLi gagal: " . $conn->connect_error);
}

// Definisikan BASE_URL
if (!defined('BASE_URL')) {
    define('BASE_URL', '/MASAGENA-ITH');
}

// Session start
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
=======
$host = 'localhost';
$dbname = 'masagena-ith';
$username = 'root';
$password = '';

try {
    // Variabel ini wajib bernama $pdo agar sesuai dengan superadmin.php
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Koneksi gagal: " . $e->getMessage());
}
?>  
>>>>>>> 9e4b9b789696603edaa30fd5aeb277ddc8239c7c

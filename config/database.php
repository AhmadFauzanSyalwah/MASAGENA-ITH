<?php
$host   = 'localhost';
$port   = '5432';
$dbname = 'masagena_ith';
$user   = 'postgres';
$pass   = '241011057';

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
    $conn = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}

// Definisikan BASE_URL setelah koneksi berhasil
define('BASE_URL', '/MASAGENA-ITH');
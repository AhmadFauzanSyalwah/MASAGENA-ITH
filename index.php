<?php
require_once __DIR__ . '/config/database.php';
if (isset($conn)) {
    echo "Koneksi berhasil!";
} else {
    echo "Koneksi gagal!";
}
<?php
require 'koneksi.php'; // Memanggil koneksi database Anda

try {
    // 1. Ambil semua data mahasiswa
    $sql = "SELECT id_mahasiswa, password FROM tbmahasiswa";
    $stmt = $pdo->query($sql);
    $users = $stmt->fetchAll();

    $counter = 0;
    foreach ($users as $user) {
        $current_password = $user['password'];

        // Cek apakah password SUDAH berbentuk hash (Bcrypt selalu diawali $2y$)
        if (substr($current_password, 0, 4) !== '$2y$') {
            // Jika belum berbentuk hash, kita hash password teks biasa tersebut
            $new_hash = password_hash($current_password, PASSWORD_DEFAULT);

            // Update ke database
            $updateSql = "UPDATE tbmahasiswa SET password = :new_hash WHERE id_mahasiswa = :id";
            $updateStmt = $pdo->prepare($updateSql);
            $updateStmt->execute([
                ':new_hash' => $new_hash,
                ':id' => $user['id_mahasiswa']
            ]);
            $counter++;
        }
    }

    echo "Selesai! Sebanyak $counter password mahasiswa berhasil diubah menjadi hash.";

} catch (PDOException $e) {
    die("Terjadi kesalahan: " . $e->getMessage());
}
?>
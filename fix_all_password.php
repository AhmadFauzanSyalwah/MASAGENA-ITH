<?php
require_once 'config/database.php';

// Plain password standar
$adminPass    = 'admin123';
$pengurusPass = 'pengurus123';
$mhsPass      = 'mhs123';

// Generate hash yang valid
$adminHash    = password_hash($adminPass, PASSWORD_BCRYPT);
$pengurusHash = password_hash($pengurusPass, PASSWORD_BCRYPT);
$mhsHash      = password_hash($mhsPass, PASSWORD_BCRYPT);

try {
    // Update semua admin
    $stmt = $pdo->prepare("UPDATE users SET password = :pw WHERE peran = 'admin'");
    $stmt->execute([':pw' => $adminHash]);

    // Update semua pengurus
    $stmt = $pdo->prepare("UPDATE users SET password = :pw WHERE peran = 'pengurus'");
    $stmt->execute([':pw' => $pengurusHash]);

    // Update semua mahasiswa
    $stmt = $pdo->prepare("UPDATE users SET password = :pw WHERE peran = 'mahasiswa'");
    $stmt->execute([':pw' => $mhsHash]);

    echo "✅ Semua password berhasil diperbarui!<br><br>";
    echo "🔹 Admin: email admin@ith.ac.id / password <b>$adminPass</b><br>";
    echo "🔹 Pengurus: semua email berdomain @ith.ac.id (selain admin & mahasiswa) / password <b>$pengurusPass</b><br>";
    echo "🔹 Mahasiswa: semua email berdomain @ith.ac.id (contoh andi@mahasiswa.ith.ac.id) / password <b>$mhsPass</b><br><br>";
    echo "⚠️ <b>Segera hapus file ini</b> setelah digunakan untuk keamanan.";

} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
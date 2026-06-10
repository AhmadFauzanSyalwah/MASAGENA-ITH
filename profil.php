<?php
require_once 'koneksi.php';

// Mengambil total jumlah kegiatan yang ada di database masagena-ith
$queryHitung = $pdo->query("SELECT COUNT(*) as total FROM konten_kegiatan");
$dataHitung  = $queryHitung->fetch(PDO::FETCH_ASSOC);
$totalPost   = $dataHitung['total'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Profil Pengirim - Portal Kampus</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background-color: #fafafa; margin: 0; padding: 20px; }
        .profile-container { max-width: 600px; margin: 30px auto; background: white; border: 1px solid #dbdbdb; border-radius: 8px; padding: 30px; display: flex; align-items: center; gap: 40px; }
        .profile-avatar { width: 150px; height: 150px; border-radius: 50%; object-fit: cover; border: 2px solid #dbdbdb; }
        .profile-info h2 { margin: 0 0 10px 0; font-weight: 300; font-size: 28px; }
        .stats { margin-bottom: 15px; font-size: 16px; }
        .bio { font-size: 14px; color: #262626; line-height: 1.5; }
        .btn-back { display: inline-block; margin-top: 15px; background: #0095f6; color: white; padding: 6px 12px; border-radius: 4px; text-decoration: none; font-weight: bold; font-size: 14px; }
    </style>
</head>
<body>

<div class="profile-container">
    <img src="https://via.placeholder.com/150?text=BEM+ITH" class="profile-avatar" alt="Foto Profil">
    
    <div class="profile-info">
        <h2>masagena_ith_organizer</h2>
        
        <div class="stats">
            <b><?php echo $totalPost; ?></b> kiriman kegiatan
        </div>
        
        <div class="bio">
            <b>Badan Eksekutif Mahasiswa (BEM)</b><br>
            Akun resmi pengelola konten informasi, kreativitas, dan agenda kegiatan terpadu Mahasiswa Kampus ITH.<br>
            <i>Database Status: Connected 🟢</i>
        </div>
        
        <a href="index.php" class="btn-back">Kembali ke Dashboard</a>
    </div>
</div>

</body>
</html>
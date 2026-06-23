<?php
// detail_organisasi.php
// Pastikan letak file database sesuai dengan struktur folder Anda
require_once '../../config/database.php'; 

// Ambil ID organisasi dari parameter URL
$id_organisasi = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

if (!$id_organisasi) {
    // Redirect kembali ke halaman organisasi jika ID tidak valid
    header("Location: /MASAGENA-ITH/organisasi.php");
    exit;
}

// Ambil data organisasi dari database berdasarkan ID
try {
    $stmt = $pdo->prepare("SELECT * FROM organisasi WHERE id_organisasi = :id");
    $stmt->execute([':id' => $id_organisasi]);
    $org = $stmt->fetch(PDO::FETCH_ASSOC);

    // Jika organisasi tidak ditemukan, lempar kembali
    if (!$org) {
        header("Location: /MASAGENA-ITH/organisasi.php");
        exit;
    }

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

// Sertakan header halaman dari folder include root web
include '../../include/header.php';
?>

<style>
    /* Styling khusus untuk Detail Organisasi menyesuaikan tema MASAGENA-ITH */
    .detail-organisasi {
        background: var(--white);
        border-radius: var(--radius);
        padding: 2rem;
        box-shadow: var(--shadow-sm);
        margin-top: 1rem;
    }

    .detail-organisasi .org-badge {
        display: inline-block;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        background-color: rgba(255, 160, 7, 0.15);
        color: var(--accent-dark);
        padding: 0.3rem 0.8rem;
        border-radius: 20px;
        font-weight: 700;
        margin-bottom: 1rem;
    }

    .detail-organisasi h1 {
        color: var(--primary);
        font-size: 2rem;
        margin-bottom: 0.5rem;
    }

    .detail-organisasi .meta-info {
        color: var(--text-muted);
        font-size: 0.9rem;
        margin-bottom: 1.5rem;
        display: flex;
        flex-wrap: wrap;
        gap: 1.5rem;
        border-bottom: 1px solid var(--border);
        padding-bottom: 1rem;
    }

    .detail-organisasi .meta-info i {
        color: var(--accent);
        width: 20px;
    }

    .deskripsi-org {
        line-height: 1.8;
        color: var(--text-dark);
        margin-bottom: 2rem;
    }

    .visi-misi-section {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 2rem;
        margin-top: 2rem;
        background-color: var(--bg-body);
        padding: 1.5rem;
        border-radius: var(--radius);
    }

    @media (max-width: 768px) {
        .visi-misi-section {
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }
    }

    .visi-misi-box h3 {
        color: var(--primary);
        border-left: 4px solid var(--accent);
        padding-left: 0.5rem;
        margin-bottom: 0.75rem;
        font-size: 1.2rem;
    }

    .visi-misi-box p, .visi-misi-box ul {
        font-size: 0.9rem;
        color: var(--text-dark);
    }
    
    .action-group {
        margin-top: 2rem;
        display: flex;
        gap: 1rem;
    }
</style>

<div class="dashboard-welcome">
    <h1>Profil Lembaga Kemahasiswaan</h1>
    <p>Informasi detail, visi, misi, dan kepengurusan dari <strong><?= htmlspecialchars($org['nama_organisasi']) ?></strong>.</p>
</div>

<div class="detail-organisasi">
    <span class="org-badge"><?= htmlspecialchars($org['kategori'] ?? 'Organisasi Mahasiswa') ?></span>
    
    <h1><?= htmlspecialchars($org['nama_organisasi']) ?> (<?= htmlspecialchars($org['singkatan'] ?? '') ?>)</h1>
    
    <div class="meta-info">
        <span><i class="fas fa-users"></i> Anggota Aktif: <strong><?= htmlspecialchars($org['jumlah_anggota'] ?? 'Belum diset') ?> Orang</strong></span>
        <span><i class="fas fa-user-tie"></i> Ketua Umum: <strong><?= htmlspecialchars($org['ketua_umum'] ?? '-') ?></strong></span>
        <span><i class="fas fa-door-open"></i> Sekretariat: <strong><?= htmlspecialchars($org['sekretariat'] ?? '-') ?></strong></span>
    </div>

    <div class="deskripsi-org">
        <h3>Tentang Organisasi</h3>
        <p><?= nl2br(htmlspecialchars($org['deskripsi'] ?? 'Deskripsi belum ditambahkan.')) ?></p>
    </div>

    <?php if (!empty($org['visi']) || (!empty($org['misi']))): ?>
    <div class="visi-misi-section">
        <div class="visi-misi-box">
            <h3>Visi</h3>
            <p><?= nl2br(htmlspecialchars($org['visi'] ?? '-')) ?></p>
        </div>
        <div class="visi-misi-box">
            <h3>Misi</h3>
            <?php if (!empty($org['misi'])): ?>
                <?php 
                // Asumsi Misi berupa teks per baris (menggunakan enter) atau list HTML
                echo nl2br(htmlspecialchars($org['misi'])); 
                ?>
            <?php else: ?>
                <p>-</p>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <div class="action-group">
        <a href="/MASAGENA-ITH/organisasi.php" class="btn-cancel"><i class="fas fa-arrow-left"></i> Kembali</a>
        <a href="pendaftaran_anggota.php?id=<?= $org['id_organisasi'] ?>" class="btn-daftar">Gabung Organisasi</a>
    </div>

</div>

<?php 
// Sertakan footer halaman dari folder include root web
include '../../include/footer.php'; 
?>
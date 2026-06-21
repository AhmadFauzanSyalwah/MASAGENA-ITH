<?php
require_once '../../config/session_check.php';
require_once '../../config/database.php';

// Pastikan hanya admin yang bisa mengakses halaman ini
if ($_SESSION['peran'] != 'admin') {
    header("Location: ../" . $_SESSION['peran'] . "/index.php");
    exit();
}

$pesan = '';
$tipe_pesan = '';

// =========================================================================
// 1. ENDPOINT AJAX: MENGAMBIL DETAIL KONTEN, GALERI, LIKES & KOMENTAR
// =========================================================================
if (isset($_GET['action']) && $_GET['action'] == 'get_detail' && isset($_GET['id'])) {
    header('Content-Type: application/json');
    $id_konten = intval($_GET['id']);
    
    try {
        // Ambil Data Utama Konten
        $stmt = $pdo->prepare("
            SELECT k.*, o.nama_organisasi 
            FROM konten_kegiatan k 
            JOIN organisasi o ON k.id_organisasi = o.id_organisasi 
            WHERE k.id_konten = ?
        ");
        $stmt->execute([$id_konten]);
        $konten = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($konten) {
            // Ambil Lampiran langsung dari kolom 'lampiran' di tabel 'konten_kegiatan'
            $lampiran = [];
            if (!empty($konten['lampiran'])) {
                $lampiran[] = ['path_file' => $konten['lampiran']];
            }
            
            // Ambil Total Likes
            $stmtLikes = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE id_konten = ?");
            $stmtLikes->execute([$id_konten]);
            $total_likes = $stmtLikes->fetchColumn();

            // Ambil Data Komentar (Join dengan tbmahasiswa untuk nama)
            $stmtKomentar = $pdo->prepare("
                SELECT c.isi_komentar, c.created_at, m.nama, m.nim 
                FROM komentar c
                LEFT JOIN tbmahasiswa m ON c.id_mahasiswa = m.id_mahasiswa
                WHERE c.id_konten = ?
                ORDER BY c.created_at DESC
            ");
            $stmtKomentar->execute([$id_konten]);
            $komentar = $stmtKomentar->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'status' => 'success',
                'data' => $konten,
                'lampiran' => $lampiran,
                'likes' => $total_likes,
                'komentar' => $komentar
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Konten tidak ditemukan.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

// =========================================================================
// 2. PROSES AKSI (UBAH STATUS & HAPUS)
// =========================================================================
if (isset($_GET['action']) && isset($_GET['id']) && $_GET['action'] !== 'get_detail') {
    $id_konten = intval($_GET['id']);
    $action = $_GET['action'];

    try {
        if ($action === 'toggle_status') {
            $stmt = $pdo->prepare("SELECT status_publikasi FROM konten_kegiatan WHERE id_konten = ?");
            $stmt->execute([$id_konten]);
            $konten = $stmt->fetch();

            if ($konten) {
                $status_baru = ($konten['status_publikasi'] === 'publish') ? 'unpublish' : 'publish';
                $update_stmt = $pdo->prepare("UPDATE konten_kegiatan SET status_publikasi = ? WHERE id_konten = ?");
                $update_stmt->execute([$status_baru, $id_konten]);
                
                $pesan = "Status konten berhasil diubah menjadi <strong>" . ucfirst($status_baru) . "</strong>.";
                $tipe_pesan = "success";
            }
        } elseif ($action === 'delete') {
            // Hapus File Lampiran Secara Fisik dari kolom 'lampiran'
            $stmt_files = $pdo->prepare("SELECT lampiran FROM konten_kegiatan WHERE id_konten = ?");
            $stmt_files->execute([$id_konten]);
            $file = $stmt_files->fetch();
            
            if ($file && !empty($file['lampiran'])) {
                $file_path = '../../uploads/' . $file['lampiran']; // Sesuaikan path upload Anda
                if (file_exists($file_path)) { unlink($file_path); }
            }
            
            // Hapus dari database (Data Likes, Komentar, dan Pendaftaran otomatis terhapus)
            $del_stmt = $pdo->prepare("DELETE FROM konten_kegiatan WHERE id_konten = ?");
            $del_stmt->execute([$id_konten]);
            
            $pesan = "Konten beserta komentar dan lampirannya berhasil dihapus permanen.";
            $tipe_pesan = "success";
        }
        
        header("Location: pengawasan_konten.php?msg=" . urlencode($pesan) . "&type=" . $tipe_pesan);
        exit;
    } catch (PDOException $e) {
        $pesan = "Terjadi kesalahan: " . $e->getMessage();
        $tipe_pesan = "error";
    }
}

// Tangkap Notifikasi
if (isset($_GET['msg'])) {
    $pesan = urldecode($_GET['msg']);
    $tipe_pesan = $_GET['type'] ?? 'info';
}

// =========================================================================
// 3. AMBIL DATA UTAMA KONTEN UNTUK TABEL
// =========================================================================
try {
    $query = "
        SELECT k.id_konten, k.judul, k.kategori, k.status_publikasi, k.created_at, o.nama_organisasi,
               (SELECT COUNT(*) FROM likes WHERE id_konten = k.id_konten) AS total_likes,
               (SELECT COUNT(*) FROM komentar WHERE id_konten = k.id_konten) AS total_komentar
        FROM konten_kegiatan k
        JOIN organisasi o ON k.id_organisasi = o.id_organisasi
        ORDER BY k.created_at DESC
    ";
    $stmt = $pdo->query($query);
    $data_konten = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error Database: " . $e->getMessage());
}

include '../../include/header.php';
?>

<style>
    .page-title { margin-bottom: 20px; font-size: 24px; color: #1F3D68; font-family: 'Montserrat', sans-serif; }
    .alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: bold; }
    .alert-success { background-color: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
    .alert-error { background-color: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
    
    .card { background: #fff; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); padding: 25px; margin-bottom: 30px; border: 1px solid #eee; }
    .card-header { margin-bottom: 20px; border-bottom: 2px solid #f3f4f6; padding-bottom: 10px; display: flex; justify-content: space-between; align-items: center; }
    .card-header h3 { font-size: 18px; color: #1F3D68; margin: 0; display: flex; align-items: center; gap: 8px; }
    
    .data-table { width: 100%; border-collapse: collapse; }
    .data-table th, .data-table td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #eee; font-size: 14px; }
    .data-table th { background: #f9fafb; color: #6b7280; text-transform: uppercase; font-size: 12px; }
    
    .badge { padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; display: inline-block; }
    .status-publish { background: #d1fae5; color: #065f46; }
    .status-unpublish { background: #fef3c7; color: #d97706; }
    
    .interaction-stats { display: flex; gap: 15px; font-size: 13px; color: #4b5563; font-weight: 600;}
    .interaction-stats i { margin-right: 4px; }

    .action-buttons { display: flex; gap: 8px; }
    .btn-sm { padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: bold; cursor: pointer; text-decoration: none; border: none; display: inline-flex; align-items: center; gap: 5px; transition: 0.2s;}
    .btn-detail { background: #3b82f6; color: white; }
    .btn-detail:hover { background: #2563eb; }
    .btn-hide { background: #f59e0b; color: white; }
    .btn-hide:hover { background: #d97706; }
    .btn-show { background: #10b981; color: white; }
    .btn-show:hover { background: #059669; }
    .btn-delete { background: #ef4444; color: white; }
    .btn-delete:hover { background: #dc2626; }

    /* MODAL STYLING */
    .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.6); display: flex; justify-content: center; align-items: center; z-index: 1000; opacity: 0; visibility: hidden; transition: 0.3s ease; }
    .modal-overlay.active { opacity: 1; visibility: visible; }
    .modal-content { background: white; width: 95%; max-width: 900px; max-height: 90vh; border-radius: 12px; display: flex; flex-direction: column; overflow: hidden; transform: translateY(-20px); transition: 0.3s ease; box-shadow: 0 15px 30px rgba(0,0,0,0.2); }
    .modal-overlay.active .modal-content { transform: translateY(0); }
    
    .modal-header { background: #1F3D68; color: white; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; }
    .modal-header h3 { margin: 0; font-size: 18px; }
    .btn-close { background: none; border: none; color: white; font-size: 20px; cursor: pointer; opacity: 0.8; transition: 0.2s; }
    .btn-close:hover { opacity: 1; color: #ef4444; }
    
    .modal-body { padding: 20px; overflow-y: auto; display: flex; flex-direction: column; gap: 20px; }
    .detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; background: #f9fafb; padding: 15px; border-radius: 8px; border: 1px solid #e5e7eb; }
    .detail-item strong { display: block; font-size: 12px; color: #6b7280; text-transform: uppercase; margin-bottom: 3px; }
    .detail-item p { margin: 0; font-size: 14px; color: #111827; font-weight: 500; }
    
    .section-title { font-size: 16px; color: #1F3D68; border-bottom: 2px solid #f3f4f6; padding-bottom: 5px; margin-bottom: 10px; font-weight: bold; display: flex; justify-content: space-between; align-items: center;}
    
    .gallery-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 10px; }
    .gallery-item { border-radius: 8px; overflow: hidden; border: 1px solid #e5e7eb; aspect-ratio: 1; }
    .gallery-item img { width: 100%; height: 100%; object-fit: cover; }
    .gallery-file { padding: 20px; text-align: center; background: #f9fafb; display: flex; flex-direction: column; align-items: center; justify-content: center; aspect-ratio: 1; }
    
    /* STYLING KOMENTAR */
    .comments-container { display: flex; flex-direction: column; gap: 10px; max-height: 300px; overflow-y: auto; padding-right: 5px; }
    .comment-card { background: #f9fafb; border: 1px solid #e5e7eb; padding: 12px; border-radius: 8px; }
    .comment-header { display: flex; justify-content: space-between; margin-bottom: 5px; }
    .comment-name { font-weight: 600; color: #1F3D68; font-size: 13px; }
    .comment-time { font-size: 11px; color: #9ca3af; }
    .comment-body { font-size: 13px; color: #374151; margin: 0; line-height: 1.5; }
    
    @media (max-width: 768px) {
        .detail-grid { grid-template-columns: 1fr; }
    }
</style>

<div style="padding: 20px;">
    <h1 class="page-title"><i class="fa-solid fa-photo-film" style="color: #F59E0B;"></i> Pengawasan Konten</h1>

    <?php if ($pesan): ?>
        <div class="alert <?= $tipe_pesan == 'success' ? 'alert-success' : 'alert-error' ?>">
            <i class="fa-solid <?= $tipe_pesan == 'success' ? 'fa-circle-check' : 'fa-circle-exclamation' ?>"></i> <?= $pesan ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <h3><i class="fa-solid fa-list"></i> Daftar Seluruh Konten Organisasi</h3>
        </div>

        <div style="overflow-x: auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Informasi Konten</th>
                        <th>Organisasi</th>
                        <th>Status</th>
                        <th>Interaksi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data_konten)): ?>
                        <tr><td colspan="5" style="text-align: center; color: #6b7280; padding: 30px 0;">📭 Belum ada konten kegiatan.</td></tr>
                    <?php else: ?>
                        <?php foreach($data_konten as $row): ?>
                            <tr>
                                <td>
                                    <strong style="color: #1F3D68; font-size: 15px; display: block;"><?= htmlspecialchars($row['judul']) ?></strong>
                                    <span style="font-size: 12px; color: #6b7280;"><i class="fa-solid fa-tag"></i> <?= htmlspecialchars($row['kategori']) ?> &nbsp;|&nbsp; <i class="fa-regular fa-calendar"></i> <?= date('d M Y', strtotime($row['created_at'])) ?></span>
                                </td>
                                <td><span style="background: #f3f4f6; padding: 4px 8px; border-radius: 6px; font-weight: 500; font-size: 13px;"><?= htmlspecialchars($row['nama_organisasi']) ?></span></td>
                                <td>
                                    <?php if ($row['status_publikasi'] === 'publish'): ?>
                                        <span class="badge status-publish"><i class="fa-solid fa-globe"></i> Publik</span>
                                    <?php else: ?>
                                        <span class="badge status-unpublish"><i class="fa-solid fa-eye-slash"></i> Tersembunyi</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="interaction-stats">
                                        <span style="color: #ef4444;"><i class="fa-solid fa-heart"></i> <?= $row['total_likes'] ?></span>
                                        <span style="color: #3b82f6;"><i class="fa-solid fa-comments"></i> <?= $row['total_komentar'] ?></span>
                                    </div>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button type="button" class="btn-sm btn-detail" onclick="bukaModal(<?= $row['id_konten'] ?>)">
                                            <i class="fa-solid fa-magnifying-glass"></i> Detail
                                        </button>
                                        
                                        <?php if ($row['status_publikasi'] === 'publish'): ?>
                                            <a href="pengawasan_konten.php?id=<?= $row['id_konten'] ?>&action=toggle_status" class="btn-sm btn-hide"><i class="fa-solid fa-eye-slash"></i> Sembunyikan</a>
                                        <?php else: ?>
                                            <a href="pengawasan_konten.php?id=<?= $row['id_konten'] ?>&action=toggle_status" class="btn-sm btn-show"><i class="fa-solid fa-eye"></i> Tampilkan</a>
                                        <?php endif; ?>
                                        
                                        <a href="pengawasan_konten.php?id=<?= $row['id_konten'] ?>&action=delete" class="btn-sm btn-delete" onclick="return confirm('Hapus permanen konten ini beserta komentar dan lampirannya?');"><i class="fa-solid fa-trash"></i> Hapus</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="modalDetail" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fa-solid fa-circle-info"></i> Review Detail Konten</h3>
            <button class="btn-close" onclick="tutupModal()"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="modal-body">
            
            <div class="detail-grid">
                <div class="detail-item" style="grid-column: 1 / -1;">
                    <strong>Judul Kegiatan</strong>
                    <p id="mdl-judul">-</p>
                </div>
                <div class="detail-item">
                    <strong>Penyelenggara</strong>
                    <p id="mdl-organisasi">-</p>
                </div>
                <div class="detail-item">
                    <strong>Kategori</strong>
                    <p id="mdl-kategori">-</p>
                </div>
                <div class="detail-item">
                    <strong>Tanggal Pelaksanaan</strong>
                    <p id="mdl-tanggal">-</p>
                </div>
                <div class="detail-item" style="grid-column: 1 / -1;">
                    <strong>Deskripsi Konten</strong>
                    <p id="mdl-deskripsi" style="font-weight: normal; white-space: pre-wrap; font-size: 13px;">-</p>
                </div>
            </div>

            <div>
                <div class="section-title">
                    <span><i class="fa-solid fa-images text-[#F59E0B]"></i> Lampiran Konten</span>
                </div>
                <div id="mdl-galeri" class="gallery-grid">
                    </div>
            </div>

            <div>
                <div class="section-title">
                    <span><i class="fa-solid fa-comments text-[#3b82f6]"></i> Ruang Diskusi & Interaksi</span>
                    <span id="mdl-likes-count" style="font-size: 14px; background: #fee2e2; color: #ef4444; padding: 4px 10px; border-radius: 20px;"><i class="fa-solid fa-heart"></i> 0 Likes</span>
                </div>
                <div id="mdl-komentar" class="comments-container">
                    </div>
            </div>

        </div>
    </div>
</div>

<script>
    // Fungsi untuk memformat tanggal ke format lokal Indonesia
    function formatTanggal(dateString) {
        if (!dateString) return '-';
        const options = { day: 'numeric', month: 'long', year: 'numeric' };
        return new Date(dateString).toLocaleDateString('id-ID', options);
    }

    // Fungsi format khusus jika komentar memiliki waktu detail
    function formatTanggalWaktu(dateString) {
        if (!dateString) return '-';
        const options = { day: 'numeric', month: 'long', year: 'numeric', hour:'2-digit', minute:'2-digit' };
        return new Date(dateString).toLocaleDateString('id-ID', options);
    }

    // Fungsi Utama Buka Modal dan Ambil Data via AJAX
    function bukaModal(idKonten) {
        document.getElementById('modalDetail').classList.add('active');
        
        // Reset isi modal dengan loading teks sementara
        document.getElementById('mdl-judul').innerText = "Memuat data...";
        document.getElementById('mdl-galeri').innerHTML = "<p style='font-size: 12px; color: #6b7280;'>Memuat lampiran...</p>";
        document.getElementById('mdl-komentar').innerHTML = "<p style='font-size: 12px; color: #6b7280;'>Memuat komentar...</p>";

        // Fetch API ke endpoint file ini sendiri
        fetch(`pengawasan_konten.php?action=get_detail&id=${idKonten}`)
            .then(response => response.json())
            .then(res => {
                if(res.status === 'success') {
                    // 1. Tampilkan Data Info Dasar
                    const k = res.data;
                    document.getElementById('mdl-judul').innerText = k.judul;
                    document.getElementById('mdl-organisasi').innerText = k.nama_organisasi;
                    document.getElementById('mdl-kategori').innerText = k.kategori;
                    
                    // Menarik data langsung dari tanggal_kegiatan database
                    document.getElementById('mdl-tanggal').innerText = formatTanggal(k.tanggal_kegiatan); 
                    
                    document.getElementById('mdl-deskripsi').innerText = k.deskripsi;
                    
                    // 2. Tampilkan Total Likes
                    document.getElementById('mdl-likes-count').innerHTML = `<i class="fa-solid fa-heart"></i> ${res.likes} Suka`;

                    // 3. Render Lampiran Galeri
                    const galeriContainer = document.getElementById('mdl-galeri');
                    galeriContainer.innerHTML = ''; // bersihkan
                    
                    if(res.lampiran.length > 0) {
                        res.lampiran.forEach(lamp => {
                            const ext = lamp.path_file.split('.').pop().toLowerCase();
                            const isImage = ['jpg','jpeg','png','gif','webp'].includes(ext);
                            const path = `../../uploads/${lamp.path_file}`; // Sesuaikan folder upload Anda

                            if(isImage) {
                                galeriContainer.innerHTML += `
                                    <div class="gallery-item">
                                        <a href="${path}" target="_blank">
                                            <img src="${path}" alt="Lampiran">
                                        </a>
                                    </div>
                                `;
                            } else {
                                galeriContainer.innerHTML += `
                                    <a href="${path}" target="_blank" style="text-decoration: none;">
                                        <div class="gallery-file">
                                            <i class="fa-solid fa-file-pdf" style="font-size: 30px; color: #ef4444; margin-bottom: 5px;"></i>
                                            <span style="font-size: 11px; color: #4b5563; word-break: break-all;">Dokumen Terlampir</span>
                                        </div>
                                    </a>
                                `;
                            }
                        });
                    } else {
                        galeriContainer.innerHTML = '<p style="grid-column: 1 / -1; font-size: 13px; color: #9ca3af; text-align: center; padding: 20px; border: 1px dashed #d1d5db; border-radius: 8px;">Tidak ada lampiran foto/file.</p>';
                    }

                    // 4. Render Riwayat Komentar
                    const komentarContainer = document.getElementById('mdl-komentar');
                    komentarContainer.innerHTML = ''; // bersihkan

                    if(res.komentar.length > 0) {
                        res.komentar.forEach(kom => {
                            const namaMhs = kom.nama ? kom.nama : 'Akun Terhapus';
                            komentarContainer.innerHTML += `
                                <div class="comment-card">
                                    <div class="comment-header">
                                        <span class="comment-name"><i class="fa-solid fa-user-circle" style="color: #9ca3af;"></i> ${namaMhs}</span>
                                        <span class="comment-time">${formatTanggalWaktu(kom.created_at)}</span>
                                    </div>
                                    <p class="comment-body">${kom.isi_komentar}</p>
                                </div>
                            `;
                        });
                    } else {
                        komentarContainer.innerHTML = '<p style="font-size: 13px; color: #9ca3af; text-align: center; padding: 20px; border: 1px dashed #d1d5db; border-radius: 8px;">Belum ada mahasiswa yang berkomentar.</p>';
                    }

                } else {
                    alert('Gagal mengambil data: ' + res.message);
                }
            })
            .catch(err => {
                console.error(err);
                document.getElementById('mdl-judul').innerText = "Gagal memuat data.";
            });
    }

    function tutupModal() {
        document.getElementById('modalDetail').classList.remove('active');
    }

    // Tutup jika klik area gelap di luar kotak modal
    window.onclick = function(event) {
        if (event.target == document.getElementById('modalDetail')) {
            tutupModal();
        }
    }
</script>

<?php include '../../include/footer.php'; ?>
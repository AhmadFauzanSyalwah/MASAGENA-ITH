<?php
// dashboard/mahasiswa/index.php
session_start();
require_once '../../config/session_check.php';
require_once '../../config/database.php';

// Ambil 5 kegiatan terbaru dengan total likes dan total komentar
$stmt = $pdo->query("
    SELECT k.*, o.nama_organisasi,
        (SELECT COUNT(*) FROM likes WHERE id_konten = k.id_konten) as total_likes,
        (SELECT COUNT(*) FROM komentar WHERE id_konten = k.id_konten) as total_komentar
    FROM konten_kegiatan k
    JOIN organisasi o ON k.id_organisasi = o.id_organisasi
    WHERE k.status_publikasi = 'publish'
    ORDER BY k.created_at DESC
    LIMIT 5
");
$kegiatan_terbaru = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ambil pengumuman
$stmt = $pdo->query("
    SELECT * FROM konten_kegiatan
    WHERE kategori = 'pengumuman' AND status_publikasi = 'publish'
    ORDER BY created_at DESC LIMIT 3
");
$pengumuman = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../../include/header.php';
?>

    <style>
        /* Animasi heart */
        .heart-btn {
            background: none;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            padding: 0;
        }
        .heart-icon {
            font-size: 1.2rem;
            transition: transform 0.2s ease;
        }
        .heart-btn:active .heart-icon {
            transform: scale(1.3);
        }
        @keyframes heartBeat {
            0% { transform: scale(1); }
            50% { transform: scale(1.3); }
            100% { transform: scale(1); }
        }
        .heart-animate {
            animation: heartBeat 0.3s ease;
        }
        .like-count, .komentar-count {
            font-size: 0.85rem;
            color: var(--text-muted);
        }
        .card-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 1rem;
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        .interaction-group {
            display: flex;
            gap: 1rem;
            align-items: center;
        }
        .interaction {
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }
        .komentar-link, .share-link {
            display: flex;
            align-items: center;
            gap: 0.3rem;
            text-decoration: none;
            color: var(--text-muted);
            font-size: 0.9rem;
            background: none;
            border: none;
            cursor: pointer;
            padding: 0;
        }
        .komentar-link:hover, .share-link:hover {
            color: var(--accent);
        }
        .btn-sm {
            padding: 0.2rem 0.8rem;
            font-size: 0.75rem;
        }
        /* Efek Bulatan Indikator di Foto (Khas Instagram Carousel) */
    .photo-wrapper {
    position: relative;
    display: inline-block;
}
.indicator-dot {
    position: absolute;
    bottom: 10px;
    right: 10px;
    background: rgba(0, 0, 0, 0.6);
    color: white;
    padding: 3px 8px;
    border-radius: 12px;
    font-size: 0.75rem;
}

/* Efek Modal/Pop-up Foto Full Screen saat Di-tab */
.modal-foto {
    display: none;
    position: fixed;
    z-index: 9999;
    top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(0,0,0,0.9);
    align-items: center;
    justify-content: center;
}
.modal-foto img {
    max-width: 90%;
    max-height: 90%;
    border-radius: 8px;
    box-shadow: 0 4px 20px rgba(255,255,255,0.1);
}

    </style>

    <div class="dashboard-welcome">
        <h1>Selamat datang, <?= htmlspecialchars($_SESSION['nama']) ?></h1>
        <p>Ini adalah portal informasi kegiatan kemahasiswaan ITH.</p>
    </div>

    <div class="dashboard-grid">
        <div class="main-content">
            <h2>Kegiatan Terbaru</h2>
            <?php if (count($kegiatan_terbaru) > 0): ?>
                <div class="kegiatan-list">
                    <?php foreach ($kegiatan_terbaru as $k): ?>
                        <div class="card" data-id="<?= $k['id_konten'] ?>">
                            <h3><a href="detail_kegiatan.php?id=<?= $k['id_konten'] ?>"><?= htmlspecialchars($k['judul']) ?></a></h3>
                            <p class="meta">Organisasi: <?= htmlspecialchars($k['nama_organisasi']) ?> | 🗓️ <?= $k['tanggal_kegiatan'] ?></p>
                            <p><?= nl2br(htmlspecialchars(substr($k['deskripsi'], 0, 150))) ?>...</p>
                            <div class="card-actions">
                                <div class="interaction-group">
                                    <!-- Tombol Like -->
                                    <div class="interaction">
                                        <button class="heart-btn" data-id="<?= $k['id_konten'] ?>">
                                            <i class="far fa-heart heart-icon"></i>
                                            <span class="like-count"><?= $k['total_likes'] ?></span>
                                        </button>
                                    </div>
                                    <!-- Tombol Komentar (link ke detail dengan anchor #komentar) -->
                                    <div class="interaction">
                                        <a href="detail_kegiatan.php?id=<?= $k['id_konten'] ?>#komentar" class="komentar-link">
                                            <i class="far fa-comment"></i>
                                            <span class="komentar-count"><?= $k['total_komentar'] ?></span>
                                        </a>
                                    </div>
                                    <!-- Tombol Share -->
                                    <div class="interaction">
                                        <button class="share-link" data-url="<?= 'http://' . $_SERVER['HTTP_HOST'] . '/MASAGENA-ITH/dashboard/mahasiswa/detail_kegiatan.php?id=' . $k['id_konten'] ?>">
                                            <i class="fas fa-share-alt"></i> Share
                                        </button>
                                    </div>
                                </div>
                                <!-- Tombol Lihat Detail -->
                                <a href="detail_kegiatan.php?id=<?= $k['id_konten'] ?>" class="btn-sm">Lihat Detail</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>Belum ada kegiatan terbaru.</p>
            <?php endif; ?>
        </div>

        <aside class="sidebar">
            <h2>Pengumuman</h2>
            <?php if (count($pengumuman) > 0): ?>
                <ul class="pengumuman-list">
                    <?php foreach ($pengumuman as $p): ?>
                        <li>
                            <a href="detail_kegiatan.php?id=<?= $p['id_konten'] ?>"><?= htmlspecialchars($p['judul']) ?></a>
                            <span class="date"><?= date('d/m/Y', strtotime($p['created_at'])) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>Tidak ada pengumuman.</p>
            <?php endif; ?>
        </aside>
    </div>

    <script>
        // AJAX like tanpa reload
        document.querySelectorAll('.heart-btn').forEach(btn => {
            btn.addEventListener('click', async function(e) {
                e.preventDefault();
                const icon = this.querySelector('.heart-icon');
                const countSpan = this.querySelector('.like-count');
                const kegiatanId = this.dataset.id;

                icon.classList.add('heart-animate');
                setTimeout(() => icon.classList.remove('heart-animate'), 300);

                try {
                    const response = await fetch('/MASAGENA-ITH/ajax/like.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: 'id=' + kegiatanId
                    });
                    const data = await response.json();
                    if (data.status === 'liked') {
                        icon.classList.remove('far');
                        icon.classList.add('fas');
                        icon.style.color = '#ff4757';
                    } else if (data.status === 'unliked') {
                        icon.classList.remove('fas');
                        icon.classList.add('far');
                        icon.style.color = '';
                    }
                    countSpan.textContent = data.likes;
                } catch (err) {
                    console.error(err);
                }
            });
        });

        // Share button (copy link atau Web Share)
        document.querySelectorAll('.share-link').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const url = this.dataset.url;
                if (navigator.share) {
                    navigator.share({
                        title: '<?= addslashes($k['judul'] ?? 'Kegiatan') ?>',
                        url: url
                    }).catch(() => {});
                } else {
                    navigator.clipboard.writeText(url);
                    alert('Link kegiatan disalin ke clipboard!');
                }
            });
        });
        // Fitur Tab Foto agar Menampilkan Ukuran Penuh (Full Screen)
document.querySelectorAll('.card img').forEach(foto => {
    foto.style.cursor = 'pointer'; // Ubah kursor jadi bentuk jari tangan
    foto.addEventListener('click', function() {
        const srcFoto = this.getAttribute('src');
        if(srcFoto) {
            document.getElementById('imgFull').setAttribute('src', srcFoto);
            document.getElementById('popupFoto').style.display = 'flex';
        }
    });
});
    </script> 

    <div id="popupFoto" class="modal-foto" onclick="this.style.display='none'">
        <img id="imgFull" src="" alt="Foto Full">
    </div>

<?php include '../../include/footer.php'; ?>
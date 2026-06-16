<?php
// dashboard/mahasiswa/detail_kegiatan.php
session_start();
require_once '../../config/session_check.php';
require_once '../../config/database.php';

$id_konten = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id_konten) {
    header("Location: index.php");
    exit();
}

// Ambil data kegiatan
$stmt = $pdo->prepare("
    SELECT k.*, o.nama_organisasi
    FROM konten_kegiatan k
    JOIN organisasi o ON k.id_organisasi = o.id_organisasi
    WHERE k.id_konten = ? AND k.status_publikasi = 'publish'
");
$stmt->execute([$id_konten]);
$kegiatan = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$kegiatan) {
    echo "Kegiatan tidak ditemukan.";
    include '../../include/footer.php';
    exit();
}

// Proses komentar via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['komentar'])) {
    $isi = trim($_POST['isi_komentar']);
    if ($isi !== '') {
        $stmt = $pdo->prepare("INSERT INTO komentar (isi_komentar, id_user, id_konten) VALUES (?, ?, ?)");
        $stmt->execute([$isi, $_SESSION['user_id'], $id_konten]);
    }
    header("Location: detail_kegiatan.php?id=$id_konten");
    exit();
}

// Total like dan status user
$stmt = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE id_konten = ?");
$stmt->execute([$id_konten]);
$total_likes = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE id_konten = ? AND id_user = ?");
$stmt->execute([$id_konten, $_SESSION['user_id']]);
$user_liked = $stmt->fetchColumn() > 0;

// Total komentar
$stmt = $pdo->prepare("SELECT COUNT(*) FROM komentar WHERE id_konten = ?");
$stmt->execute([$id_konten]);
$total_komentar = $stmt->fetchColumn();

// Daftar komentar
$stmt = $pdo->prepare("
    SELECT c.*, u.nama
    FROM komentar c
    JOIN users u ON c.id_user = u.id_user
    WHERE c.id_konten = ?
    ORDER BY c.created_at DESC
");
$stmt->execute([$id_konten]);
$komentar = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Status pendaftaran
$stmt = $pdo->prepare("SELECT status_pendaftaran FROM pendaftaran WHERE id_user = ? AND id_konten = ?");
$stmt->execute([$_SESSION['user_id'], $id_konten]);
$status_daftar = $stmt->fetchColumn();

include '../../include/header.php';
?>

    <style>
        .action-buttons {
            display: flex;
            gap: 1.5rem;
            align-items: center;
            margin: 1.5rem 0;
            flex-wrap: wrap;
        }
        .action-btn {
            background: none;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.4rem 1rem;
            border-radius: 40px;
            cursor: pointer;
            font-size: 1rem;
            transition: background 0.2s;
            text-decoration: none;
            color: var(--text-dark);
        }
        .action-btn:hover {
            background-color: rgba(0,0,0,0.05);
        }
        .heart-icon, .comment-icon, .share-icon {
            font-size: 1.4rem;
        }
        .daftar-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background-color: var(--accent);
            color: var(--primary);
            padding: 0.5rem 1.2rem;
            border-radius: 40px;
            text-decoration: none;
            font-weight: 600;
            margin: 1rem 0;
        }
        .komentar-form {
            margin: 1.5rem 0;
            padding-top: 1rem;
            border-top: 1px solid var(--border);
        }
        .komentar-form textarea {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            font-family: inherit;
            resize: vertical;
        }
        .kirim-btn {
            background-color: var(--accent);
            border: none;
            padding: 0.5rem 1.2rem;
            border-radius: 40px;
            cursor: pointer;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 0.5rem;
        }
        .komentar-item {
            background: var(--bg-body);
            padding: 1rem;
            border-radius: var(--radius);
            margin-bottom: 1rem;
        }
        .komentar-item strong {
            color: var(--primary);
        }
        .komentar-item .date {
            font-size: 0.7rem;
            color: var(--text-muted);
            margin-left: 0.5rem;
        }
        .pendaftaran-status {
            padding: 0.5rem 1rem;
            background-color: #e6f7e6;
            border-radius: 30px;
            display: inline-block;
            font-size: 0.9rem;
            margin: 1rem 0;
        }
    </style>

    <div class="detail-kegiatan">
        <h1><?= htmlspecialchars($kegiatan['judul']) ?></h1>
        <p class="meta">
            <strong>Organisasi:</strong> <?= htmlspecialchars($kegiatan['nama_organisasi']) ?> |
            <strong>Tanggal:</strong> <?= date('d/m/Y', strtotime($kegiatan['tanggal_kegiatan'])) ?>
        </p>
        <div class="deskripsi">
            <?= nl2br(htmlspecialchars($kegiatan['deskripsi'])) ?>
        </div>
        <?php if ($kegiatan['lampiran']): ?>
            <p><strong>Lampiran:</strong>
                <a href="/MASAGENA-ITH/uploads/<?= $kegiatan['lampiran'] ?>" download><i class="fas fa-download"></i> Download</a>
            </p>
        <?php endif; ?>

        <!-- Tombol Like, Komentar, Share -->
        <div class="action-buttons">
            <button id="likeButton" class="action-btn" data-id="<?= $id_konten ?>">
                <i class="<?= $user_liked ? 'fas fa-heart' : 'far fa-heart' ?> heart-icon" id="likeIcon"></i>
                <span id="likeCount"><?= $total_likes ?></span>
            </button>
            <a href="#komentar-section" class="action-btn" id="commentBtn">
                <i class="far fa-comment comment-icon"></i>
                <span id="commentCount"><?= $total_komentar ?></span>
            </a>
            <button id="shareButton" class="action-btn">
                <i class="fas fa-share-alt share-icon"></i> Share
            </button>
        </div>

        <!-- Pendaftaran dengan ikon plus -->
        <div>
            <?php if ($status_daftar === false): ?>
                <a href="pendaftaran.php?kegiatan_id=<?= $id_konten ?>" class="daftar-btn">
                    <i class="fas fa-plus-circle"></i> Daftar Kegiatan
                </a>
            <?php else: ?>
                <div class="pendaftaran-status">
                    ✅ Status pendaftaran Anda: <strong><?= ucfirst($status_daftar) ?></strong>
                </div>
            <?php endif; ?>
        </div>

        <!-- Komentar Section -->
        <div id="komentar-section" class="komentar-section">
            <h3>Komentar</h3>
            <form method="POST" class="komentar-form">
                <textarea name="isi_komentar" rows="3" placeholder="Tulis komentar Anda..." required></textarea><br>
                <button type="submit" name="komentar" class="kirim-btn">
                    <i class="fas fa-paper-plane"></i> Kirim Komentar
                </button>
            </form>
            <div id="komentarList">
                <?php if (count($komentar) > 0): ?>
                    <?php foreach ($komentar as $komen): ?>
                        <div class="komentar-item">
                            <strong><?= htmlspecialchars($komen['nama']) ?></strong>
                            <span class="date"><?= date('d/m/Y H:i', strtotime($komen['created_at'])) ?></span>
                            <p><?= nl2br(htmlspecialchars($komen['isi_komentar'])) ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Belum ada komentar. Jadilah yang pertama!</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // AJAX Like
        const likeBtn = document.getElementById('likeButton');
        if (likeBtn) {
            likeBtn.addEventListener('click', async function(e) {
                e.preventDefault();
                const icon = this.querySelector('.heart-icon');
                const countSpan = document.getElementById('likeCount');
                const kegiatanId = this.dataset.id;

                icon.style.transform = 'scale(1.2)';
                setTimeout(() => icon.style.transform = '', 200);

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
                    console.error('Like error:', err);
                }
            });
        }

        // Share
        document.getElementById('shareButton')?.addEventListener('click', function() {
            const url = window.location.href;
            if (navigator.share) {
                navigator.share({ url: url }).catch(() => {});
            } else {
                navigator.clipboard.writeText(url);
                alert('Link kegiatan disalin!');
            }
        });
    </script>

<?php include '../../include/footer.php'; ?>
<?php 
require_once 'koneksi.php'; 

// Mengambil data kegiatan menggunakan kolom bawaan asli tabel konten_kegiatan Anda
$queryAgenda = $pdo->query("SELECT id_konten, judul_kegiatan as title, tanggal_upload as start FROM konten_kegiatan");
$eventsJson  = json_encode($queryAgenda->fetchAll(PDO::FETCH_ASSOC));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Portal Kampus - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background-color: #fafafa; margin: 0; padding: 20px; }
        .nav-tabs { display: flex; justify-content: center; margin-bottom: 25px; border-bottom: 1px solid #dbdbdb; }
        .tab-link { padding: 15px 30px; font-weight: bold; cursor: pointer; color: #8e8e8e; text-decoration: none; border-bottom: 2px solid transparent; }
        .tab-link.active { color: #262626; border-bottom: 2px solid #262626; }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        
        .instagram-feed { max-width: 600px; margin: 0 auto; }
        .insta-card { background: white; border: 1px solid #dbdbdb; border-radius: 8px; margin-bottom: 24px; overflow: hidden; }
        .insta-header { padding: 14px; font-weight: bold; border-bottom: 1px solid #efefef; }
        
        .carousel-container { position: relative; width: 100%; height: 400px; display: flex; overflow-x: auto; scroll-snap-type: x mandatory; }
        .carousel-container img { width: 100%; height: 100%; object-fit: cover; flex-shrink: 0; scroll-snap-align: start; }
        
        .insta-body { padding: 14px; }
        #calendar-box { background: white; padding: 20px; border-radius: 8px; border: 1px solid #dbdbdb; max-width: 900px; margin: 0 auto; }
    </style>
</head>
<body>

<div class="nav-tabs">
    <div class="tab-link active" onclick="bukaTab('feed', this)">📸 POSTINGAN</div>
    <div class="tab-link" onclick="bukaTab('agenda', this)">📅 AGENDA </div>
</div>

<div id="feed" class="tab-content active">
    <div class="instagram-feed">
        <center><a href="form_tambah.php" style="background:#0095f6; color:white; padding:8px 16px; border-radius:4px; text-decoration:none; font-weight:bold;">+ Postingan Baru</a></center><br>
        
        <?php
        $sql = "SELECT * FROM konten_kegiatan ORDER BY id_konten DESC";
        foreach ($pdo->query($sql) as $row) {
            ?>
            <div class="insta-card">
                <div class="insta-header">🏛️<?php echo htmlspecialchars($row['judul_kegiatan']); ?></div>
                
                <div class="carousel-container">
                    <?php
                    if (!empty($row['foto'])) {
                        // Memecah teks koma menjadi kepingan gambar fisik
                        $listFoto = explode(',', $row['foto']);
                        foreach ($listFoto as $fotoTunggal) {
                            echo '<img src="uploads/images/'.trim($fotoTunggal).'">';
                        }
                    } else {
                        echo '<img src="https://via.placeholder.com/600x400?text=Tidak+Ada+Foto">';
                    }
                    ?>
                </div>
                
                <div class="insta-body"><center> ➔</center/small>
                    <p><b>Deskripsi:</b> <?php echo nl2br(htmlspecialchars($row['isi_kegiatan'])); ?></p>
                    <span style="font-size:11px; color:#8e8e8e;">Diunggah Pada: <?php echo $row['tanggal_upload']; ?></span>
                </div>
            </div>
            <?php
        }
        ?>
    </div>
</div>

<div id="agenda" class="tab-content">
    <div id="calendar-box">
        <div id="calendar"></div>
    </div>
</div>

<script>
    function bukaTab(tabId, elemen) {
        document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
        document.querySelectorAll('.tab-link').forEach(link => link.classList.remove('active'));
        document.getElementById(tabId).classList.add('active');
        elemen.classList.add('active');
        
        if(tabId === 'agenda') { calendar.render(); }
    }

    var calendar;
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'id',
            selectable: true, // 1. AKTIFKAN FITUR AGAR KOTAK TANGGAL BISA DI-KLIK
            events: <?php echo $eventsJson; ?>, // Menampilkan data dari database
            
            // 2. LOGIKA KETIKA TANGGAL DI-KLIK
            select: function(info) {
                // Ketika admin mengklik tanggal di kalender, otomatis dialihkan ke form_tambah.php
                // Kita juga bisa mengirimkan data tanggal yang dipilih lewat URL pembantu
                var konfirmasi = confirm("Apakah Anda ingin menambah kegiatan pada tanggal " + info.startStr + "?");
                if (konfirmasi) {
                    window.location.href = "form_tambah.php?tanggal=" + info.startStr;
                }
            }
        });
    });
</script>
</body>
</html>
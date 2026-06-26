<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Menggunakan file konfigurasi bawaan kelompok Anda
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../include/components.php';
require_once __DIR__ . '/../../include/pendaftaran-helper.php';
require_once '../../config/session_check.php';

// Catatan: Pastikan file database.php di kelompok Anda menggunakan variabel $pdo.
// Jika kelompok Anda menggunakan $conn, ganti $pdo di bawah ini menjadi $conn.
$queryAgenda = $pdo->query("SELECT id_konten, judul_kegiatan as title, tanggal_upload as start FROM konten_kegiatan");
$eventsJson  = json_encode($queryAgenda->fetchAll(PDO::FETCH_ASSOC));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Portal Kampus - Agenda Kegiatan</title>
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <style>
        body { 
            font-family: 'Segoe UI', Arial, sans-serif; 
            background-color: #fafafa; 
            margin: 0; 
            padding: 20px; 
        }
        #calendar-box { 
            background: white; 
            padding: 20px; 
            border-radius: 8px; 
            border: 1px solid #dbdbdb; 
            max-width: 900px; 
            margin: 40px auto; 
        }
    </style>
</head>
<body>

<div id="calendar-box">
    <div id="calendar"></div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        if (calendarEl) {
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'id',
                events: <?php echo $eventsJson; ?>
            });
            calendar.render();
        }
    });
</script>
</body>
</html>
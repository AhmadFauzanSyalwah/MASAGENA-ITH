<?php
// dashboard/mahasiswa/print_agenda.php
session_start();
require_once '../../config/database.php';

$type = isset($_GET['type']) ? $_GET['type'] : 'month';
$year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
$month = isset($_GET['month']) ? (int)$_GET['month'] : date('n');

function getIndonesianMonth($month) {
    $months = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',
               7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];
    return $months[$month];
}

// ============================================================
// GENERATE HTML PRINT
// ============================================================
$html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Kalender Agenda</title>';
$html .= '<style>
    body { font-family: Arial, sans-serif; padding: 20px; color: #333; }
    h1 { text-align: center; color: #071C34; border-bottom: 3px solid #FFA007; padding-bottom: 10px; }
    .month-title { font-size: 1.5rem; font-weight: bold; color: #071C34; margin: 20px 0 10px 0; }
    .month-title span { color: #FFA007; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
    th { background: #071C34; color: #fff; padding: 6px; text-align: center; font-size: 0.8rem; }
    td { border: 1px solid #ddd; padding: 6px; vertical-align: top; height: 50px; width: 14.28%; }
    td .day-num { font-weight: bold; font-size: 0.85rem; }
    td .event { background: #FFA007; color: #071C34; padding: 1px 4px; border-radius: 3px; font-size: 0.6rem; margin-top: 2px; display: block; }
    td .today { background: #FFA007; border-radius: 50%; display: inline-block; padding: 0 4px; }
    .event-list { margin-top: 10px; }
    .event-item { padding: 4px 8px; border-left: 3px solid #FFA007; margin-bottom: 3px; background: #f8fafc; font-size: 0.85rem; }
    .event-item .date { font-weight: bold; color: #071C34; }
    .event-item .org { color: #64748b; font-size: 0.75rem; }
    .footer { text-align: center; margin-top: 20px; font-size: 0.75rem; color: #94a3b8; border-top: 1px solid #e9ecef; padding-top: 10px; }
    @page { margin: 1.2cm; }
    .print-page { page-break-after: always; }
    .no-events { color: #94a3b8; font-style: italic; }
</style></head><body>';

if ($type == 'month') {
    // ===== BULAN INI =====
    $start = "$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-01";
    $end = date('Y-m-t', strtotime($start));
    $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
    $firstDay = date('w', strtotime($start));

    $sql = "SELECT k.*, o.nama_organisasi FROM konten_kegiatan k 
            JOIN organisasi o ON k.id_organisasi = o.id_organisasi 
            WHERE k.status_publikasi = 'publik' AND k.tanggal_kegiatan BETWEEN :start AND :end 
            ORDER BY k.tanggal_kegiatan ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':start' => $start, ':end' => $end]);
    $events = $stmt->fetchAll();

    $eventsByDate = [];
    foreach ($events as $e) {
        $dateKey = date('Y-m-d', strtotime($e['tanggal_kegiatan']));
        if (!isset($eventsByDate[$dateKey])) $eventsByDate[$dateKey] = [];
        $eventsByDate[$dateKey][] = $e;
    }

    $html .= '<h1>Kalender Kegiatan ' . getIndonesianMonth($month) . ' ' . $year . '</h1>';
    $html .= '<table>';
    $html .= '<tr><th>Minggu</th><th>Senin</th><th>Selasa</th><th>Rabu</th><th>Kamis</th><th>Jumat</th><th>Sabtu</th></tr>';
    $html .= '<tr>';
    for ($i = 0; $i < $firstDay; $i++) {
        $html .= '<td></td>';
    }
    for ($d = 1; $d <= $daysInMonth; $d++) {
        $dateKey = "$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-" . str_pad($d, 2, '0', STR_PAD_LEFT);
        $isToday = ($dateKey == date('Y-m-d'));
        $html .= '<td>';
        $html .= '<div class="day-num' . ($isToday ? ' today' : '') . '">' . $d . '</div>';
        if (isset($eventsByDate[$dateKey])) {
            foreach (array_slice($eventsByDate[$dateKey], 0, 3) as $ev) {
                $html .= '<span class="event">' . htmlspecialchars(substr($ev['judul'], 0, 25)) . '</span>';
            }
            if (count($eventsByDate[$dateKey]) > 3) {
                $html .= '<span class="event" style="background:#e2e8f0;color:#475569;">+' . (count($eventsByDate[$dateKey]) - 3) . ' lagi</span>';
            }
        }
        $html .= '</td>';
        if (($i + $d) % 7 == 0 && $d < $daysInMonth) {
            $html .= '</tr><tr>';
        }
    }
    $remaining = (7 - (($firstDay + $daysInMonth) % 7)) % 7;
    for ($i = 0; $i < $remaining; $i++) {
        $html .= '<td></td>';
    }
    $html .= '</tr></table>';

    // Daftar kegiatan
    $html .= '<div class="event-list"><h3>Daftar Kegiatan Bulan ' . getIndonesianMonth($month) . '</h3>';
    if (count($events) > 0) {
        foreach ($events as $e) {
            $html .= '<div class="event-item"><span class="date">' . date('d M Y', strtotime($e['tanggal_kegiatan'])) . '</span> - ' . htmlspecialchars($e['judul']) . ' <span class="org">(' . htmlspecialchars($e['nama_organisasi']) . ')</span></div>';
        }
    } else {
        $html .= '<p class="no-events">Tidak ada kegiatan pada bulan ini.</p>';
    }
    $html .= '</div>';

} elseif ($type == 'year') {
    // ===== TAHUN INI (12 BULAN) =====
    $html .= '<h1>Kalender Kegiatan Tahun ' . $year . '</h1>';
    $totalEvents = 0;
    
    for ($m = 1; $m <= 12; $m++) {
        $start = "$year-" . str_pad($m, 2, '0', STR_PAD_LEFT) . "-01";
        $end = date('Y-m-t', strtotime($start));
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $m, $year);
        $firstDay = date('w', strtotime($start));

        $sql = "SELECT k.*, o.nama_organisasi FROM konten_kegiatan k 
                JOIN organisasi o ON k.id_organisasi = o.id_organisasi 
                WHERE k.status_publikasi = 'publik' AND k.tanggal_kegiatan BETWEEN :start AND :end 
                ORDER BY k.tanggal_kegiatan ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':start' => $start, ':end' => $end]);
        $events = $stmt->fetchAll();
        $totalEvents += count($events);

        $eventsByDate = [];
        foreach ($events as $e) {
            $dateKey = date('Y-m-d', strtotime($e['tanggal_kegiatan']));
            if (!isset($eventsByDate[$dateKey])) $eventsByDate[$dateKey] = [];
            $eventsByDate[$dateKey][] = $e;
        }

        $html .= '<div class="print-page">';
        $html .= '<div class="month-title">' . getIndonesianMonth($m) . ' <span>' . $year . '</span></div>';
        $html .= '<table>';
        $html .= '<tr><th>Minggu</th><th>Senin</th><th>Selasa</th><th>Rabu</th><th>Kamis</th><th>Jumat</th><th>Sabtu</th></tr>';
        $html .= '<tr>';
        for ($i = 0; $i < $firstDay; $i++) {
            $html .= '<td></td>';
        }
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $dateKey = "$year-" . str_pad($m, 2, '0', STR_PAD_LEFT) . "-" . str_pad($d, 2, '0', STR_PAD_LEFT);
            $isToday = ($dateKey == date('Y-m-d'));
            $html .= '<td>';
            $html .= '<div class="day-num' . ($isToday ? ' today' : '') . '">' . $d . '</div>';
            if (isset($eventsByDate[$dateKey])) {
                foreach (array_slice($eventsByDate[$dateKey], 0, 2) as $ev) {
                    $html .= '<span class="event">' . htmlspecialchars(substr($ev['judul'], 0, 20)) . '</span>';
                }
                if (count($eventsByDate[$dateKey]) > 2) {
                    $html .= '<span class="event" style="background:#e2e8f0;color:#475569;">+' . (count($eventsByDate[$dateKey]) - 2) . '</span>';
                }
            }
            $html .= '</td>';
            if (($i + $d) % 7 == 0 && $d < $daysInMonth) {
                $html .= '</tr><tr>';
            }
        }
        $remaining = (7 - (($firstDay + $daysInMonth) % 7)) % 7;
        for ($i = 0; $i < $remaining; $i++) {
            $html .= '<td></td>';
        }
        $html .= '</tr></table>';

        // Daftar kegiatan (ringkas)
        $html .= '<div class="event-list"><strong>Kegiatan:</strong> ';
        if (count($events) > 0) {
            $eventList = [];
            foreach ($events as $e) {
                $eventList[] = date('d M', strtotime($e['tanggal_kegiatan'])) . ' - ' . htmlspecialchars($e['judul']);
            }
            $html .= implode('; ', array_slice($eventList, 0, 5));
            if (count($events) > 5) $html .= ' (+' . (count($events) - 5) . ' lagi)';
        } else {
            $html .= 'Tidak ada kegiatan.';
        }
        $html .= '</div>';
        $html .= '</div>'; // end print-page
    }

    if ($totalEvents == 0) {
        $html .= '<p class="no-events" style="text-align:center;font-size:1.2rem;margin-top:2rem;">Tidak ada kegiatan sepanjang tahun ' . $year . '.</p>';
    }
}

$html .= '<div class="footer">Dicetak dari MASAGENA-ITH | ' . date('d M Y H:i') . '</div>';
$html .= '</body></html>';

echo $html;
?>
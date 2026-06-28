<?php
<<<<<<< HEAD
// dashboard/mahasiswa/agenda.php
session_start();
require_once '../../config/session_check.php';
require_once '../../config/database.php';
require_once '../../include/pendaftaran-helper.php';

// ===== AMBIL PARAMETER =====
$filter_organisasi = isset($_GET['filter_organisasi']) ? (int)$_GET['filter_organisasi'] : 0;
$filter_jenis = isset($_GET['filter_jenis']) ? trim($_GET['filter_jenis']) : '';
$filter_kategori = isset($_GET['filter_kategori']) ? trim($_GET['filter_kategori']) : '';
$filter_status = isset($_GET['filter_status']) ? $_GET['filter_status'] : 'semua';
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'terdekat';

// ===== NAVIGASI BULAN =====
$month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('n');
$year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');
if ($month < 1) { $month = 12; $year--; }
if ($month > 12) { $month = 1; $year++; }

$today = date('Y-m-d');
$currentMonthStart = "$year-$month-01";
$currentMonthEnd = date('Y-m-t', strtotime($currentMonthStart));
$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
$firstDayOfMonth = date('w', strtotime($currentMonthStart));

// ===== AMBIL DATA UNTUK DROPDOWN =====
$allOrganisasi = $pdo->query("SELECT id_organisasi, nama_organisasi FROM organisasi ORDER BY nama_organisasi")->fetchAll();
$allKategori = $pdo->query("SELECT DISTINCT kategori FROM konten_kegiatan WHERE kategori IS NOT NULL AND kategori != '' ORDER BY kategori")->fetchAll(PDO::FETCH_COLUMN);
$allJenis = $pdo->query("SELECT DISTINCT jenis FROM organisasi WHERE jenis IS NOT NULL AND jenis != '' ORDER BY jenis")->fetchAll(PDO::FETCH_COLUMN);

// ============================================================
// QUERY KEGIATAN BULAN INI
// ============================================================
$where = "k.status_publikasi = 'publik' AND k.tanggal_kegiatan BETWEEN :start AND :end";
$params = [':start' => $currentMonthStart, ':end' => $currentMonthEnd];

if ($filter_organisasi > 0) {
    $where .= " AND k.id_organisasi = :org";
    $params[':org'] = $filter_organisasi;
}
if (!empty($filter_jenis)) {
    $where .= " AND o.jenis = :jenis";
    $params[':jenis'] = $filter_jenis;
}
if (!empty($filter_kategori)) {
    $where .= " AND k.kategori = :kat";
    $params[':kat'] = $filter_kategori;
}
if ($filter_status == 'akan_datang') {
    $where .= " AND k.tanggal_kegiatan >= CURDATE()";
} elseif ($filter_status == 'lewat') {
    $where .= " AND k.tanggal_kegiatan < CURDATE()";
}

$sql = "SELECT k.*, o.nama_organisasi, o.jenis 
        FROM konten_kegiatan k
        JOIN organisasi o ON k.id_organisasi = o.id_organisasi
        WHERE $where
        ORDER BY k.tanggal_kegiatan ASC";
$stmt = $pdo->prepare($sql);
foreach ($params as $key => $val) {
    $stmt->bindValue($key, $val);
}
$stmt->execute();
$kegiatanBulan = $stmt->fetchAll();

// Kelompokkan berdasarkan tanggal
$eventsByDate = [];
foreach ($kegiatanBulan as $k) {
    $dateKey = date('Y-m-d', strtotime($k['tanggal_kegiatan']));
    if (!isset($eventsByDate[$dateKey])) {
        $eventsByDate[$dateKey] = [];
    }
    $eventsByDate[$dateKey][] = $k;
}

// ============================================================
// KEGIATAN TERDEKAT (5)
// ============================================================
$whereNext = "k.status_publikasi = 'publik' AND k.tanggal_kegiatan >= CURDATE()";
$paramsNext = [];
if ($filter_organisasi > 0) {
    $whereNext .= " AND k.id_organisasi = :org";
    $paramsNext[':org'] = $filter_organisasi;
}
if (!empty($filter_jenis)) {
    $whereNext .= " AND o.jenis = :jenis";
    $paramsNext[':jenis'] = $filter_jenis;
}
if (!empty($filter_kategori)) {
    $whereNext .= " AND k.kategori = :kat";
    $paramsNext[':kat'] = $filter_kategori;
}
$sqlNext = "SELECT k.*, o.nama_organisasi, o.jenis 
            FROM konten_kegiatan k
            JOIN organisasi o ON k.id_organisasi = o.id_organisasi
            WHERE $whereNext
            ORDER BY k.tanggal_kegiatan ASC
            LIMIT 5";
$stmtNext = $pdo->prepare($sqlNext);
foreach ($paramsNext as $key => $val) {
    $stmtNext->bindValue($key, $val);
}
$stmtNext->execute();
$kegiatanTerdekat = $stmtNext->fetchAll();

// ============================================================
// SEMUA KEGIATAN BULAN INI
// ============================================================
$sqlAllBulan = "SELECT k.*, o.nama_organisasi, o.jenis 
                FROM konten_kegiatan k
                JOIN organisasi o ON k.id_organisasi = o.id_organisasi
                WHERE $where
                ORDER BY k.tanggal_kegiatan ASC";
$stmtAllBulan = $pdo->prepare($sqlAllBulan);
foreach ($params as $key => $val) {
    $stmtAllBulan->bindValue($key, $val);
}
$stmtAllBulan->execute();
$kegiatanAllBulan = $stmtAllBulan->fetchAll();

// ============================================================
// FUNGSI BANTU
// ============================================================
function getIndonesianMonth($month) {
    $months = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',
               7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];
    return $months[$month];
}

function getIndonesianDay($day) {
    $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    return $days[$day];
}

// ============================================================
// GENERATE HTML UNTUK PRINT (BULAN / TAHUN)
// ============================================================
function generatePrintHTML($type, $year, $month = null) {
    global $pdo;
    $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Kalender Agenda</title>';
    $html .= '<style>
        body { font-family: Arial, sans-serif; padding: 20px; color: #333; }
        h1 { text-align: center; color: #071C34; border-bottom: 3px solid #FFA007; padding-bottom: 10px; }
        .month-title { font-size: 1.5rem; font-weight: bold; color: #071C34; margin: 20px 0 10px 0; }
        .month-title span { color: #FFA007; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { background: #071C34; color: #fff; padding: 8px; text-align: center; }
        td { border: 1px solid #ddd; padding: 8px; vertical-align: top; height: 60px; width: 14.28%; }
        td .day-num { font-weight: bold; font-size: 0.9rem; }
        td .event { background: #FFA007; color: #071C34; padding: 2px 4px; border-radius: 4px; font-size: 0.65rem; margin-top: 2px; display: block; }
        td .today { background: #FFA007; border-radius: 50%; display: inline-block; padding: 0 5px; }
        .event-list { margin-top: 20px; }
        .event-item { padding: 5px 10px; border-left: 4px solid #FFA007; margin-bottom: 5px; background: #f8fafc; }
        .event-item .date { font-weight: bold; color: #071C34; }
        .event-item .org { color: #64748b; font-size: 0.8rem; }
        .footer { text-align: center; margin-top: 30px; font-size: 0.8rem; color: #94a3b8; border-top: 1px solid #e9ecef; padding-top: 15px; }
        @page { margin: 1.5cm; }
        .print-page { page-break-after: always; }
    </style></head><body>';

    if ($type == 'month' && $month) {
        // ===== CETAK BULAN INI =====
        $start = "$year-$month-01";
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
            if (($i + $d) % 7 == 0) {
                $html .= '</tr><tr>';
            }
        }
        $remaining = (7 - (($firstDay + $daysInMonth) % 7)) % 7;
        for ($i = 0; $i < $remaining; $i++) {
            $html .= '<td></td>';
        }
        $html .= '</tr></table>';

        // Daftar kegiatan bulan ini
        $html .= '<div class="event-list"><h3>Daftar Kegiatan Bulan ' . getIndonesianMonth($month) . '</h3>';
        if (count($events) > 0) {
            foreach ($events as $e) {
                $html .= '<div class="event-item"><span class="date">' . date('d M Y', strtotime($e['tanggal_kegiatan'])) . '</span> - ' . htmlspecialchars($e['judul']) . ' <span class="org">(' . htmlspecialchars($e['nama_organisasi']) . ')</span></div>';
            }
        } else {
            $html .= '<p>Tidak ada kegiatan pada bulan ini.</p>';
        }
        $html .= '</div>';

    } elseif ($type == 'year') {
        // ===== CETAK TAHUN INI (12 BULAN) =====
        $html .= '<h1>Kalender Kegiatan Tahun ' . $year . '</h1>';
        
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
                if (($i + $d) % 7 == 0) {
                    $html .= '</tr><tr>';
                }
            }
            $remaining = (7 - (($firstDay + $daysInMonth) % 7)) % 7;
            for ($i = 0; $i < $remaining; $i++) {
                $html .= '<td></td>';
            }
            $html .= '</tr></table>';

            // Daftar kegiatan bulan ini
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
    }

    $html .= '<div class="footer">Dicetak dari MASAGENA-ITH | ' . date('d M Y H:i') . '</div>';
    $html .= '</body></html>';
    return $html;
}

include '../../include/header.php';
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

<style>
/* ============================================================
   AGENDA STYLES (sama seperti sebelumnya)
   ============================================================ */
.agenda-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 1rem;
    /* tambahkan */
    margin-top: 0; /* atau 0.5rem jika perlu */
    padding-top: 0.5rem;
}

/* Filter bar */
.agenda-filter-bar {
    background: #f8fafc;
    border-radius: 16px;
    padding: 1rem 1.5rem;
    margin-bottom: 2rem;
    border: 1px solid #e9ecef;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.8rem 1.2rem;
}
.agenda-filter-bar .filter-group {
    display: flex;
    flex-direction: column;
    flex: 0 1 130px;
    min-width: 100px;
}
.agenda-filter-bar .filter-group label {
    font-size: 0.6rem;
    font-weight: 700;
    color: #071C34;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.1rem;
}
.agenda-filter-bar .filter-group select {
    border-radius: 50px;
    border: 1.5px solid #e2e8f0;
    padding: 0.25rem 0.7rem;
    font-size: 0.8rem;
    background: #ffffff;
    height: 34px;
}
.agenda-filter-bar .filter-group select:focus {
    border-color: #FFA007;
    outline: none;
}
.agenda-filter-bar .btn-reset {
    background: #FFA007;
    color: #071C34;
    border: none;
    border-radius: 50px;
    padding: 0.25rem 1.8rem;
    font-weight: 700;
    font-size: 0.8rem;
    cursor: pointer;
    height: 34px;
    display: flex;
    align-items: center;
    gap: 0.4rem;
    margin-left: auto;
    transition: 0.3s;
}
.agenda-filter-bar .btn-reset:hover {
    background: #0a2a4a;
    color: #ffffff;
}

/* Calendar header */
.calendar-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
    gap: 0.5rem;
}
.calendar-header .month-title {
    font-size: 1.8rem;
    font-weight: 700;
    color: #071C34;
}
.calendar-header .month-title span { color: #FFA007; }
.calendar-header .nav-buttons {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
    align-items: center;
}
.calendar-header .nav-buttons a {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.4rem 1.2rem;
    border-radius: 50px;
    background: #f1f5f9;
    color: #071C34;
    text-decoration: none;
    font-weight: 600;
    transition: 0.2s;
    border: 1px solid #e2e8f0;
}
.calendar-header .nav-buttons a:hover {
    background: #FFA007;
    color: #071C34;
    border-color: #FFA007;
}
.calendar-header .nav-buttons .btn-today {
    background: #071C34;
    color: #fff;
    border-color: #071C34;
}
.calendar-header .nav-buttons .btn-today:hover {
    background: #FFA007;
    color: #071C34;
    border-color: #FFA007;
}

/* Calendar grid */
.calendar-grid {
    background: #ffffff;
    border-radius: 20px;
    overflow: hidden;
    border: 1px solid #e9ecef;
    box-shadow: 0 4px 20px rgba(0,0,0,0.04);
}
.calendar-grid .days-header {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    background: #071C34;
    color: #fff;
    font-weight: 600;
    font-size: 0.85rem;
}
.calendar-grid .days-header div {
    padding: 0.8rem 0.5rem;
    text-align: center;
}
.calendar-grid .days-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
}
.calendar-grid .day-cell {
    min-height: 100px;
    padding: 0.5rem 0.5rem 0.5rem 0.8rem;
    border-right: 1px solid #f1f5f9;
    border-bottom: 1px solid #f1f5f9;
    background: #ffffff;
    transition: background 0.2s;
    position: relative;
    text-align: left;
}
.calendar-grid .day-cell:nth-child(7n) { border-right: none; }
.calendar-grid .day-cell:hover { background: #fafbfc; }
.calendar-grid .day-cell .day-number {
    font-weight: 600;
    font-size: 0.9rem;
    color: #071C34;
    margin-bottom: 0.3rem;
    display: inline-block;
    width: 28px;
    height: 28px;
    line-height: 28px;
    text-align: center;
}
.calendar-grid .day-cell .day-number.today {
    background: #FFA007;
    color: #071C34;
    border-radius: 50%;
    font-weight: 700;
}
.calendar-grid .day-cell .day-number.other-month { color: #cbd5e0; }
.calendar-grid .day-cell .event-badge {
    display: block;
    font-size: 0.6rem;
    background: #FFA007;
    color: #071C34;
    padding: 0.15rem 0.4rem;
    border-radius: 4px;
    margin-bottom: 0.2rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    cursor: pointer;
    transition: 0.2s;
    text-decoration: none;
    text-align: left;
}
.calendar-grid .day-cell .event-badge:hover {
    background: #071C34;
    color: #fff;
}
.calendar-grid .day-cell .event-badge.more {
    background: #e2e8f0;
    color: #475569;
    font-weight: 600;
}
.calendar-grid .day-cell.empty { background: #fafbfc; }

/* Tab */
.upcoming-tabs {
    margin-top: 2rem;
}
.upcoming-tabs .tab-nav {
    display: flex;
    gap: 0.5rem;
    border-bottom: 2px solid #e9ecef;
    margin-bottom: 1.5rem;
}
.upcoming-tabs .tab-nav .tab-btn {
    padding: 0.5rem 1.5rem;
    border: none;
    background: none;
    font-weight: 600;
    color: #94a3b8;
    cursor: pointer;
    border-bottom: 3px solid transparent;
    transition: 0.2s;
    font-size: 0.95rem;
}
.upcoming-tabs .tab-nav .tab-btn.active {
    color: #071C34;
    border-bottom-color: #FFA007;
}
.upcoming-tabs .tab-nav .tab-btn:hover {
    color: #071C34;
}
.upcoming-tabs .tab-content {
    display: none;
}
.upcoming-tabs .tab-content.active {
    display: block;
}

.upcoming-tabs .event-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 0.8rem 1.2rem;
    background: #f8fafc;
    border-radius: 12px;
    margin-bottom: 0.6rem;
    transition: 0.2s;
    border-left: 4px solid #FFA007;
}
.upcoming-tabs .event-item:hover {
    background: #f1f5f9;
}
.upcoming-tabs .event-item .date-badge {
    background: #071C34;
    color: #fff;
    padding: 0.2rem 0.8rem;
    border-radius: 50px;
    font-size: 0.7rem;
    font-weight: 600;
    white-space: nowrap;
}
.upcoming-tabs .event-item .event-title {
    font-weight: 600;
    color: #071C34;
    flex: 1;
}
.upcoming-tabs .event-item .event-title a {
    color: #071C34;
    text-decoration: none;
}
.upcoming-tabs .event-item .event-title a:hover {
    color: #FFA007;
}
.upcoming-tabs .event-item .event-org {
    font-size: 0.8rem;
    color: #64748b;
}
.upcoming-tabs .empty-state {
    text-align: center;
    padding: 2rem;
    color: #94a3b8;
}

/* ===== TOMBOL UNDUH PDF DI BAWAH ===== */
.download-section {
    margin-top: 2.5rem;
    padding: 1.5rem;
    background: #f8fafc;
    border-radius: 16px;
    border: 1px solid #e9ecef;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: center;
    gap: 1rem;
}
.download-section .btn-print {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1.5rem;
    border-radius: 50px;
    background: #FFA007;
    color: #071C34;
    text-decoration: none;
    font-weight: 600;
    font-size: 0.9rem;
    border: 1px solid #FFA007;
    transition: 0.2s;
    cursor: pointer;
}
.download-section .btn-print:hover {
    background: #071C34;
    color: #fff;
    border-color: #071C34;
}
.download-section .btn-print i {
    font-size: 1rem;
}

/* Responsive */
@media (max-width: 768px) {
    .agenda-filter-bar { flex-direction: column; align-items: stretch; }
    .agenda-filter-bar .filter-group { flex: 1; }
    .agenda-filter-bar .btn-reset { width: 100%; justify-content: center; margin-left: 0; }
    .calendar-header { flex-direction: column; gap: 0.8rem; text-align: center; }
    .calendar-grid .day-cell { min-height: 60px; padding: 0.3rem; }
    .calendar-grid .day-cell .day-number { font-size: 0.75rem; }
    .calendar-grid .day-cell .event-badge { font-size: 0.5rem; padding: 0.1rem 0.3rem; }
    .upcoming-tabs .event-item { flex-wrap: wrap; gap: 0.3rem; }
    .calendar-header .nav-buttons { justify-content: center; }
    .download-section { flex-direction: column; }
    .download-section .btn-print { width: 100%; justify-content: center; }
}
</style>

<div class="agenda-container">

    <!-- FILTER -->
    <div class="agenda-filter-bar">
        <form id="filterForm" method="get" action="<?= $_SERVER['REQUEST_URI'] ?>" style="display:contents;">
            <input type="hidden" name="month" value="<?= $month ?>">
            <input type="hidden" name="year" value="<?= $year ?>">
            <input type="hidden" name="tab" value="<?= $tab ?>">

            <div class="filter-group">
                <label>Status</label>
                <select name="filter_status" onchange="this.form.submit()">
                    <option value="semua" <?= $filter_status=='semua'?'selected':'' ?>>Semua</option>
                    <option value="akan_datang" <?= $filter_status=='akan_datang'?'selected':'' ?>>Akan Datang</option>
                    <option value="lewat" <?= $filter_status=='lewat'?'selected':'' ?>>Sudah Lewat</option>
                </select>
            </div>

            <div class="filter-group">
                <label>Jenis Org</label>
                <select name="filter_jenis" onchange="this.form.submit()">
                    <option value="">Semua</option>
                    <?php foreach ($allJenis as $jenis): ?>
                        <option value="<?= htmlspecialchars($jenis) ?>" <?= ($filter_jenis == $jenis) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($jenis) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-group">
                <label>Kategori</label>
                <select name="filter_kategori" onchange="this.form.submit()">
                    <option value="">Semua</option>
                    <?php foreach($allKategori as $kat): ?>
                        <option value="<?= htmlspecialchars($kat) ?>" <?= $filter_kategori==$kat?'selected':'' ?>><?= htmlspecialchars($kat) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-group">
                <label>Organisasi</label>
                <select name="filter_organisasi" onchange="this.form.submit()">
                    <option value="0">Semua</option>
                    <?php foreach($allOrganisasi as $org): ?>
                        <option value="<?= $org['id_organisasi'] ?>" <?= $filter_organisasi==$org['id_organisasi']?'selected':'' ?>><?= htmlspecialchars($org['nama_organisasi']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="button" class="btn-reset" onclick="resetFilter()">
                <i class="fas fa-undo"></i> Reset
            </button>
        </form>
    </div>

    <!-- KALENDER HEADER -->
    <div class="calendar-header">
        <div class="month-title"><?= getIndonesianMonth($month) ?> <span><?= $year ?></span></div>
        <div class="nav-buttons">
            <a href="?month=<?= $month-1 ?>&year=<?= $year ?>&<?= http_build_query(array_filter(['filter_organisasi' => $filter_organisasi, 'filter_jenis' => $filter_jenis, 'filter_kategori' => $filter_kategori, 'filter_status' => $filter_status, 'tab' => $tab])) ?>">
                <i class="fas fa-chevron-left"></i> Prev
            </a>
            <a href="?month=<?= date('n') ?>&year=<?= date('Y') ?>&<?= http_build_query(array_filter(['filter_organisasi' => $filter_organisasi, 'filter_jenis' => $filter_jenis, 'filter_kategori' => $filter_kategori, 'filter_status' => $filter_status, 'tab' => $tab])) ?>" class="btn-today">Hari Ini</a>
            <a href="?month=<?= $month+1 ?>&year=<?= $year ?>&<?= http_build_query(array_filter(['filter_organisasi' => $filter_organisasi, 'filter_jenis' => $filter_jenis, 'filter_kategori' => $filter_kategori, 'filter_status' => $filter_status, 'tab' => $tab])) ?>">
                Next <i class="fas fa-chevron-right"></i>
            </a>
        </div>
    </div>

    <!-- KALENDER GRID -->
    <div class="calendar-grid">
        <div class="days-header">
            <div>Minggu</div><div>Senin</div><div>Selasa</div><div>Rabu</div>
            <div>Kamis</div><div>Jumat</div><div>Sabtu</div>
        </div>
        <div class="days-grid">
            <?php
            $emptyDays = $firstDayOfMonth;
            $totalCells = ceil(($emptyDays + $daysInMonth) / 7) * 7;
            $dayCount = 1;
            for ($i = 0; $i < $totalCells; $i++):
                $isToday = ($dayCount == date('j') && $month == date('n') && $year == date('Y'));
                $isOtherMonth = ($i < $emptyDays || $dayCount > $daysInMonth);
                $currentDate = null;
                if (!$isOtherMonth) {
                    $currentDate = "$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-" . str_pad($dayCount, 2, '0', STR_PAD_LEFT);
                }
                $dayNumber = ($isOtherMonth || $dayCount > $daysInMonth) ? null : $dayCount;
                $eventsToday = isset($currentDate) && isset($eventsByDate[$currentDate]) ? $eventsByDate[$currentDate] : [];
            ?>
                <div class="day-cell <?= ($i < $emptyDays || $dayCount > $daysInMonth) ? 'empty' : '' ?>">
                    <?php if ($dayNumber !== null): ?>
                        <div class="day-number <?= $isToday ? 'today' : '' ?>">
                            <?= $dayNumber ?>
                        </div>
                        <?php if (count($eventsToday) > 0): ?>
                            <?php $display = array_slice($eventsToday, 0, 3); foreach ($display as $ev): ?>
                                <a href="<?= BASE_URL ?>/dashboard/mahasiswa/detail_kegiatan.php?id=<?= $ev['id_konten'] ?>&back=<?= urlencode(BASE_URL . '/dashboard/mahasiswa/agenda.php') ?>" class="event-badge" title="<?= htmlspecialchars($ev['judul']) ?>">
                                    <?= htmlspecialchars(substr($ev['judul'], 0, 20)) ?>
                                </a>
                            <?php endforeach; ?>
                            <?php if (count($eventsToday) > 3): ?>
                                <span class="event-badge more">+<?= count($eventsToday)-3 ?> lagi</span>
                            <?php endif; ?>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            <?php if (!$isOtherMonth) $dayCount++; endfor; ?>
        </div>
    </div>

    <!-- TAB KEGIATAN -->
    <div class="upcoming-tabs" id="tabContainer">
        <div class="tab-nav">
            <button class="tab-btn <?= $tab=='terdekat'?'active':'' ?>" data-tab="terdekat" onclick="switchTab('terdekat')">
                <i class="fa-regular fa-clock"></i> Kegiatan Mendatang
            </button>
            <button class="tab-btn <?= $tab=='bulan_ini'?'active':'' ?>" data-tab="bulan_ini" onclick="switchTab('bulan_ini')">
                <i class="fa-regular fa-calendar"></i> Semua Kegiatan Bulan Ini
            </button>
        </div>

        <div class="tab-content <?= $tab=='terdekat'?'active':'' ?>" id="tab-terdekat">
            <?php if (count($kegiatanTerdekat) > 0): ?>
                <?php foreach ($kegiatanTerdekat as $k): ?>
                    <div class="event-item">
                        <span class="date-badge"><?= date('d M', strtotime($k['tanggal_kegiatan'])) ?></span>
                        <span class="event-title">
                            <a href="<?= BASE_URL ?>/dashboard/mahasiswa/detail_kegiatan.php?id=<?= $k['id_konten'] ?>&back=<?= urlencode(BASE_URL . '/dashboard/mahasiswa/agenda.php') ?>">
                                <?= htmlspecialchars($k['judul']) ?>
                            </a>
                        </span>
                        <span class="event-org"><i class="fa-regular fa-building"></i> <?= htmlspecialchars($k['nama_organisasi']) ?></span>
                        <?php if (!empty($k['kategori'])): ?>
                            <span class="event-org" style="color:#FFA007; font-weight:600;"><?= htmlspecialchars($k['kategori']) ?></span>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state"><i class="fa-regular fa-calendar-xmark fa-2x"></i><p>Tidak ada kegiatan mendatang.</p></div>
            <?php endif; ?>
        </div>

        <div class="tab-content <?= $tab=='bulan_ini'?'active':'' ?>" id="tab-bulan_ini">
            <?php if (count($kegiatanAllBulan) > 0): ?>
                <?php foreach ($kegiatanAllBulan as $k): ?>
                    <div class="event-item">
                        <span class="date-badge"><?= date('d M', strtotime($k['tanggal_kegiatan'])) ?></span>
                        <span class="event-title">
                            <a href="<?= BASE_URL ?>/dashboard/mahasiswa/detail_kegiatan.php?id=<?= $k['id_konten'] ?>&back=<?= urlencode(BASE_URL . '/dashboard/mahasiswa/agenda.php') ?>">
                                <?= htmlspecialchars($k['judul']) ?>
                            </a>
                        </span>
                        <span class="event-org"><i class="fa-regular fa-building"></i> <?= htmlspecialchars($k['nama_organisasi']) ?></span>
                        <?php if (!empty($k['kategori'])): ?>
                            <span class="event-org" style="color:#FFA007; font-weight:600;"><?= htmlspecialchars($k['kategori']) ?></span>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state"><i class="fa-regular fa-calendar-xmark fa-2x"></i><p>Tidak ada kegiatan pada bulan ini.</p></div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ========================================================== -->
    <!-- TOMBOL UNDUH PDF (DI BAWAH SEMUA KONTEN)                   -->
    <!-- ========================================================== -->
    <div class="download-section">
        <button class="btn-print" onclick="printPDF('month', <?= $month ?>, <?= $year ?>)">
            <i class="fas fa-file-pdf"></i> Unduh Kalender Bulan Ini (PDF)
        </button>
        <button class="btn-print" onclick="printPDF('year', null, <?= $year ?>)">
            <i class="fas fa-file-pdf"></i> Unduh Kalender Tahun Ini (PDF)
        </button>
    </div>

</div>

<script>
// ============================================================
// FUNGSI UNDUH PDF (tanpa reload & tanpa buka tab baru)
// ============================================================
function printPDF(type, month, year) {
    // Buat iframe tersembunyi
    var iframe = document.createElement('iframe');
    iframe.style.position = 'fixed';
    iframe.style.top = '-9999px';
    iframe.style.left = '-9999px';
    iframe.style.width = '1px';
    iframe.style.height = '1px';
    iframe.style.border = 'none';
    document.body.appendChild(iframe);

    var url = '<?= BASE_URL ?>/dashboard/mahasiswa/print_agenda.php?type=' + type + '&year=' + year;
    if (type == 'month') {
        url += '&month=' + month;
    }

    iframe.src = url;

    iframe.onload = function() {
        try {
            iframe.contentWindow.focus();
            iframe.contentWindow.print();
        } catch (e) {
            alert('Gagal membuka print. Silakan coba lagi.');
        }
        // Hapus iframe setelah selesai print (tapi kita hapus setelah beberapa detik)
        setTimeout(function() {
            if (document.body.contains(iframe)) {
                document.body.removeChild(iframe);
            }
        }, 5000);
    };
}

// ============================================================
// RESET FILTER & SWITCH TAB (sama seperti sebelumnya)
// ============================================================
function resetFilter() {
    var form = document.getElementById('filterForm');
    form.querySelectorAll('select').forEach(function(sel) {
        sel.selectedIndex = 0;
    });
    form.submit();
}

function switchTab(tab) {
    document.querySelectorAll('.tab-btn').forEach(function(btn) {
        btn.classList.remove('active');
    });
    document.querySelector('.tab-btn[data-tab="' + tab + '"]').classList.add('active');

    document.querySelectorAll('.tab-content').forEach(function(content) {
        content.classList.remove('active');
    });
    document.getElementById('tab-' + tab).classList.add('active');

    var url = new URL(window.location.href);
    url.searchParams.set('tab', tab);
    window.history.pushState({}, '', url);
}
</script>

<?php include '../../include/footer.php'; ?>
=======
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
>>>>>>> 9e4b9b789696603edaa30fd5aeb277ddc8239c7c

<?php
// Helper khusus modul pendaftaran kegiatan MASAGENA-ITH

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Karena file helper ini diasumsikan berada di dashboard/mahasiswa atau dashboard/pengurus
$rootPath = dirname(__DIR__, 2);

// Sesuaikan dengan nama folder project di htdocs
$baseUrl = '/MASAGENA-ITH';


if (!function_exists('short_text')) {
    function short_text($text, $limit = 120) {
        $text = trim(strip_tags((string) $text));

        if (function_exists('mb_strlen') && mb_strlen($text) <= $limit) {
            return $text;
        }

        if (!function_exists('mb_strlen') && strlen($text) <= $limit) {
            return $text;
        }

        return function_exists('mb_substr')
            ? mb_substr($text, 0, $limit) . '...'
            : substr($text, 0, $limit) . '...';
    }
}

if (!function_exists('pendaftaran_default_kuota')) {
    function pendaftaran_default_kuota() {
        return 50;
    }
}

if (!function_exists('pendaftaran_status_badge')) {
    function pendaftaran_status_badge($status) {
        $status = strtolower(trim((string) $status));

        $labels = [
            'menunggu' => 'Menunggu',
            'diterima' => 'Diterima',
            'ditolak' => 'Ditolak',
        ];

        $label = $labels[$status] ?? ucfirst($status ?: 'Menunggu');
        $class = preg_replace('/[^a-z0-9_-]/', '', $status ?: 'menunggu');

        return '<span class="status-badge status-' . h($class) . '">' . h($label) . '</span>';
    }
}

if (!function_exists('pendaftaran_current_mahasiswa')) {
    function pendaftaran_current_mahasiswa($conn) {

        if (empty($_SESSION['id_mahasiswa'])) {
            return null;
        }

        $id = (int) $_SESSION['id_mahasiswa'];

        $stmt = mysqli_prepare($conn, "
            SELECT 
                id_mahasiswa,
                nim,
                nama,
                prodi,
                kontak,
                email
            FROM tbmahasiswa
            WHERE id_mahasiswa = ?
            LIMIT 1
        ");

        if (!$stmt) {
            return null;
        }

        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);
        $data = mysqli_fetch_assoc($result);

        mysqli_stmt_close($stmt);

        return $data ?: null;
    }
}

if (!function_exists('pendaftaran_lampiran_src')) {
    function pendaftaran_lampiran_src($lampiran) {
        global $baseUrl;

        $lampiran = trim((string) $lampiran);

        if ($lampiran === '') {
            return '';
        }

        $lampiran = str_replace('\\', '/', $lampiran);

        if (preg_match('/^https?:\/\//', $lampiran)) {
            return $lampiran;
        }

        if (substr($lampiran, 0, 1) === '/') {
            return $lampiran;
        }

        if (strpos($lampiran, 'uploads/') === 0 || strpos($lampiran, 'assets/') === 0) {
            return $baseUrl . '/' . $lampiran;
        }

        return $baseUrl . '/uploads/kegiatan/' . $lampiran;
    }
}
?>
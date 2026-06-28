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
    /**
     * Mengambil data mahasiswa yang sedang login menggunakan PDO
     * Parameter diganti menjadi objek PDO (misal: $pdo)
     */
    function pendaftaran_current_mahasiswa($pdo) {
        // Cek $_SESSION['user_id'] sesuai dengan data session login Anda
        if (empty($_SESSION['user_id'])) {
            return null;
        }

        $id = (int) $_SESSION['user_id'];

        try {
            // Konversi query dari mysqli ke PDO Prepared Statement
            $stmt = $pdo->prepare("
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

            $stmt->execute([$id]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            return $data ?: null;
        } catch (PDOException $e) {
            // Jika terjadi kegagalan query database
            return null;
        }
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

// Tambahkan fungsi highlightText
if (!function_exists('highlightText')) {
    function highlightText($text, $keyword) {
        if (empty($keyword)) return $text;
        return preg_replace('/(' . preg_quote($keyword, '/') . ')/i', '<span class="highlight">$1</span>', $text);
    }
}
?>
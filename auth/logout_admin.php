<?php
session_start();
// Hapus semua data session
$_SESSION = array();
session_destroy();

// Alihkan ke halaman login utama
header("Location: login_admin.php");
exit;
?>
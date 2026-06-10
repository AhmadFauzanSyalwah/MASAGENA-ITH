<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if (
    !isset($_SESSION['id_mahasiswa']) &&
    !isset($_SESSION['id_pengurus']) &&
    !isset($_SESSION['id_admin'])
) {
    header('Location: /auth/login.php');
    exit;
}
<?php
// ajax/like.php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit;
}

$id_konten = isset($_POST['id']) ? (int)$_POST['id'] : 0;
if ($id_konten <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid ID']);
    exit;
}

$id_mahasiswa = $_SESSION['user_id'];

// Cek apakah sudah like
$check = $pdo->prepare("SELECT id_like FROM likes WHERE id_mahasiswa = ? AND id_konten = ?");
$check->execute([$id_mahasiswa, $id_konten]);
$existing = $check->fetch();

if ($existing) {
    // Unlike
    $delete = $pdo->prepare("DELETE FROM likes WHERE id_like = ?");
    $delete->execute([$existing['id_like']]);
    $status = 'unliked';
} else {
    // Like
    $insert = $pdo->prepare("INSERT INTO likes (id_mahasiswa, id_konten, created_at) VALUES (?, ?, NOW())");
    $insert->execute([$id_mahasiswa, $id_konten]);
    $status = 'liked';
}

// Hitung total like
$count = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE id_konten = ?");
$count->execute([$id_konten]);
$total = $count->fetchColumn();

echo json_encode(['status' => $status, 'likes' => $total]);
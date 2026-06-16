<?php
// ajax/like.php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

require_once '../config/database.php';

$id_konten = isset($_POST['id']) ? (int)$_POST['id'] : 0;
if (!$id_konten) {
    echo json_encode(['error' => 'Invalid ID']);
    exit();
}

$user_id = $_SESSION['user_id'];

// Cek apakah user sudah like
$stmt = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE id_user = ? AND id_konten = ?");
$stmt->execute([$user_id, $id_konten]);
$liked = $stmt->fetchColumn() > 0;

if ($liked) {
    // Unlike
    $stmt = $pdo->prepare("DELETE FROM likes WHERE id_user = ? AND id_konten = ?");
    $stmt->execute([$user_id, $id_konten]);
    $status = 'unliked';
} else {
    // Like
    $stmt = $pdo->prepare("INSERT INTO likes (id_user, id_konten) VALUES (?, ?)");
    $stmt->execute([$user_id, $id_konten]);
    $status = 'liked';
}

// Hitung total like terbaru
$stmt = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE id_konten = ?");
$stmt->execute([$id_konten]);
$likes = $stmt->fetchColumn();

echo json_encode(['status' => $status, 'likes' => $likes]);
?>
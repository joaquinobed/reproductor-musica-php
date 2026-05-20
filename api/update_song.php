<?php
header('Content-Type: application/json');
require_once '../config.php';

$id = $_POST['id'] ?? null;
$title = $_POST['title'] ?? null;
$artist = $_POST['artist'] ?? null;
$album_id = $_POST['album_id'] ?? null;

if (!$id || !$title) {
    echo json_encode(['error' => 'Datos incompletos']);
    exit;
}

$stmt = $db->prepare("UPDATE songs SET title = :title, artist = :artist, album_id = :album_id WHERE id = :id");
$stmt->bindValue(':title', $title);
$stmt->bindValue(':artist', $artist);
$stmt->bindValue(':album_id', $album_id);
$stmt->bindValue(':id', $id);
$stmt->execute();

echo json_encode(['success' => true]);
?>

<?php
header('Content-Type: application/json');
require_once '../config.php';

$album_id = $_GET['album_id'] ?? 'all';
$favs = $_GET['favs'] ?? null;

$query = "SELECT songs.*, albums.name as album_name, albums.cover_url 
          FROM songs 
          LEFT JOIN albums ON songs.album_id = albums.id";

if ($favs == 1) {
    $query .= " WHERE songs.is_favorite = 1";
} elseif ($album_id !== 'all') {
    $query .= " WHERE songs.album_id = " . intval($album_id);
}

$results = $db->query($query);
$songs = $results->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($songs);
?>

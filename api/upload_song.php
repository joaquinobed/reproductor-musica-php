<?php
header('Content-Type: application/json');
require_once '../config.php';

$title = $_POST['title'] ?? null;
$artist = $_POST['artist'] ?? 'Desconocido';
$album_id = $_POST['album_id'] ?? null;
$duration = $_POST['duration'] ?? '00:00';
$file = $_FILES['music_file'] ?? null;

if (!$file) {
    echo json_encode(['error' => 'No se ha subido ningún archivo']);
    exit;
}

require_once 'lib/id3_simple.php';

if ($file['error'] === UPLOAD_ERR_OK) {
    // Validación de tipo MIME real
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    $allowedMimes = ['audio/mpeg', 'audio/mp3', 'audio/wav', 'audio/x-wav', 'audio/ogg', 'application/ogg'];
    
    if (!in_array($mimeType, $allowedMimes)) {
        echo json_encode(['error' => 'Tipo de archivo no permitido: ' . $mimeType]);
        exit;
    }
    
    // Extraer metadatos ID3
    $id3 = SimpleID3::read($file['tmp_name']);
    
    if (!$title || $title === 'Desconocido') {
        $title = !empty($id3['title']) ? $id3['title'] : basename($file['name'], '.mp3');
    }
    
    if ($artist === 'Desconocido' && !empty($id3['artist'])) {
        $artist = $id3['artist'];
    }

    $upload_dir = '../audio/';
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
    
    $filename = time() . '-' . basename($file['name']);
    $target = $upload_dir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $target)) {
        $stmt = $db->prepare("INSERT INTO songs (album_id, title, artist, duration, filename) VALUES (:album, :title, :artist, :duration, :file)");
        $stmt->bindValue(':album', $album_id);
        $stmt->bindValue(':title', $title);
        $stmt->bindValue(':artist', $artist);
        $stmt->bindValue(':duration', $duration);
        $stmt->bindValue(':file', $filename);
        $stmt->execute();
        
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => 'No se pudo mover el archivo']);
    }
} else {
    echo json_encode(['error' => 'Error en la subida del archivo']);
}
?>

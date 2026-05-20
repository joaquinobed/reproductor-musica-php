<?php
header('Content-Type: application/json');
require_once '../config.php';

$name = $_POST['name'] ?? null;
$cover = $_FILES['cover'] ?? null;
$cover_path = null;

if (!$name) {
    echo json_encode(['error' => 'El nombre es obligatorio']);
    exit;
}

if ($cover && $cover['error'] === UPLOAD_ERR_OK) {
    // Validación de tipo MIME real
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $cover['tmp_name']);
    finfo_close($finfo);

    $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    
    if (!in_array($mimeType, $allowedMimes)) {
        echo json_encode(['error' => 'La portada debe ser una imagen válida (JPG, PNG, GIF, WEBP)']);
        exit;
    }

    $upload_dir = '../uploads/covers/';
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
    
    $filename = time() . '-' . basename($cover['name']);
    $target = $upload_dir . $filename;
    
    if (move_uploaded_file($cover['tmp_name'], $target)) {
        $cover_path = 'uploads/covers/' . $filename;
    }
}

$stmt = $db->prepare("INSERT INTO albums (name, cover_url) VALUES (:name, :cover)");
$stmt->bindValue(':name', $name);
$stmt->bindValue(':cover', $cover_path);
$stmt->execute();

echo json_encode(['success' => true, 'id' => $db->lastInsertId()]);
?>

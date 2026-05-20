<?php
header('Content-Type: application/json');
require_once '../config.php';

$id = $_POST['id'] ?? null;

if (!$id) {
    echo json_encode(['error' => 'ID no proporcionado']);
    exit;
}

// Obtener nombre del archivo antes de borrar
$stmt = $db->prepare("SELECT filename FROM songs WHERE id = :id");
$stmt->bindValue(':id', $id);
$stmt->execute();
$song = $stmt->fetch(PDO::FETCH_ASSOC);

if ($song) {
    try {
        // Borrar el archivo físico si existe
        $filepath = '../audio/' . $song['filename'];
        if (file_exists($filepath)) {
            @unlink($filepath);
        }
        
        // Borrar de la base de datos (independientemente de si el archivo existía o no)
        $stmt = $db->prepare("DELETE FROM songs WHERE id = :id");
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Error en la base de datos: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'La canción ya no existe en el sistema']);
}
?>

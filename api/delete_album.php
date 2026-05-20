<?php
header('Content-Type: application/json');
require_once '../config.php';

$id = $_POST['id'] ?? null;

if (!$id) {
    echo json_encode(['error' => 'ID de álbum no proporcionado']);
    exit;
}

try {
    // Verificar si tiene canciones
    $stmt = $db->prepare("SELECT COUNT(*) FROM songs WHERE album_id = :id");
    $stmt->bindValue(':id', $id);
    $stmt->execute();
    $count = $stmt->fetchColumn();

    if ($count > 0) {
        echo json_encode(['error' => 'No se puede eliminar un álbum que contiene canciones. Primero mueve o elimina las canciones.']);
        exit;
    }

    // Obtener portada para borrarla
    $stmt = $db->prepare("SELECT cover_url FROM albums WHERE id = :id");
    $stmt->bindValue(':id', $id);
    $stmt->execute();
    $album = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($album && $album['cover_url']) {
        $filepath = '../' . $album['cover_url'];
        if (file_exists($filepath)) @unlink($filepath);
    }

    // Borrar de la DB
    $stmt = $db->prepare("DELETE FROM albums WHERE id = :id");
    $stmt->bindValue(':id', $id);
    $stmt->execute();

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>

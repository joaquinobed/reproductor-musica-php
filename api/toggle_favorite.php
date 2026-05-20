<?php
header('Content-Type: application/json');
require_once '../config.php';

$id = $_POST['id'] ?? null;

if (!$id) {
    echo json_encode(['error' => 'ID no proporcionado']);
    exit;
}

try {
    // Obtener estado actual
    $stmt = $db->prepare("SELECT is_favorite FROM songs WHERE id = :id");
    $stmt->bindValue(':id', $id);
    $stmt->execute();
    $song = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($song) {
        $new_status = $song['is_favorite'] == 1 ? 0 : 1;
        $stmt = $db->prepare("UPDATE songs SET is_favorite = :status WHERE id = :id");
        $stmt->bindValue(':status', $new_status);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        
        echo json_encode(['success' => true, 'is_favorite' => $new_status]);
    } else {
        echo json_encode(['error' => 'Canción no encontrada']);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>

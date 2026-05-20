<?php
header('Content-Type: application/json');
require_once '../config.php';

$id = $_POST['id'] ?? null;
$name = $_POST['name'] ?? null;

if (!$id || !$name) {
    echo json_encode(['error' => 'Datos incompletos']);
    exit;
}

try {
    $stmt = $db->prepare("UPDATE albums SET name = :name WHERE id = :id");
    $stmt->bindValue(':name', $name);
    $stmt->bindValue(':id', $id);
    $stmt->execute();
    
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>

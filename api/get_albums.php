<?php
header('Content-Type: application/json');
require_once '../config.php';

$results = $db->query("SELECT * FROM albums");
$albums = $results->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($albums);
?>

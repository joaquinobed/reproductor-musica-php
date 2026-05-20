<?php
/**
 * Conexión a SQLite y creación de tablas
 */
$db = new SQLite3(__DIR__ . '/../database.db');

// Migraciones automáticas
$db->exec("CREATE TABLE IF NOT EXISTS albums (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    cover_url TEXT
)");

$db->exec("CREATE TABLE IF NOT EXISTS songs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    album_id INTEGER,
    title TEXT NOT NULL,
    artist TEXT,
    duration TEXT,
    filename TEXT NOT NULL
)");
?>

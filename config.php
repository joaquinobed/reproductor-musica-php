<?php
/**
 * Configuración general del proyecto
 */

define('APP_NAME', 'PHP SoundStream');
define('AUDIO_DIR', 'audio/');
define('DB_FILE', __DIR__ . '/database.sqlite');

try {
    // Conexión mediante PDO para mayor compatibilidad
    $db = new PDO("sqlite:" . DB_FILE);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Migraciones: Creación de tablas si no existen
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
        filename TEXT NOT NULL,
        is_favorite INTEGER DEFAULT 0,
        FOREIGN KEY (album_id) REFERENCES albums(id)
    )");

    // Asegurar que la columna existe por si la tabla ya fue creada previamente
    $checkColumn = $db->query("PRAGMA table_info(songs)");
    $hasFavorite = false;
    while($col = $checkColumn->fetch(PDO::FETCH_ASSOC)) {
        if($col['name'] === 'is_favorite') {
            $hasFavorite = true;
            break;
        }
    }
    if(!$hasFavorite) {
        $db->exec("ALTER TABLE songs ADD COLUMN is_favorite INTEGER DEFAULT 0");
    }

} catch (PDOException $e) {
    die("Error de base de datos: " . $e->getMessage());
}
?>

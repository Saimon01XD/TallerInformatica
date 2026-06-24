<?php
/*
    Archivo: database.php
    Configuración limpia y persistente de la base de datos SQLite
*/

// Si la petición es vía navegador, no mostrar mensajes de depuración
if (php_sapi_name() !== 'cli') {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0);
} else {
    // Solo mostrar errores en consola (CLI)
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

date_default_timezone_set('America/Santiago');

// Crear directorio para datos utilizando ruta absoluta segura
$dataDir = __DIR__ . "/data";
if (!is_dir($dataDir)) {
    mkdir($dataDir, 0755, true);
}

$rutaDb = $dataDir . "/tienda.db";

try {
    if (!extension_loaded('pdo_sqlite')) {
        throw new Exception("La extensión pdo_sqlite no está habilitada en PHP.");
    }

    $pdo = new PDO("sqlite:" . $rutaDb);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Habilitar el modo WAL para asegurar escrituras persistentes inmediatas
    $pdo->exec("PRAGMA journal_mode = WAL;");

    // Crear la estructura de la tabla si no existe (ahora completamente vacía)
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS productos (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nombre TEXT NOT NULL,
            categoria TEXT NOT NULL,
            precio REAL NOT NULL CHECK (precio >= 0),
            stock INTEGER NOT NULL CHECK (stock >= 0),
            descripcion TEXT NOT NULL,
            fecha_creacion TEXT NOT NULL
        )
    ");

} catch (Exception $e) {
    if (php_sapi_name() === 'cli') {
        die("Error: " . $e->getMessage() . "\n");
    }
    throw $e;
}

// Retornar la conexión PDO
return $pdo;
?>
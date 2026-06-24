<?php
/*
    Archivo: crear_base_datos.php

    Este archivo crea la base de datos SQLite y la tabla productos.

    Para ejecutarlo en Codespaces:
    php crear_base_datos.php

    Luego puedes abrir la página con:
    php -S 0.0.0.0:8080
*/

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$rutaDb = __DIR__ . "/tienda.db";

try {
    if (!extension_loaded('pdo_sqlite')) {
        throw new Exception("La extensión pdo_sqlite no está habilitada en PHP.");
    }

    $pdo = new PDO("sqlite:" . $rutaDb);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS productos (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nombre TEXT NOT NULL,
            categoria TEXT NOT NULL,
            precio REAL NOT NULL,
            stock INTEGER NOT NULL,
            descripcion TEXT NOT NULL,
            fecha_creacion DATETIME DEFAULT (datetime('now','localtime'))
        )
    ");

    echo "Base de datos creada correctamente.\n";
    echo "Archivo generado: tienda.db\n";
    echo "Tabla creada: productos\n";

} catch (Exception $e) {
    echo "Error al crear la base de datos: " . $e->getMessage() . "\n";
}
?>

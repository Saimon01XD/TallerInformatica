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

date_default_timezone_set('America/Santiago');

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
            precio REAL NOT NULL CHECK (precio >= 0),
            stock INTEGER NOT NULL CHECK (stock >= 0),
            descripcion TEXT NOT NULL,
            fecha_creacion TEXT NOT NULL
        )
    ");

} catch (Exception $e) {
    die("Error de conexión o creación de base de datos: " . $e->getMessage());
}
?>

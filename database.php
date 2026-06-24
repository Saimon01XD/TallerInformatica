<?php
/*
    Archivo: database.php
    Configuración de la base de datos SQLite
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

// Crear directorio para datos si no existe
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

    // Crear tabla si no existe
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

    // Insertar productos de ejemplo SOLO si estamos en CLI (consola)
    if (php_sapi_name() === 'cli') {
        $stmt = $pdo->query("SELECT COUNT(*) FROM productos");
        $count = $stmt->fetchColumn();
        
        if ($count == 0) {
            $productosEjemplo = [
                ['Laptop HP', 'Computadoras', 899990, 5, 'Laptop HP Pavilion con 16GB RAM', date('Y-m-d H:i:s')],
                ['Mouse Logitech', 'Accesorios', 29990, 15, 'Mouse inalámbrico Logitech MX', date('Y-m-d H:i:s')],
                ['Monitor Samsung', 'Monitores', 249990, 3, 'Monitor 24" Samsung Full HD', date('Y-m-d H:i:s')]
            ];
            
            $insert = $pdo->prepare("INSERT INTO productos (nombre, categoria, precio, stock, descripcion, fecha_creacion) VALUES (?, ?, ?, ?, ?, ?)");
            foreach ($productosEjemplo as $producto) {
                $insert->execute($producto);
            }
            // Este mensaje solo se verá en consola
            echo "Productos de ejemplo insertados.\n";
        }
    }

} catch (Exception $e) {
    // Manejar errores de forma silenciosa para el navegador
    if (php_sapi_name() === 'cli') {
        die("Error: " . $e->getMessage());
    }
    // Para el navegador, simplemente lanzar la excepción
    throw $e;
}

// Retornar la conexión PDO para usar en otros archivos
return $pdo;
?>
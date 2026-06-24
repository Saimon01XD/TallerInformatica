<?php
/*
    Archivo: database.php
    Configuración de la base de datos SQLite
*/

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

date_default_timezone_set('America/Santiago');

// Crear directorio para datos si no existe
$dataDir = __DIR__ . "/data";
if (!is_dir($dataDir)) {
    mkdir($dataDir, 0755, true);
}

// Guardar en data/ para persistencia
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

    // Verificar si hay datos de ejemplo
    $stmt = $pdo->query("SELECT COUNT(*) FROM productos");
    $count = $stmt->fetchColumn();
    
    if ($count == 0) {
        // Insertar productos de ejemplo
        $productosEjemplo = [
            ['Laptop HP', 'Computadoras', 899.99, 5, 'Laptop HP Pavilion con 16GB RAM', date('Y-m-d H:i:s')],
            ['Mouse Logitech', 'Accesorios', 29.99, 15, 'Mouse inalámbrico Logitech MX', date('Y-m-d H:i:s')],
            ['Monitor Samsung', 'Monitores', 249.99, 3, 'Monitor 24" Samsung Full HD', date('Y-m-d H:i:s')]
        ];
        
        $insert = $pdo->prepare("INSERT INTO productos (nombre, categoria, precio, stock, descripcion, fecha_creacion) VALUES (?, ?, ?, ?, ?, ?)");
        foreach ($productosEjemplo as $producto) {
            $insert->execute($producto);
        }
        echo "Productos de ejemplo insertados.\n";
    }

    echo "Base de datos creada/conectada exitosamente en: " . $rutaDb . "\n";

} catch (Exception $e) {
    die("Error de conexión o creación de base de datos: " . $e->getMessage());
}
?>
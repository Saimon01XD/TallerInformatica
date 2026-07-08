<?php
date_default_timezone_set('America/Santiago');

try {
    $pdo = new PDO('sqlite:' . __DIR__ . '/tienda.db');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    /* Tabla de usuarios */
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS usuarios (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            correo TEXT NOT NULL UNIQUE,
            password TEXT NOT NULL,
            fecha_registro TEXT NOT NULL
        )
    ");

    /* Tabla de productos */
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

    /* Tabla de logs / bitácora */
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS logs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            fecha_hora TEXT NOT NULL,
            nombre_usuario TEXT NOT NULL,
            tipo TEXT NOT NULL,
            detalle TEXT NOT NULL,
            ip_host_cliente TEXT NOT NULL
        )
    ");

} catch (PDOException $e) {
    die("Error de conexión a la base de datos: " . $e->getMessage());
}

/* Obtener IP del cliente */
function obtenerIPCliente() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
        return $_SERVER['REMOTE_ADDR'];
    }

    return 'IP no disponible';
}

/* Registrar evento en bitácora */
function registrarLog($pdo, $nombreUsuario, $tipo, $detalle) {
    $fechaHora = date('d/m/Y, H:i:s');
    $ipCliente = obtenerIPCliente();

    $stmt = $pdo->prepare("
        INSERT INTO logs (fecha_hora, nombre_usuario, tipo, detalle, ip_host_cliente)
        VALUES (?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $fechaHora,
        $nombreUsuario,
        $tipo,
        $detalle,
        $ipCliente
    ]);
}
?>

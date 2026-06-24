<?php
require 'database.php';

$mensaje = "";

/* ===== CREAR PRODUCTO ===== */
if (isset($_POST['crear'])) {
    $nombre = trim($_POST['nombre']);
    $categoria = trim($_POST['categoria']);
    $precio = $_POST['precio'];
    $stock = $_POST['stock'];
    $descripcion = trim($_POST['descripcion']);
    $fechaCreacion = date('Y-m-d H:i:s');

    if ($precio < 0) {
        $mensaje = "Error: el precio no puede ser negativo.";
    } elseif ($stock < 0) {
        $mensaje = "Error: el stock no puede ser negativo.";
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO productos (nombre, categoria, precio, stock, descripcion, fecha_creacion)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$nombre, $categoria, $precio, $stock, $descripcion, $fechaCreacion]);
        header("Location: index.php");
        exit;
    }
}

/* ===== ELIMINAR PRODUCTO ===== */
if (isset($_GET['eliminar'])) {
    $stmt = $pdo->prepare("DELETE FROM productos WHERE id = ?");
    $stmt->execute([$_GET['eliminar']]);
    header("Location: index.php");
    exit;
}

/* ===== LEER PRODUCTOS ===== */
// Usar try-catch por si la tabla aún no existe
try {
    $productos = $pdo->query("SELECT * FROM productos ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $productos = [];
    $mensaje = "Aviso: La tabla de productos no existe. Por favor, ejecuta php init_db.php en la terminal.";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Tienda Tecnológica</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f8; padding: 30px; }
        .container { max-width: 1000px; background: #fff; padding: 25px; margin: auto; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h1, h2 { color: #2c3e50; }
        .descripcion { background: #eff6ff; border: 1px solid #bfdbfe; color: #1e3a8a; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .integrantes, .crud-info, .mockup { margin-bottom: 25px; padding: 18px; border-radius: 8px; background: #f9fafb; border: 1px solid #d1d5db; }
        .integrantes ul, .crud-info ul { margin: 10px 0 0 20px; padding: 0; }
        .integrantes li, .crud-info li { margin-bottom: 8px; }
        .mockup img { max-width: 100%; border-radius: 8px; border: 1px solid #cbd5e1; margin-top: 12px; }
        label { font-weight: bold; color: #374151; }
        input, textarea { width: 100%; padding: 8px; margin-top: 5px; margin-bottom: 15px; box-sizing: border-box; }
        button { background: #2563eb; color: white; border: none; padding: 10px 18px; border-radius: 5px; cursor: pointer; font-weight: bold; }
        button:hover { background: #1d4ed8; }
        table { width: 100%; border-collapse: collapse; margin-top: 25px; }
        th, td { padding: 10px; border-bottom: 1px solid #ddd; text-align: left; }
        th { background: #1f2937; color: white; }
        tr:nth-child(even) { background: #f9fafb; }
        a { text-decoration: none; font-weight: bold; }
        .editar { color: #2563eb; }
        .eliminar { color: red; }
        .mensaje-error { background: #fee2e2; border: 1px solid #fca5a5; color: #991b1b; padding: 12px; border-radius: 5px; margin-bottom: 15px; font-weight: bold; }
    </style>
</head>
<body>

<div class="container">
    <h1>Tienda de Artículos Tecnológicos</h1>
    
    <div class="descripcion">
        Aplicación web dinámica desarrollada en PHP con base de datos SQLite.
        Permite gestionar productos tecnológicos mediante operaciones CRUD.
    </div>

    <section class="integrantes">
        <h2>Integrantes del Proyecto</h2>
        <ul>
            <li>Gabriel Lebien</li>
            <li>Simón Pérez</li>
            <li>Sebastián Valderas</li>
        </ul>
    </section>

    <?php if ($mensaje): ?>
        <div class="mensaje-error">
            <?= htmlspecialchars($mensaje) ?>
        </div>
    <?php endif; ?>

    <h2>Crear Producto</h2>
    <form method="POST">
        <label>Nombre del producto</label>
        <input type="text" name="nombre" required>

        <label>Categoría</label>
        <input type="text" name="categoria" required>

        <label>Precio</label>
        <input type="number" name="precio" min="0" step="0.01" required>

        <label>Stock</label>
        <input type="number" name="stock" min="0" required>

        <label>Descripción</label>
        <textarea name="descripcion" required></textarea>

        <button type="submit" name="crear">Crear Producto</button>
    </form>

    <h2>Leer, Modificar y Borrar Productos</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Categoría</th>
            <th>Descripción</th>
            <th>Precio</th>
            <th>Stock</th>
            <th>Fecha y hora</th>
            <th>Acciones</th>
        </tr>

        <?php if (count($productos) > 0): ?>
            <?php foreach ($productos as $p): ?>
                <tr>
                    <td><?= htmlspecialchars($p['id']) ?></td>
                    <td><?= htmlspecialchars($p['nombre']) ?></td>
                    <td><?= htmlspecialchars($p['categoria']) ?></td>
                    <td><?= htmlspecialchars($p['descripcion']) ?></td>
                    <td>$<?= number_format($p['precio'], 0, ',', '.') ?></td>
                    <td><?= htmlspecialchars($p['stock']) ?></td>
                    <td><?= htmlspecialchars($p['fecha_creacion']) ?></td>
                    <td>
                        <a class="editar" href="editar.php?id=<?= $p['id'] ?>">Editar</a> |
                        <a class="eliminar" href="?eliminar=<?= $p['id'] ?>"
                           onclick="return confirm('¿Eliminar producto?')">
                           Eliminar
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="8">No existen productos registrados.</td>
            </tr>
        <?php endif; ?>
    </table>
</div>

</body>
</html>
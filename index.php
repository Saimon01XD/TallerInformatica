<?php
require 'database.php';

/* ===== CREAR PRODUCTO ===== */
if (isset($_POST['crear'])) {
    $stmt = $pdo->prepare("
        INSERT INTO productos (nombre, categoria, precio, stock, descripcion)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $_POST['nombre'],
        $_POST['categoria'],
        $_POST['precio'],
        $_POST['stock'],
        $_POST['descripcion']
    ]);
    header("Location: index.php");
    exit;
}

/* ===== ELIMINAR ===== */
if (isset($_GET['eliminar'])) {
    $stmt = $pdo->prepare("DELETE FROM productos WHERE id = ?");
    $stmt->execute([$_GET['eliminar']]);
    header("Location: index.php");
    exit;
}

/* ===== LEER ===== */
$productos = $pdo->query("SELECT * FROM productos ORDER BY id DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Tienda Tecnológica</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f8;
            padding: 30px;
        }
        .container {
            max-width: 1000px;
            background: #fff;
            padding: 25px;
            margin: auto;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1, h2 {
            color: #2c3e50;
        }
        input, textarea {
            width: 100%;
            padding: 8px;
            margin-top: 5px; 
            margin-bottom: 15px;
        }
        button {
            background: #2563eb;
            color: white;
            border: none;
            padding: 10px 18px;
            border-radius: 5px;
            cursor: pointer;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
        }
        th, td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }
        th {
            background: #1f2937;
            color: white;
        }
        a {
            text-decoration: none;
            font-weight: bold;
        }
        .eliminar {
            color: red;
        }
    </style>
</head>
<body>

<div class="container">

    <h1>Tienda de Artículos Tecnológicos</h1>

    <!-- ===== CREAR ===== -->
    <h2>Crear Producto</h2>
    <form method="POST">
        <label>Nombre del producto</label>
        <input type="text" name="nombre" required>

        <label>Categoría</label>
        <input type="text" name="categoria" required>

        <label>Precio</label>
        <input type="number" name="precio" required>

        <label>Stock</label>
        <input type="number" name="stock" required>

        <label>Descripción</label>
        <textarea name="descripcion" required></textarea>

        <button type="submit" name="crear">Crear Producto</button>
    </form>

    <!-- ===== LEER ===== -->
    <h2>Leer, Modificar y Borrar Productos</h2>

    <table>
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Categoría</th>
            <th>Descripción</th>
            <th>Precio</th>
            <th>Stock</th>
            <th>Fecha</th>
            <th>Acciones CRUD</th>
        </tr>

        <?php foreach ($productos as $p): ?>
        <tr>
            <td><?= $p['id'] ?></td>
            <td><?= htmlspecialchars($p['nombre']) ?></td>
            <td><?= htmlspecialchars($p['categoria']) ?></td>
            <td><?= htmlspecialchars($p['descripcion']) ?></td>
            <td>$<?= number_format($p['precio'], 0, ',', '.') ?></td>
            <td><?= $p['stock'] ?></td>
            <td><?= $p['fecha_creacion'] ?></td>
            <td>
                <!-- Update simple (puede ampliarse) -->
                <a href="editar.php?id=<?= $p['id'] ?>">Editar</a> |
                <a class="eliminar" href="?eliminar=<?= $p['id'] ?>"
                   onclick="return confirm('¿Eliminar producto?')">
                   Eliminar
                </a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>

</div>

</body>
</html>
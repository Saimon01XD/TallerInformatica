<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit;
}

require 'database.php';

$mensaje = "";

/* ===== CERRAR SESIÓN ===== */
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}

/* ===== CREAR TABLA PRODUCTOS SI NO EXISTE ===== */
try {
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
    $mensaje = "Error al preparar la tabla productos: " . $e->getMessage();
}

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

        header("Location: crud.php");
        exit;
    }
}

/* ===== ELIMINAR PRODUCTO ===== */
if (isset($_GET['eliminar'])) {
    $stmt = $pdo->prepare("DELETE FROM productos WHERE id = ?");
    $stmt->execute([$_GET['eliminar']]);

    header("Location: crud.php");
    exit;
}

/* ===== LEER PRODUCTOS ===== */
try {
    $productos = $pdo->query("SELECT * FROM productos ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $productos = [];
    $mensaje = "Aviso: no se pudieron cargar los productos.";
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Tienda Tecnológica - CRUD</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f8;
            padding: 30px;
            margin: 0;
        }

        .container {
            max-width: 1000px;
            background: #fff;
            padding: 25px;
            margin: auto;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h1,
        h2 {
            color: #2c3e50;
        }

        .menu-superior {
            background: #1f2937;
            padding: 12px 20px;
            margin: -30px -30px 25px -30px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
        }

        .menu-superior a {
            color: white;
            text-decoration: none;
            padding: 8px 14px;
            border-radius: 5px;
            font-weight: bold;
            background: #374151;
            transition: background 0.2s;
        }

        .menu-superior a:hover {
            background: #2563eb;
        }

        .menu-superior a.salir {
            background: #dc2626;
            margin-left: auto;
        }

        .menu-superior a.salir:hover {
            background: #b91c1c;
        }

        .descripcion {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            color: #1e3a8a;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .usuario-box {
            background: #f0fdf4;
            border: 1px solid #86efac;
            color: #166534;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .integrantes,
        .crud-info {
            margin-bottom: 25px;
            padding: 18px;
            border-radius: 8px;
            background: #f9fafb;
            border: 1px solid #d1d5db;
        }

        .integrantes ul,
        .crud-info ul {
            margin: 10px 0 0 20px;
            padding: 0;
        }

        .integrantes li,
        .crud-info li {
            margin-bottom: 8px;
        }

        label {
            font-weight: bold;
            color: #374151;
        }

        input,
        textarea {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            margin-bottom: 15px;
            box-sizing: border-box;
            border: 1px solid #cbd5e1;
            border-radius: 5px;
        }

        button {
            background: #2563eb;
            color: white;
            border: none;
            padding: 10px 18px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }

        button:hover {
            background: #1d4ed8;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
        }

        th,
        td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
            text-align: left;
            vertical-align: top;
        }

        th {
            background: #1f2937;
            color: white;
        }

        tr:nth-child(even) {
            background: #f9fafb;
        }

        a {
            text-decoration: none;
            font-weight: bold;
        }

        .editar {
            color: #2563eb;
        }

        .eliminar {
            color: red;
        }

        .mensaje-error {
            background: #fee2e2;
            border: 1px solid #fca5a5;
            color: #991b1b;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 15px;
            font-weight: bold;
        }
    </style>
</head>

<body>

    <nav class="menu-superior">
        <a href="crud.php">🏠 Inicio</a>
        <a href="crud.php#crear-producto">➕ Crear Producto</a>
        <a href="crud.php#tabla-productos">✏️ Editar Producto</a>
        <a href="crud.php#tabla-productos">🗑️ Eliminar Producto</a>
        <a class="salir" href="crud.php?logout=1" onclick="return confirm('¿Cerrar sesión?')">🚪 Cerrar Sesión</a>
    </nav>

    <div class="container">
        <h1>Tienda de Artículos Tecnológicos</h1>

        <div class="usuario-box">
            Sesión iniciada como:
            <strong><?= htmlspecialchars($_SESSION['usuario_correo']) ?></strong>
        </div>

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

        <section class="crud-info">
            <h2>Descripción de Operaciones CRUD</h2>
            <ul>
                <li><strong>Crear:</strong> permite registrar un nuevo producto tecnológico indicando nombre, categoría, precio, stock y descripción.</li>
                <li><strong>Leer:</strong> permite visualizar todos los productos almacenados en la base de datos mediante una tabla.</li>
                <li><strong>Modificar:</strong> permite actualizar los datos de un producto existente a través de la opción Editar.</li>
                <li><strong>Borrar:</strong> permite eliminar un producto registrado mediante la opción Eliminar.</li>
            </ul>
        </section>

        <?php if ($mensaje): ?>
            <div class="mensaje-error">
                <?= htmlspecialchars($mensaje) ?>
            </div>
        <?php endif; ?>

        <h2 id="crear-producto">Crear Producto</h2>

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

        <h2 id="tabla-productos">Leer, Modificar y Borrar Productos</h2>

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
                            <a class="editar" href="editar.php?id=<?= htmlspecialchars($p['id']) ?>">Editar</a> |
                            <a class="eliminar" href="?eliminar=<?= htmlspecialchars($p['id']) ?>"
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
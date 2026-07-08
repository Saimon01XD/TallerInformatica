<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit;
}

require 'database.php';

$usuarioActual = $_SESSION['usuario_correo'];
$mensaje = "";
$accion = $_GET['accion'] ?? 'consultar';

/* Cerrar sesión */
if (isset($_GET['logout'])) {
    registrarLog(
        $pdo,
        $usuarioActual,
        "Cierre de sesión",
        "Tabla: usuarios | Usuario cerró sesión"
    );

    session_destroy();
    header("Location: index.php");
    exit;
}

/* Crear producto */
if (isset($_POST['crear'])) {
    $nombre = trim($_POST['nombre']);
    $categoria = trim($_POST['categoria']);
    $precio = $_POST['precio'];
    $stock = $_POST['stock'];
    $descripcion = trim($_POST['descripcion']);
    $fechaCreacion = date('d/m/Y, H:i:s');

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

        $productoId = $pdo->lastInsertId();

        registrarLog(
            $pdo,
            $usuarioActual,
            "Crear registro",
            "Tabla: productos | ID creado: " . $productoId . " | Nombre: " . $nombre
        );

        header("Location: crud.php?accion=consultar");
        exit;
    }
}

/* Eliminar producto */
if (isset($_GET['eliminar'])) {
    $idEliminar = $_GET['eliminar'];

    $stmtConsulta = $pdo->prepare("SELECT * FROM productos WHERE id = ?");
    $stmtConsulta->execute([$idEliminar]);
    $productoEliminado = $stmtConsulta->fetch(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("DELETE FROM productos WHERE id = ?");
    $stmt->execute([$idEliminar]);

    registrarLog(
        $pdo,
        $usuarioActual,
        "Eliminar registro",
        "Tabla: productos | ID eliminado: " . $idEliminar . " | Nombre: " . ($productoEliminado['nombre'] ?? 'No disponible')
    );

    header("Location: crud.php?accion=eliminar");
    exit;
}

/* Consultar productos */
$productos = $pdo->query("SELECT * FROM productos ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

/* Consultar logs */
$logs = $pdo->query("SELECT * FROM logs ORDER BY id DESC LIMIT 50")->fetchAll(PDO::FETCH_ASSOC);
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
            max-width: 1100px;
            background: #fff;
            padding: 25px;
            margin: auto;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        h1, h2 {
            color: #2c3e50;
        }

        .menu {
            background: #1f2937;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 25px;
        }

        .menu a {
            color: white;
            margin-right: 18px;
            text-decoration: none;
            font-weight: bold;
        }

        .menu a:hover {
            text-decoration: underline;
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

        .integrantes, .crud-info, .mockup, .seccion {
            margin-bottom: 25px;
            padding: 18px;
            border-radius: 8px;
            background: #f9fafb;
            border: 1px solid #d1d5db;
        }

        .integrantes ul, .crud-info ul {
            margin: 10px 0 0 20px;
            padding: 0;
        }

        .integrantes li, .crud-info li {
            margin-bottom: 8px;
        }

        .mockup img {
            display: block;
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            border: 1px solid #cbd5e1;
            margin-top: 12px;
        }

        label {
            font-weight: bold;
            color: #374151;
        }

        input, textarea {
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
            margin-top: 18px;
            font-size: 14px;
        }

        th, td {
            padding: 9px;
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

<div class="container">
    <h1>Tienda de Artículos Tecnológicos</h1>

    <div class="menu">
        <a href="crud.php?accion=crear">Crear</a>
        <a href="crud.php?accion=consultar">Consultar</a>
        <a href="crud.php?accion=modificar">Modificar</a>
        <a href="crud.php?accion=eliminar">Eliminar</a>
        <a href="crud.php?logout=1">Cerrar sesión</a>
    </div>

    <div class="usuario-box">
        Sesión iniciada como:
        <strong><?= htmlspecialchars($usuarioActual) ?></strong>
    </div>

    <div class="descripcion">
        Aplicación web dinámica desarrollada en PHP con base de datos SQLite.
        Permite gestionar productos tecnológicos mediante operaciones CRUD y registra eventos en una bitácora.
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
            <li><strong>Crear:</strong> permite registrar un nuevo producto tecnológico.</li>
            <li><strong>Consultar:</strong> permite visualizar los productos almacenados en la base de datos.</li>
            <li><strong>Modificar:</strong> permite actualizar los datos de un producto existente.</li>
            <li><strong>Eliminar:</strong> permite borrar un producto registrado.</li>
        </ul>
    </section>

    <?php if ($mensaje): ?>
        <div class="mensaje-error">
            <?= htmlspecialchars($mensaje) ?>
        </div>
    <?php endif; ?>

    <?php if ($accion === 'crear'): ?>
        <section class="seccion">
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
        </section>
    <?php endif; ?>

    <?php if ($accion === 'consultar'): ?>
        <section class="seccion">
            <h2>Consultar Productos</h2>

            <table>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Categoría</th>
                    <th>Descripción</th>
                    <th>Precio</th>
                    <th>Stock</th>
                    <th>Fecha y hora</th>
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
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7">No existen productos registrados.</td>
                    </tr>
                <?php endif; ?>
            </table>
        </section>
    <?php endif; ?>

    <?php if ($accion === 'modificar'): ?>
        <section class="seccion">
            <h2>Modificar Productos</h2>

            <table>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Categoría</th>
                    <th>Precio</th>
                    <th>Stock</th>
                    <th>Acción</th>
                </tr>

                <?php foreach ($productos as $p): ?>
                    <tr>
                        <td><?= htmlspecialchars($p['id']) ?></td>
                        <td><?= htmlspecialchars($p['nombre']) ?></td>
                        <td><?= htmlspecialchars($p['categoria']) ?></td>
                        <td>$<?= number_format($p['precio'], 0, ',', '.') ?></td>
                        <td><?= htmlspecialchars($p['stock']) ?></td>
                        <td>
                            <a class="editar" href="editar.php?id=<?= htmlspecialchars($p['id']) ?>">Editar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </section>
    <?php endif; ?>

    <?php if ($accion === 'eliminar'): ?>
        <section class="seccion">
            <h2>Eliminar Productos</h2>

            <table>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Categoría</th>
                    <th>Precio</th>
                    <th>Stock</th>
                    <th>Acción</th>
                </tr>

                <?php foreach ($productos as $p): ?>
                    <tr>
                        <td><?= htmlspecialchars($p['id']) ?></td>
                        <td><?= htmlspecialchars($p['nombre']) ?></td>
                        <td><?= htmlspecialchars($p['categoria']) ?></td>
                        <td>$<?= number_format($p['precio'], 0, ',', '.') ?></td>
                        <td><?= htmlspecialchars($p['stock']) ?></td>
                        <td>
                            <a class="eliminar" href="crud.php?accion=eliminar&eliminar=<?= htmlspecialchars($p['id']) ?>"
                               onclick="return confirm('¿Eliminar producto?')">
                                Eliminar
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </section>
    <?php endif; ?>

    <section class="seccion">
        <h2>Bitácora / Historial de Eventos</h2>

        <table>
            <tr>
                <th>ID</th>
                <th>Fecha hora</th>
                <th>Nombre de usuario</th>
                <th>Tipo</th>
                <th>Detalle</th>
                <th>IP_HOST_CLIENTE</th>
            </tr>

            <?php foreach ($logs as $log): ?>
                <tr>
                    <td><?= htmlspecialchars($log['id']) ?></td>
                    <td><?= htmlspecialchars($log['fecha_hora']) ?></td>
                    <td><?= htmlspecialchars($log['nombre_usuario']) ?></td>
                    <td><?= htmlspecialchars($log['tipo']) ?></td>
                    <td><?= htmlspecialchars($log['detalle']) ?></td>
                    <td><?= htmlspecialchars($log['ip_host_cliente']) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    </section>

</div>

</body>
</html>

<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit;
}

require 'database.php';

$usuarioActual = $_SESSION['usuario_correo'];
$mensaje = "";

if (!isset($_GET['id'])) {
    header("Location: crud.php?accion=modificar");
    exit;
}

$id = $_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM productos WHERE id = ?");
$stmt->execute([$id]);
$producto = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$producto) {
    header("Location: crud.php?accion=modificar");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $categoria = trim($_POST['categoria']);
    $precio = $_POST['precio'];
    $stock = $_POST['stock'];
    $descripcion = trim($_POST['descripcion']);

    if ($precio < 0) {
        $mensaje = "Error: el precio no puede ser negativo.";
    } elseif ($stock < 0) {
        $mensaje = "Error: el stock no puede ser negativo.";
    } else {
        $stmt = $pdo->prepare("
            UPDATE productos
            SET nombre = ?, categoria = ?, precio = ?, stock = ?, descripcion = ?
            WHERE id = ?
        ");

        $stmt->execute([$nombre, $categoria, $precio, $stock, $descripcion, $id]);

        registrarLog(
            $pdo,
            $usuarioActual,
            "Modificar registro",
            "Tabla: productos | ID modificado: " . $id . " | Nombre: " . $nombre
        );

        header("Location: crud.php?accion=modificar");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Producto</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f8;
            padding: 30px;
            margin: 0;
        }

        .container {
            max-width: 700px;
            background: #fff;
            padding: 25px;
            margin: auto;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.12);
        }

        h1 {
            color: #1f2937;
        }

        label {
            font-weight: bold;
            color: #374151;
        }

        input, textarea {
            width: 100%;
            padding: 9px;
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

        a {
            margin-left: 10px;
            font-weight: bold;
            text-decoration: none;
            color: #6b7280;
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
    <h1>Editar Producto</h1>

    <?php if ($mensaje): ?>
        <div class="mensaje-error">
            <?= htmlspecialchars($mensaje) ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <label>Nombre del producto</label>
        <input type="text" name="nombre" value="<?= htmlspecialchars($producto['nombre']) ?>" required>

        <label>Categoría</label>
        <input type="text" name="categoria" value="<?= htmlspecialchars($producto['categoria']) ?>" required>

        <label>Precio</label>
        <input type="number" name="precio" min="0" step="0.01" value="<?= htmlspecialchars($producto['precio']) ?>" required>

        <label>Stock</label>
        <input type="number" name="stock" min="0" value="<?= htmlspecialchars($producto['stock']) ?>" required>

        <label>Descripción</label>
        <textarea name="descripcion" required><?= htmlspecialchars($producto['descripcion']) ?></textarea>

        <button type="submit">Guardar Cambios</button>
        <a href="crud.php?accion=modificar">Cancelar</a>
    </form>
</div>

</body>
</html>

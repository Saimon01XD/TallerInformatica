<?php
require 'database.php';

/* ===== VALIDAR ID ===== */
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = $_GET['id'];

/* ===== OBTENER PRODUCTO ===== */
$stmt = $pdo->prepare("SELECT * FROM productos WHERE id = ?");
$stmt->execute([$id]);
$producto = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$producto) {
    echo "Producto no encontrado.";
    exit;
}

$mensaje = "";

/* ===== ACTUALIZAR PRODUCTO ===== */
if (isset($_POST['actualizar'])) {
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

        header("Location: index.php");
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
        body { font-family: Arial, sans-serif; background: #f4f6f8; padding: 30px; }
        .container { max-width: 700px; background: #fff; padding: 25px; margin: auto; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h1 { color: #2c3e50; }
        label { font-weight: bold; color: #374151; }
        input, textarea { width: 100%; padding: 8px; margin-top: 5px; margin-bottom: 15px; box-sizing: border-box; }
        button { background: #16a34a; color: white; border: none; padding: 10px 18px; border-radius: 5px; cursor: pointer; font-weight: bold; }
        a { display: inline-block; margin-top: 15px; text-decoration: none; color: #2563eb; font-weight: bold; }
        .mensaje-error { background: #fee2e2; border: 1px solid #fca5a5; color: #991b1b; padding: 12px; border-radius: 5px; margin-bottom: 15px; font-weight: bold; }
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

        <button type="submit" name="actualizar">Actualizar Producto</button>
    </form>

    <a href="index.php">⬅ Volver al listado</a>
</div>

</body>
</html>
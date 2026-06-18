<?php
/*
    index.php
    Tienda de Artículos Tecnológicos con CRUD + Base de Datos SQLite

    Para ejecutar en GitHub Codespaces:
    php -S 0.0.0.0:8080

    Archivos esperados en la misma carpeta:
    - index.php
    - mockup.jpeg

    La base de datos se creará automáticamente como:
    - tienda.db
*/

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$dbDisponible = false;
$errorConexion = "";
$mensaje = "";
$productoEditar = null;
$productos = [];

$rutaDb = __DIR__ . "/tienda.db";

try {
    if (!extension_loaded('pdo_sqlite')) {
        throw new Exception("La extensión pdo_sqlite no está habilitada en PHP.");
    }

    $pdo = new PDO("sqlite:" . $rutaDb);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $dbDisponible = true;

    /*
        Crear tabla si no existe.
        Esto permite que el proyecto funcione aunque no exista previamente una base de datos.
    */
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS productos (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nombre TEXT NOT NULL,
            categoria TEXT NOT NULL,
            precio REAL NOT NULL,
            stock INTEGER NOT NULL,
            descripcion TEXT NOT NULL,
            fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");

    /*
        CREAR PRODUCTO
    */
    if (isset($_POST['crear'])) {
        $nombre = trim($_POST['nombre'] ?? '');
        $categoria = trim($_POST['categoria'] ?? '');
        $precio = isset($_POST['precio']) ? floatval($_POST['precio']) : -1;
        $stock = isset($_POST['stock']) ? intval($_POST['stock']) : -1;
        $descripcion = trim($_POST['descripcion'] ?? '');

        if ($nombre === '') {
            $mensaje = "Error: el nombre del producto es obligatorio.";
        } elseif ($categoria === '') {
            $mensaje = "Error: la categoría es obligatoria.";
        } elseif ($precio < 0) {
            $mensaje = "Error: el precio no puede ser negativo.";
        } elseif ($stock < 0) {
            $mensaje = "Error: el stock no puede ser negativo.";
        } elseif ($descripcion === '') {
            $mensaje = "Error: la descripción es obligatoria.";
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO productos (nombre, categoria, precio, stock, descripcion)
                VALUES (:nombre, :categoria, :precio, :stock, :descripcion)
            ");

            $stmt->execute([
                ':nombre' => $nombre,
                ':categoria' => $categoria,
                ':precio' => $precio,
                ':stock' => $stock,
                ':descripcion' => $descripcion
            ]);

            $mensaje = "Producto creado correctamente.";
        }
    }

    /*
        ACTUALIZAR PRODUCTO
    */
    if (isset($_POST['actualizar'])) {
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $nombre = trim($_POST['nombre'] ?? '');
        $categoria = trim($_POST['categoria'] ?? '');
        $precio = isset($_POST['precio']) ? floatval($_POST['precio']) : -1;
        $stock = isset($_POST['stock']) ? intval($_POST['stock']) : -1;
        $descripcion = trim($_POST['descripcion'] ?? '');

        if ($id <= 0) {
            $mensaje = "Error: ID de producto inválido.";
        } elseif ($nombre === '') {
            $mensaje = "Error: el nombre del producto es obligatorio.";
        } elseif ($categoria === '') {
            $mensaje = "Error: la categoría es obligatoria.";
        } elseif ($precio < 0) {
            $mensaje = "Error: el precio no puede ser negativo.";
        } elseif ($stock < 0) {
            $mensaje = "Error: el stock no puede ser negativo.";
        } elseif ($descripcion === '') {
            $mensaje = "Error: la descripción es obligatoria.";
        } else {
            $stmt = $pdo->prepare("
                UPDATE productos
                SET nombre = :nombre,
                    categoria = :categoria,
                    precio = :precio,
                    stock = :stock,
                    descripcion = :descripcion
                WHERE id = :id
            ");

            $stmt->execute([
                ':nombre' => $nombre,
                ':categoria' => $categoria,
                ':precio' => $precio,
                ':stock' => $stock,
                ':descripcion' => $descripcion,
                ':id' => $id
            ]);

            $mensaje = "Producto actualizado correctamente.";
        }
    }

    /*
        ELIMINAR PRODUCTO
    */
    if (isset($_GET['eliminar'])) {
        $id = intval($_GET['eliminar']);

        if ($id > 0) {
            $stmt = $pdo->prepare("DELETE FROM productos WHERE id = :id");
            $stmt->execute([':id' => $id]);

            $mensaje = "Producto eliminado correctamente.";
        } else {
            $mensaje = "Error: ID inválido para eliminar.";
        }
    }

    /*
        CARGAR PRODUCTO PARA EDITAR
    */
    if (isset($_GET['editar'])) {
        $id = intval($_GET['editar']);

        if ($id > 0) {
            $stmt = $pdo->prepare("
                SELECT id, nombre, categoria, precio, stock, descripcion, fecha_creacion
                FROM productos
                WHERE id = :id
            ");

            $stmt->execute([':id' => $id]);
            $productoEditar = $stmt->fetch(PDO::FETCH_ASSOC);
        }
    }

    /*
        LISTAR PRODUCTOS
    */
    $stmt = $pdo->query("
        SELECT id, nombre, categoria, precio, stock, descripcion, fecha_creacion
        FROM productos
        ORDER BY id DESC
    ");

    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    $dbDisponible = false;
    $errorConexion = $e->getMessage();
}

function limpiar($texto) {
    return htmlspecialchars($texto ?? '', ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Tienda de Artículos Tecnológicos</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 40px;
        }

        .container {
            background-color: #ffffff;
            padding: 25px;
            border-radius: 8px;
            max-width: 1000px;
            margin: auto;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        h1, h2 {
            color: #2c3e50;
        }

        ul {
            margin-left: 20px;
        }

        .mockup {
            text-align: center;
            margin-top: 25px;
            margin-bottom: 25px;
        }

        .mockup img {
            max-width: 100%;
            width: 700px;
            border-radius: 10px;
            border: 1px solid #ddd;
            box-shadow: 0 0 8px rgba(0,0,0,0.15);
        }

        .mockup p {
            font-size: 14px;
            color: #555;
            margin-top: 8px;
        }

        .mensaje {
            padding: 12px;
            border-radius: 6px;
            margin: 20px 0;
            background-color: #eafaf1;
            border: 1px solid #2ecc71;
            color: #1e8449;
        }

        .error {
            padding: 12px;
            border-radius: 6px;
            margin: 20px 0;
            background-color: #fdecea;
            border: 1px solid #e74c3c;
            color: #922b21;
        }

        form {
            background-color: #f8f9fa;
            padding: 18px;
            border-radius: 8px;
            margin-top: 15px;
            margin-bottom: 25px;
            border: 1px solid #ddd;
        }

        label {
            display: block;
            margin-top: 12px;
            font-weight: bold;
            color: #2c3e50;
        }

        input, textarea, select {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #bbb;
            border-radius: 5px;
            box-sizing: border-box;
            font-size: 14px;
        }

        textarea {
            resize: vertical;
            min-height: 80px;
        }

        button, .btn {
            display: inline-block;
            margin-top: 15px;
            padding: 10px 14px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            font-size: 14px;
            cursor: pointer;
        }

        button {
            background-color: #2c3e50;
            color: white;
        }

        button:hover {
            background-color: #1a252f;
        }

        .btn-editar {
            background-color: #f1c40f;
            color: #000;
        }

        .btn-eliminar {
            background-color: #e74c3c;
            color: white;
        }

        .btn-cancelar {
            background-color: #7f8c8d;
            color: white;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            font-size: 14px;
        }

        th {
            background-color: #2c3e50;
            color: white;
            padding: 10px;
            text-align: left;
        }

        td {
            border: 1px solid #ddd;
            padding: 10px;
            vertical-align: top;
        }

        tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .acciones {
            white-space: nowrap;
        }

        .nota {
            background-color: #eef5ff;
            border-left: 5px solid #3498db;
            padding: 12px;
            margin-top: 15px;
            border-radius: 5px;
        }

        .precio {
            font-weight: bold;
            color: #1e8449;
        }
    </style>
</head>

<body>

<div class="container">
    <h1>🛒 Tienda de Artículos Tecnológicos</h1>

    <h2>👥 Integrantes del grupo</h2>
    <ul>
        <li>Gabriel Lebien</li>
        <li>Simón Pérez</li>
        <li>Sebastián Valderas</li>
    </ul>

    <h2>📱 Descripción de la aplicación</h2>
    <p>
        Esta aplicación corresponde a una tienda de artículos tecnológicos cuyo objetivo es
        administrar productos como computadores, celulares, accesorios y componentes electrónicos.
        La aplicación permite gestionar la información de los productos de manera ordenada,
        facilitando el control del inventario y la administración del negocio.
    </p>

    <h2>🖼️ Mockup de la aplicación</h2>
    <div class="mockup">
        <img src="mockup.jpeg" alt="Mockup de la tienda de artículos tecnológicos">
        <p>Mockup referencial de la interfaz principal de la aplicación.</p>
    </div>

    <h2>🗄️ Base de datos utilizada</h2>
    <p>
        La aplicación utiliza una base de datos SQLite llamada <strong>tienda.db</strong>,
        la cual se crea automáticamente al ejecutar el proyecto. En ella se almacena la información
        de los productos registrados en la tienda.
    </p>

    <div class="nota">
        Para ejecutar este proyecto en GitHub Codespaces, usa el siguiente comando:
        <br><br>
        <strong>php -S 0.0.0.0:8080</strong>
    </div>

    <h2>🔄 Operaciones CRUD</h2>

    <p><strong>Crear (Create):</strong>
    Permite registrar nuevos productos en el sistema, ingresando datos como nombre, categoría,
    precio, stock y descripción.</p>

    <p><strong>Leer (Read):</strong>
    Permite visualizar el listado de productos disponibles y consultar el detalle de cada artículo.</p>

    <p><strong>Actualizar (Update):</strong>
    Permite modificar la información de los productos existentes, como actualizar precios,
    stock o descripciones.</p>

    <p><strong>Eliminar (Delete):</strong>
    Permite eliminar productos del sistema cuando ya no estén disponibles o se desee retirarlos
    del catálogo.</p>

    <?php if ($mensaje !== ''): ?>
        <div class="mensaje">
            <?php echo limpiar($mensaje); ?>
        </div>
    <?php endif; ?>

    <?php if (!$dbDisponible): ?>
        <div class="error">
            <strong>Error de base de datos:</strong>
            <?php echo limpiar($errorConexion); ?>
            <br><br>
            Si estás en Codespaces, asegúrate de que PHP tenga habilitada la extensión SQLite.
        </div>
    <?php else: ?>

        <h2>
            <?php echo $productoEditar ? "✏️ Editar producto" : "➕ Agregar nuevo producto"; ?>
        </h2>

        <form method="POST" action="index.php">
            <?php if ($productoEditar): ?>
                <input type="hidden" name="id" value="<?php echo limpiar($productoEditar['id']); ?>">
            <?php endif; ?>

            <label for="nombre">Nombre del producto</label>
            <input 
                type="text" 
                id="nombre" 
                name="nombre" 
                required
                value="<?php echo limpiar($productoEditar['nombre'] ?? ''); ?>"
                placeholder="Ejemplo: Notebook Lenovo"
            >

            <label for="categoria">Categoría</label>
            <select id="categoria" name="categoria" required>
                <?php
                    $categoriaActual = $productoEditar['categoria'] ?? '';
                    $categorias = [
                        "Computadores",
                        "Celulares",
                        "Accesorios",
                        "Componentes",
                        "Audio",
                        "Otros"
                    ];

                    foreach ($categorias as $categoria) {
                        $selected = ($categoriaActual === $categoria) ? "selected" : "";
                        echo "<option value=\"" . limpiar($categoria) . "\" $selected>" . limpiar($categoria) . "</option>";
                    }
                ?>
            </select>

            <label for="precio">Precio</label>
            <input 
                type="number" 
                id="precio" 
                name="precio" 
                min="0" 
                step="0.01" 
                required
                value="<?php echo limpiar($productoEditar['precio'] ?? ''); ?>"
                placeholder="Ejemplo: 350000"
            >

            <label for="stock">Stock</label>
            <input 
                type="number" 
                id="stock" 
                name="stock" 
                min="0" 
                required
                value="<?php echo limpiar($productoEditar['stock'] ?? ''); ?>"
                placeholder="Ejemplo: 10"
            >

            <label for="descripcion">Descripción</label>
            <textarea 
                id="descripcion" 
                name="descripcion" 
                required
                placeholder="Escribe una breve descripción del producto"
            ><?php echo limpiar($productoEditar['descripcion'] ?? ''); ?></textarea>

            <?php if ($productoEditar): ?>
                <button type="submit" name="actualizar">Actualizar producto</button>
                <a class="btn btn-cancelar" href="index.php">Cancelar</a>
            <?php else: ?>
                <button type="submit" name="crear">Crear producto</button>
            <?php endif; ?>
        </form>

        <h2>📋 Listado de productos</h2>

        <?php if (count($productos) === 0): ?>
            <p>No hay productos registrados todavía.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Producto</th>
                        <th>Categoría</th>
                        <th>Precio</th>
                        <th>Stock</th>
                        <th>Descripción</th>
                        <th>Fecha creación</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($productos as $producto): ?>
                        <tr>
                            <td><?php echo limpiar($producto['id']); ?></td>
                            <td><?php echo limpiar($producto['nombre']); ?></td>
                            <td><?php echo limpiar($producto['categoria']); ?></td>
                            <td class="precio">$<?php echo number_format($producto['precio'], 0, ',', '.'); ?></td>
                            <td><?php echo limpiar($producto['stock']); ?></td>
                            <td><?php echo limpiar($producto['descripcion']); ?></td>
                            <td><?php echo limpiar($producto['fecha_creacion']); ?></td>
                            <td class="acciones">
                                <a 
                                    class="btn btn-editar" 
                                    href="index.php?editar=<?php echo limpiar($producto['id']); ?>"
                                >
                                    Editar
                                </a>

                                <a 
                                    class="btn btn-eliminar" 
                                    href="index.php?eliminar=<?php echo limpiar($producto['id']); ?>"
                                    onclick="return confirm('¿Seguro que deseas eliminar este producto?');"
                                >
                                    Eliminar
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

    <?php endif; ?>

</div>

</body>
</html>

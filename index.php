<?php
session_start();
require 'database.php';

$mensaje = "";
$mensajeLogin = "";
$mensajeRegistro = "";

/* ===== CREAR TABLAS SI NO EXISTEN ===== */
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS usuarios (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            correo TEXT NOT NULL UNIQUE,
            password TEXT NOT NULL,
            fecha_registro TEXT NOT NULL
        )
    ");

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
    $mensaje = "Error al preparar la base de datos: " . $e->getMessage();
}

/* ===== CERRAR SESIÓN ===== */
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}

/* ===== REGISTRO DE USUARIO ===== */
if (isset($_POST['registrar_usuario'])) {
    $correo = trim($_POST['correo_registro']);
    $passwordPlano = trim($_POST['password_registro']);
    $fechaRegistro = date('Y-m-d H:i:s');

    if (empty($correo) || empty($passwordPlano)) {
        $mensajeRegistro = "Debes ingresar correo y contraseña.";
    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $mensajeRegistro = "El correo ingresado no tiene un formato válido.";
    } elseif (strlen($passwordPlano) < 4) {
        $mensajeRegistro = "La contraseña debe tener al menos 4 caracteres.";
    } else {
        try {
            $passwordHash = password_hash($passwordPlano, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("
                INSERT INTO usuarios (correo, password, fecha_registro)
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$correo, $passwordHash, $fechaRegistro]);

            $mensajeRegistro = "Usuario registrado correctamente. Ahora puedes iniciar sesión.";
        } catch (Exception $e) {
            $mensajeRegistro = "No se pudo registrar el usuario. Puede que el correo ya exista.";
        }
    }
}

/* ===== LOGIN DE USUARIO ===== */
if (isset($_POST['login_usuario'])) {
    $correo = trim($_POST['correo_login']);
    $passwordPlano = trim($_POST['password_login']);

    if (empty($correo) || empty($passwordPlano)) {
        $mensajeLogin = "Debes ingresar correo y contraseña.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE correo = ?");
        $stmt->execute([$correo]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario && password_verify($passwordPlano, $usuario['password'])) {
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_correo'] = $usuario['correo'];

            header("Location: index.php");
            exit;
        } else {
            $mensajeLogin = "Correo o contraseña incorrectos. No puedes acceder al CRUD.";
        }
    }
}

/* ===== VERIFICAR SI HAY SESIÓN INICIADA ===== */
$usuarioAutenticado = isset($_SESSION['usuario_id']);

/* ===== CREAR PRODUCTO ===== */
if ($usuarioAutenticado && isset($_POST['crear'])) {
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
if ($usuarioAutenticado && isset($_GET['eliminar'])) {
    $stmt = $pdo->prepare("DELETE FROM productos WHERE id = ?");
    $stmt->execute([$_GET['eliminar']]);

    header("Location: index.php");
    exit;
}

/* ===== LEER PRODUCTOS ===== */
$productos = [];

if ($usuarioAutenticado) {
    try {
        $productos = $pdo->query("SELECT * FROM productos ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $productos = [];
        $mensaje = "Aviso: no se pudieron cargar los productos.";
    }
}
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
            margin: 0;
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

        .descripcion {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            color: #1e3a8a;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .integrantes, .crud-info, .mockup, .login-box, .usuario-box {
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

        .mockup p {
            color: #4b5563;
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

        .btn-salir {
            display: inline-block;
            background: #dc2626;
            color: white;
            padding: 9px 15px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
        }

        .btn-salir:hover {
            background: #b91c1c;
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

        .mensaje-ok {
            background: #dcfce7;
            border: 1px solid #86efac;
            color: #166534;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 15px;
            font-weight: bold;
        }

        .login-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
        }

        @media (max-width: 800px) {
            .login-grid {
                grid-template-columns: 1fr;
            }
        }
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

    <section class="mockup">
        <h2>Mockup de la Aplicación</h2>
        <p>
            A continuación, se presenta un mockup visual de la aplicación web desarrollada,
            utilizado como referencia para representar la interfaz principal del sistema CRUD.
        </p>
        <img src="mockup.jpeg" alt="Mockup de la aplicación Tienda Tecnológica">
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

    <?php if (!$usuarioAutenticado): ?>

        <section class="login-grid">

            <div class="login-box">
                <h2>Iniciar Sesión</h2>

                <?php if ($mensajeLogin): ?>
                    <div class="mensaje-error">
                        <?= htmlspecialchars($mensajeLogin) ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <label>Correo electrónico</label>
                    <input type="email" name="correo_login" required>

                    <label>Contraseña</label>
                    <input type="password" name="password_login" required>

                    <button type="submit" name="login_usuario">Ingresar al CRUD</button>
                </form>
            </div>

            <div class="login-box">
                <h2>Registrarse</h2>

                <?php if ($mensajeRegistro): ?>
                    <div class="<?= str_contains($mensajeRegistro, 'correctamente') ? 'mensaje-ok' : 'mensaje-error' ?>">
                        <?= htmlspecialchars($mensajeRegistro) ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <label>Correo electrónico</label>
                    <input type="email" name="correo_registro" required>

                    <label>Contraseña</label>
                    <input type="password" name="password_registro" required>

                    <button type="submit" name="registrar_usuario">Crear cuenta</button>
                </form>
            </div>

        </section>

    <?php else: ?>

        <section class="usuario-box">
            <h2>Sesión iniciada</h2>
            <p>
                Usuario autenticado:
                <strong><?= htmlspecialchars($_SESSION['usuario_correo']) ?></strong>
            </p>
            <a class="btn-salir" href="index.php?logout=1">Cerrar sesión</a>
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

    <?php endif; ?>

</div>

</body>
</html>

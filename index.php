<?php
session_start();
require 'database.php';

$mensajeLogin = "";
$mensajeRegistro = "";

/* Si ya existe sesión, enviar directamente al CRUD */
if (isset($_SESSION['usuario_id'])) {
    header("Location: crud.php?accion=consultar");
    exit;
}

/* Registro de usuario */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registrar_usuario'])) {
    $correo = trim($_POST['correo_registro'] ?? '');
    $passwordPlano = trim($_POST['password_registro'] ?? '');
    $fechaRegistro = date('d/m/Y, H:i:s');

    if ($correo === '' || $passwordPlano === '') {
        $mensajeRegistro = "Debes ingresar correo y contraseña.";
    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $mensajeRegistro = "El correo ingresado no tiene un formato válido.";
    } elseif (strlen($passwordPlano) < 4) {
        $mensajeRegistro = "La contraseña debe tener al menos 4 caracteres.";
    } else {
        try {
            $stmtVerificar = $pdo->prepare("SELECT id FROM usuarios WHERE correo = ?");
            $stmtVerificar->execute([$correo]);
            $usuarioExiste = $stmtVerificar->fetch(PDO::FETCH_ASSOC);

            if ($usuarioExiste) {
                $mensajeRegistro = "Este correo ya está registrado. Intenta iniciar sesión.";
            } else {
                $passwordHash = password_hash($passwordPlano, PASSWORD_DEFAULT);

                $stmt = $pdo->prepare("
                    INSERT INTO usuarios (correo, password, fecha_registro)
                    VALUES (?, ?, ?)
                ");
                $stmt->execute([$correo, $passwordHash, $fechaRegistro]);

                registrarLog(
                    $pdo,
                    $correo,
                    "Creación de usuario",
                    "Tabla: usuarios | Usuario creado con correo: " . $correo
                );

                $mensajeRegistro = "Usuario registrado correctamente. Ahora puedes iniciar sesión.";
            }
        } catch (Exception $e) {
            $mensajeRegistro = "Error al registrar usuario: " . $e->getMessage();
        }
    }
}

/* Login de usuario */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_usuario'])) {
    $correo = trim($_POST['correo_login'] ?? '');
    $passwordPlano = trim($_POST['password_login'] ?? '');

    if ($correo === '' || $passwordPlano === '') {
        $mensajeLogin = "Debes ingresar correo y contraseña.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE correo = ?");
            $stmt->execute([$correo]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($usuario && password_verify($passwordPlano, $usuario['password'])) {
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['usuario_correo'] = $usuario['correo'];

                registrarLog(
                    $pdo,
                    $usuario['correo'],
                    "Inicio de sesión",
                    "Tabla: usuarios | ID usuario: " . $usuario['id']
                );

                header("Location: crud.php?accion=consultar");
                exit;
            } else {
                $mensajeLogin = "Correo o contraseña incorrectos. No puedes acceder al sistema.";
            }
        } catch (Exception $e) {
            $mensajeLogin = "Error al iniciar sesión: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Acceso - Tienda Tecnológica</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #eef2f7;
            padding: 30px;
            margin: 0;
        }

        .container {
            max-width: 900px;
            background: #fff;
            padding: 25px;
            margin: auto;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.12);
        }

        h1, h2 {
            color: #1f2937;
        }

        .descripcion {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            color: #1e3a8a;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 25px;
        }

        .login-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
        }

        .box {
            padding: 20px;
            border-radius: 8px;
            background: #f9fafb;
            border: 1px solid #d1d5db;
        }

        label {
            font-weight: bold;
            color: #374151;
        }

        input {
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

        button:hover {
            background: #1d4ed8;
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

        @media (max-width: 800px) {
            .login-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Acceso a Tienda Tecnológica</h1>

    <div class="descripcion">
        Para acceder al sistema CRUD de productos tecnológicos, debes iniciar sesión con un usuario registrado.
        Si aún no tienes cuenta, puedes registrarte desde esta pantalla.
    </div>

    <div class="login-grid">

        <div class="box">
            <h2>Iniciar Sesión</h2>

            <?php if ($mensajeLogin): ?>
                <div class="mensaje-error">
                    <?= htmlspecialchars($mensajeLogin) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="index.php">
                <label>Correo electrónico</label>
                <input type="email" name="correo_login" required>

                <label>Contraseña</label>
                <input type="password" name="password_login" required>

                <button type="submit" name="login_usuario" value="1">Ingresar</button>
            </form>
        </div>

        <div class="box">
            <h2>Registrarse</h2>

            <?php if ($mensajeRegistro): ?>
                <div class="<?= str_contains($mensajeRegistro, 'correctamente') ? 'mensaje-ok' : 'mensaje-error' ?>">
                    <?= htmlspecialchars($mensajeRegistro) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="index.php">
                <label>Correo electrónico</label>
                <input type="email" name="correo_registro" required>

                <label>Contraseña</label>
                <input type="password" name="password_registro" required>

                <button type="submit" name="registrar_usuario" value="1">Crear cuenta</button>
            </form>
        </div>

    </div>
</div>

</body>
</html>

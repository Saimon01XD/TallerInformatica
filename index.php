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
            max-width: 900px;
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

</div>

</body>
</html>

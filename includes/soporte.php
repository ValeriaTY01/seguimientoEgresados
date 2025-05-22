<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

include("header.php"); 
include("menu.php");

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Soporte Técnico</title>
    <style>
        /* Estilos base del sistema, adaptados de lo que me diste */
        body {
            font-family: Arial, sans-serif;
            font-size: 13px;
            margin: 0;
            padding: 0;
            color: black;
            background-color: #f9f9f9;
        }

        h2 {
            color: #003366;
            font-size: 18px;
            border-bottom: 2px solid #e46a00;
            padding-bottom: 4px;
            margin-top: 0;
        }

        .contenedor {
            max-width: 1000px;
            margin: 30px auto;
            padding: 0 20px;
            color:black;
        }

        .card {
            background-color: white;
            border: 2px solid #e46a00;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 25px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            text-align: center;
        }

        /* Botón con estilo del sistema */
        .btn-cerrar {
            padding: 10px 30px;
            background-color: #003366;
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: bold;
            font-size: 14px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-top: 20px;
            display: inline-block;
            text-decoration: none;
        }
        .btn-cerrar:hover {
            background-color: #002244;
        }
    </style>
</head>
<body>

<div class="contenedor">
    <div class="card">
        <h2>Soporte Técnico</h2>
        <p>Por el momento no está disponible.</p>
        <a href="javascript:history.back()" class="btn-cerrar">Regresar</a>
    </div>
</div>

</body>
</html>

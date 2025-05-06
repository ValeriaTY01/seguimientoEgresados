<?php
$host = 'localhost';
$usuario = 'root';
$contrasena = '';
$basedatos = 'seguimientoegresados'; // asegúrate que sea el nombre correcto

$conexion = new mysqli($host, $usuario, $contrasena, $basedatos, 3307);

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}
?>

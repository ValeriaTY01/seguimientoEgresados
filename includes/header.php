<?php
// Iniciar sesión si no está ya iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Obtener rol y normalizarlo
$rol = strtolower($_SESSION['rol'] ?? 'egresado');

// Asignar nombre del portal según rol
$nombrePortal = match ($rol) {
    'jefe departamento' => 'Portal de Jefe de Departamento',
    'jefe vinculación'  => 'Portal de Jefe de Vinculación',
    'administrador'     => 'Portal de Administrador',
    'dba'               => 'Portal de DBA',
    default             => 'Portal de Egresados'
};
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= $nombrePortal ?></title>
    <base href="/seguimientoEgresados/">
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="encabezado">
        <div class="logo">
            <img src="img/logo-sep-scaled.jpeg" alt="Logo SEP">
        </div>
        <div class="titulo">
            <div class="linea1"><span class="amarillo">Sistema Integral de Información</span></div>
            <div class="linea2">Instituto Tecnológico de Veracruz</div>
            <div class="linea3"><?= $nombrePortal ?></div>
        </div>

<?php
session_start();
require 'db/conexion.php';

$tipo_usuario = $_POST['tipo_usuario'] ?? 'alumno'; // "alumno" o "personal"
$curp = strtoupper(trim($_POST['curp'] ?? '')); // Para egresados
$rfc = strtoupper(trim($_POST['rfc'] ?? '')); // Para personal
$contrasena = $_POST['contrasena'] ?? '';

if ($tipo_usuario === 'alumno') {
    // Validación de egresado
    $query = "SELECT * FROM EGRESADO WHERE CURP = ? LIMIT 1";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("s", $curp);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 1) {
        $egresado = $resultado->fetch_assoc();

        // Verificar si está verificado
        if (!$egresado['VERIFICADO']) {
            header("Location: login.php?error=2"); // No verificado
            exit;
        }

        // Verificar contraseña (aquí sí debe ser password_verify)
        if (password_verify($contrasena, $egresado['CONTRASENA'])) {
            $_SESSION['nombre'] = $egresado['NOMBRE'];
            $_SESSION['curp'] = $egresado['CURP'];
            $_SESSION['rol'] = 'egresado';
            header("Location: index.php");
            exit;
        } else {
            header("Location: login.php?error=1"); // Contraseña incorrecta
            exit;
        }
    } else {
        header("Location: login.php?error=1"); // CURP no encontrado
        exit;
    }
} else {
    // Validación de personal administrativo
    $query = "SELECT * FROM USUARIO WHERE RFC = ? LIMIT 1";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("s", $rfc);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 1) {
        $usuario = $resultado->fetch_assoc();

        // Aquí SOLO comparamos directamente
        if ($contrasena === $usuario['CONTRASENA']) {
            $_SESSION['nombre'] = $usuario['NOMBRE'];
            $_SESSION['rfc'] = $usuario['RFC'];
            $_SESSION['rol'] = $usuario['ROL']; // Asignar el rol correspondiente
            header("Location: index.php");
            exit;
        } else {
            header("Location: login.php?error=1"); // Contraseña incorrecta
            exit;
        }
    } else {
        header("Location: login.php?error=1"); // RFC no encontrado
        exit;
    }
}
?>

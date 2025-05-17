<?php
session_start();
require 'db/conexion.php';

$tipo_usuario = $_POST['tipo_usuario'] ?? 'alumno';
$curp = strtoupper(trim($_POST['curp'] ?? ''));
$rfc = strtoupper(trim($_POST['rfc'] ?? ''));
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

        if (!$egresado['VERIFICADO']) {
            header("Location: login.php?error=2");
            exit;
        }

        if (password_verify($contrasena, $egresado['CONTRASENA'])) {
            $_SESSION['nombre'] = $egresado['NOMBRE'] . ' ' . $egresado['APELLIDO_PATERNO'] . ' ' . $egresado['APELLIDO_MATERNO'];
            $_SESSION['curp'] = $egresado['CURP'];
            $_SESSION['rol'] = 'egresado';

            header("Location: index.php");
            exit;
        } else {
            header("Location: login.php?error=1");
            exit;
        }
    } else {
        header("Location: login.php?error=1");
        exit;
    }

} else {
    // Validación de personal
    $query = "SELECT * FROM USUARIO WHERE RFC = ? LIMIT 1";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("s", $rfc);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 1) {
        $usuario = $resultado->fetch_assoc();

        if ($contrasena === $usuario['CONTRASENA']) {
            $_SESSION['nombre'] = $usuario['NOMBRE'] . ' ' . $usuario['APELLIDO_PATERNO'] . ' ' . $usuario['APELLIDO_MATERNO'];
            $_SESSION['rfc'] = $usuario['RFC'];
            $_SESSION['rol'] = strtolower($usuario['ROL']);

            if ($_SESSION['rol'] === 'jefe departamento') {
                $_SESSION['carrera'] = $usuario['CARRERA'];
            }

            header("Location: index.php");
            exit;
        } else {
            header("Location: login.php?error=1");
            exit;
        }
    } else {
        header("Location: login.php?error=1");
        exit;
    }
}
?>

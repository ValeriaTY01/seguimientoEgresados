<?php
session_start();

if (!isset($_SESSION['rol'])) {
    header("Location: login.php?error=1");
    exit;
}

// Normalizamos el rol
$rol = strtolower($_SESSION['rol']);

// Redirección por rol
switch ($rol) {
    case 'egresado':
        include 'includes/header.php';
        include 'includes/menu.php';
        ?>
        <div class="contenido-principal">
            <div class="botones-flotantes">
                <button id="btn-encuesta" class="boton-amarillo">Encuesta para Egresados</button>
                <button class="boton-blanco">Reiniciar contraseña.</button>
            </div>
        </div>
        <script src="js/script.js"></script>
        <?php include 'includes/footer.php';
        break;

    case 'jefe departamento':
        header("Location: jefe/index_jefe.php");
        exit;

    case 'administrador':
        header("Location: admin/index_admin.php");
        exit;

    case 'jefe vinculación':
        header("Location: vinculacion/index_vinculacion.php");
        exit;

    case 'dba':
        header("Location: dba/index_dba.php");
        exit;

    default:
        header("Location: login.php?error=2");
        exit;
}
?>



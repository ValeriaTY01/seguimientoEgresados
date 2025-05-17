<?php
session_start();

if (!isset($_SESSION['rol'])) {
    header("Location: login.php?error=1");
    exit;
}

$rol = strtolower($_SESSION['rol']);

// Mapeo de rutas por rol
$destinos = [
    'egresado'          => null, // Se queda en este archivo
    'jefe departamento' => 'jefe/index_jefe.php',
    'administrador'     => 'admin/index_admin.php',
    'jefe vinculación'  => 'vinculacion/index_vinculacion.php',
    'dba'               => 'dba/index_dba.php'
];

// Redirige si el rol tiene una ruta asociada
if (isset($destinos[$rol]) && $destinos[$rol] !== null) {
    header("Location: " . $destinos[$rol]);
    exit;
}

// Rol 'egresado' o desconocido: mostrar bienvenida
if ($rol === 'egresado') {
    include 'includes/header.php';
    include 'includes/menu.php';
    ?>
    <div class="contenido-principal">
        <div class="bienvenida">
            <h2>BIENVENIDO(A)</h2>
            <p><?= htmlspecialchars($_SESSION['curp']) ?></p>
            <p><?= htmlspecialchars($_SESSION['nombre']) ?></p>
        </div>
        <div class="botones-flotantes">
            <button id="btn-encuesta" class="boton-amarillo">Encuesta para Egresados</button>
            <button class="boton-blanco">Reiniciar contraseña</button>
        </div>
    </div>
    <script src="js/script.js"></script>
    <?php
    include 'includes/footer.php';
} else {
    header("Location: login.php?error=2");
    exit;
}
?>

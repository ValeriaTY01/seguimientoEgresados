<?php
// Asegúrate de incluir esto al principio del archivo si aún no está
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
require_once __DIR__ . '/../db/conexion.php';

// Rol actual
$rolActual = $_SESSION['rol'] ?? '';

// Cargar últimos 3 avisos dirigidos a este rol
$avisos = [];
if ($rolActual) {
    $rolEscape = $conexion->real_escape_string($rolActual);
    $query = "
        SELECT CONTENIDO, FECHA
        FROM AVISOS
        WHERE FIND_IN_SET('$rolEscape', DESTINATARIOS)
        ORDER BY FECHA DESC
        LIMIT 3
    ";
    $res = $conexion->query($query);
    while ($fila = $res->fetch_assoc()) {
        $avisos[] = $fila;
    }
}
?>

<div class="flotantes-inferiores">
    <?php include 'avisos_footer.php'; ?>

    <?php if (isset($_SESSION['rol']) && strtolower($_SESSION['rol']) === 'egresado'): ?>
        <div class="quejas">
            <div class="titulo-quejas">QUEJAS Y SUGERENCIAS</div>
        </div>

        <div class="texto-quejas">
            Recuerda que la sección de <strong>Soporte Técnico</strong> se encuentra en el menú de Información Escolar.<br>
            Es anónimo y solo la autoridad correspondiente lo leerá.
        </div>
    <?php endif; ?>

</div>

<div class="logo-centro">
    <img src="img/logo_veracruz.png" alt="Logo grande centro">
</div>
</div> <!-- cierre de .contenido-principal -->
</body>
</html>

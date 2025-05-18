<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
require_once __DIR__ . '/../db/conexion.php';

$rolActual = $_SESSION['rol'] ?? '';
$avisos = [];

if ($rolActual) {
    $ahora = date('Y-m-d H:i:s');
    
    // Usamos consulta preparada para seguridad y filtramos por FECHA_PROGRAMADA <= ahora
    $sql = "
        SELECT CONTENIDO, FECHA_PROGRAMADA
        FROM AVISOS
        WHERE FIND_IN_SET(?, DESTINATARIOS)
          AND FECHA_PROGRAMADA <= ?
        ORDER BY FECHA_PROGRAMADA DESC
        LIMIT 3
    ";
    $stmt = $conexion->prepare($sql);
    if ($stmt) {
        $stmt->bind_param('ss', $rolActual, $ahora);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($fila = $result->fetch_assoc()) {
            $avisos[] = $fila;
        }
        $stmt->close();
    }
}
?>

<div class="anuncios">
    <div class="titulo-anuncios">ANUNCIOS</div>
    <div class="texto-anuncio">
        <?php if (!empty($avisos)): ?>
            <ul class="lista-avisos">
                <?php foreach ($avisos as $a): ?>
                    <li>
                        <?= htmlspecialchars($a['CONTENIDO']) ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="sin-avisos">No hay anuncios recientes.</p>
        <?php endif; ?>
    </div>
</div>

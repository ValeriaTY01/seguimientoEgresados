<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once('../db/conexion.php');

$id = $_GET['id'] ?? null;
if (!$id) {
    echo "<p class='error'>ID de cuestionario no proporcionado.</p>";
    exit;
}

// Obtener CURP del cuestionario
$stmtQ = $conexion->prepare("SELECT CURP FROM CUESTIONARIO_RESPUESTA WHERE ID_CUESTIONARIO = ?");
$stmtQ->bind_param("i", $id);
$stmtQ->execute();
$resultQ = $stmtQ->get_result();
$cuest = $resultQ->fetch_assoc();

if (!$cuest) {
    echo "<p class='error'>Cuestionario no encontrado.</p>";
    exit;
}

$curp = $cuest['CURP'];
$rol = $_SESSION['rol'] ?? '';
$carreraUsuario = $_SESSION['carrera'] ?? '';

// Validar carrera si es jefe de departamento
$stmtE = $conexion->prepare("SELECT CARRERA FROM EGRESADO WHERE CURP = ?");
$stmtE->bind_param("s", $curp);
$stmtE->execute();
$resultE = $stmtE->get_result();
$egresado = $resultE->fetch_assoc();

if ($rol === 'Jefe Departamento' && $egresado['CARRERA'] !== $carreraUsuario) {
    echo "<p class='error'>No tienes permiso para ver este cuestionario.</p>";
    exit;
}

// Obtener respuestas agrupadas por sección
$sql = "SELECT S.NOMBRE AS SECCION, P.TEXTO AS PREGUNTA, P.TIPO, R.RESPUESTA_TEXTO, O.TEXTO AS RESPUESTA_OPCION
        FROM RESPUESTA R
        JOIN PREGUNTA P ON R.ID_PREGUNTA = P.ID_PREGUNTA
        JOIN SECCION S ON P.ID_SECCION = S.ID_SECCION
        LEFT JOIN OPCION_RESPUESTA O ON R.ID_OPCION = O.ID_OPCION
        WHERE R.ID_CUESTIONARIO = ?
        ORDER BY S.ORDEN, P.ORDEN";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

$secciones = [];
while ($r = $result->fetch_assoc()) {
    $secciones[$r['SECCION']][] = $r;
}
?>

<div class="respuesta-container">
    <h2>Respuestas del cuestionario</h2>

    <?php foreach ($secciones as $nombreSeccion => $preguntas): ?>
        <div class="seccion">
            <h3><?= htmlspecialchars($nombreSeccion) ?></h3>
            <ul class="preguntas">
                <?php foreach ($preguntas as $p): ?>
                    <li>
                        <strong><?= htmlspecialchars($p['PREGUNTA']) ?></strong>
                        <span>
                            <?= $p['TIPO'] === 'texto'
                                ? nl2br(htmlspecialchars($p['RESPUESTA_TEXTO']))
                                : htmlspecialchars($p['RESPUESTA_OPCION']) ?>
                        </span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endforeach; ?>
</div>

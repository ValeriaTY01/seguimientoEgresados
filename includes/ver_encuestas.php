<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once('../db/conexion.php');

$curp = $_GET['curp'] ?? null;

if (!$curp) {
    echo "<p style='color:red;'>CURP no proporcionado.</p>";
    exit;
}

$rol = $_SESSION['rol'] ?? '';
$carreraUsuario = $_SESSION['carrera'] ?? '';

// Obtener datos del egresado
$stmtE = $conexion->prepare("SELECT NOMBRE, APELLIDO_PATERNO, APELLIDO_MATERNO, CARRERA FROM EGRESADO WHERE CURP = ?");
$stmtE->bind_param("s", $curp);
$stmtE->execute();
$resultE = $stmtE->get_result();
$egresado = $resultE->fetch_assoc();

if (!$egresado) {
    echo "<p style='color:red;'>Egresado no encontrado.</p>";
    exit;
}

if ($rol === 'Jefe Departamento' && $egresado['CARRERA'] !== $carreraUsuario) {
    echo "<p style='color:red;'>No tienes permiso para ver este egresado.</p>";
    exit;
}

// Obtener encuestas contestadas
$sql = "SELECT CR.ID_CUESTIONARIO, CR.TIPO, CR.FECHA_APLICACION, E.RAZON_SOCIAL, CR.COMPLETO
        FROM CUESTIONARIO_RESPUESTA CR
        LEFT JOIN EMPRESA E ON CR.ID_EMPRESA = E.ID_EMPRESA
        WHERE CR.CURP = ?
        ORDER BY CR.FECHA_APLICACION DESC";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("s", $curp);
$stmt->execute();
$result = $stmt->get_result();

$encuestas = [];
while ($row = $result->fetch_assoc()) {
    $encuestas[] = $row;
}
?>

<h3 style="margin-top: 0;">
    Encuestas respondidas por <?= htmlspecialchars($egresado['NOMBRE'] . ' ' . $egresado['APELLIDO_PATERNO']) ?>
</h3>

<?php if (count($encuestas) > 0): ?>
    <table border="1" cellpadding="6" cellspacing="0" style="width:100%; border-collapse: collapse;">
        <thead style="background-color: #f0f0f0;">
            <tr>
                <th>Tipo</th>
                <th>Empresa</th>
                <th>Fecha</th>
                <th>Estado</th>
                <th>Ver respuestas</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($encuestas as $enc): ?>
            <tr>
                <td><?= htmlspecialchars($enc['TIPO']) ?></td>
                <td><?= htmlspecialchars($enc['RAZON_SOCIAL']) ?></td>
                <td><?= htmlspecialchars($enc['FECHA_APLICACION']) ?></td>
                <td><?= $enc['COMPLETO'] ? 'Completa' : 'Incompleta' ?></td>
                <td>
                    <a href="javascript:void(0);" onclick="verRespuestas(<?= $enc['ID_CUESTIONARIO'] ?>)">Ver</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>Este egresado no ha contestado ninguna encuesta.</p>
<?php endif; ?>
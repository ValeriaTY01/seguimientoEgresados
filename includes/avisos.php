<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
require('../db/conexion.php');
include('header.php');
include('menu.php');

$rol = $_SESSION['rol'] ?? '';
$rfc = $_SESSION['rfc'] ?? '';
$puedePublicar = in_array($rol, ['administrador', 'jefe vinculación']);
$mensaje = '';

// Aquí empieza el fragmento nuevo para crear avisos automáticos

$hoy = date('Y-m-d');

$sql = "SELECT ID_PERIODO, NOMBRE, FECHA_INICIO, FECHA_FIN 
        FROM PERIODO_ENCUESTA
        WHERE ACTIVO = 1 AND (
            (FECHA_INICIO <= ? AND FECHA_FIN >= ?)
            OR (FECHA_FIN BETWEEN ? AND DATE_ADD(?, INTERVAL 3 DAY))
        )";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("ssss", $hoy, $hoy, $hoy, $hoy);
$stmt->execute();
$result = $stmt->get_result();

while ($periodo = $result->fetch_assoc()) {
    $sqlCheck = "SELECT COUNT(*) AS cnt FROM AVISOS WHERE ES_AUTOMATICO = 1 AND ID_PERIODO = ?";
    $stmtCheck = $conexion->prepare($sqlCheck);
    $stmtCheck->bind_param("i", $periodo['ID_PERIODO']);
    $stmtCheck->execute();
    $resCheck = $stmtCheck->get_result()->fetch_assoc();

    if ($resCheck['cnt'] == 0) {
        $contenido = "";
        if ($periodo['FECHA_INICIO'] <= $hoy && $periodo['FECHA_FIN'] >= $hoy) {
            $contenido = "¡Nuevo periodo de encuesta abierto: {$periodo['NOMBRE']}! Por favor, completa tu encuesta lo antes posible.";
        } else {
            $contenido = "El periodo de encuesta '{$periodo['NOMBRE']}' está por cerrar pronto. No olvides completar tu encuesta.";
        }
        $destinatarios = "Egresado";
        $fechaActual = date('Y-m-d H:i:s');
        $esAutomatico = 1;
        $idPeriodo = $periodo['ID_PERIODO'];

        // IMPORTANTE: RFC_AUTOR es NULL para avisos automáticos
        $insertSql = "INSERT INTO AVISOS (CONTENIDO, RFC_AUTOR, DESTINATARIOS, FECHA_PROGRAMADA, ES_AUTOMATICO, ID_PERIODO) 
                      VALUES (?, NULL, ?, ?, ?, ?)";
        $stmtInsert = $conexion->prepare($insertSql);
        $stmtInsert->bind_param("sssii", $contenido, $destinatarios, $fechaActual, $esAutomatico, $idPeriodo);
        $stmtInsert->execute();
    }
}

// ELIMINAR AVISO
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar']) && $puedePublicar) {
    $idAviso = intval($_POST['id_aviso']);
    $stmt = $conexion->prepare("DELETE FROM AVISOS WHERE ID_AVISO = ? AND RFC_AUTOR = ?");
    $stmt->bind_param("is", $idAviso, $rfc);
    $stmt->execute();
    $mensaje = "🗑️ Aviso eliminado correctamente.";
}

// ACTUALIZAR AVISO
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar']) && $puedePublicar) {
    $idAviso = intval($_POST['id_aviso']);
    $contenido = trim($_POST['contenido']);
    $fechaProgramada = $_POST['fecha_programada'] ?: date('Y-m-d H:i:s');
    $destinatarios = $_POST['destinatarios'] ?? [];
    $destinatarioStr = implode(',', $destinatarios);

    $stmt = $conexion->prepare("UPDATE AVISOS SET CONTENIDO = ?, FECHA_PROGRAMADA = ?, DESTINATARIOS = ? WHERE ID_AVISO = ? AND RFC_AUTOR = ?");
    $stmt->bind_param("sssis", $contenido, $fechaProgramada, $destinatarioStr, $idAviso, $rfc);
    $stmt->execute();
    $mensaje = "✏️ Aviso actualizado correctamente.";
}

// NUEVO AVISO
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nuevo']) && $puedePublicar) {
    $contenido = trim($_POST['contenido']);
    $fechaProgramada = $_POST['fecha_programada'] ?: date('Y-m-d H:i:s');
    $destinatarios = $_POST['destinatarios'] ?? [];

    if ($contenido && $destinatarios) {
        $destinatarioStr = implode(',', $destinatarios);
        $stmt = $conexion->prepare("INSERT INTO AVISOS (CONTENIDO, RFC_AUTOR, DESTINATARIOS, FECHA_PROGRAMADA) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $contenido, $rfc, $destinatarioStr, $fechaProgramada);
        $stmt->execute();
        $mensaje = "📢 Aviso programado correctamente.";
    } else {
        $mensaje = "⚠️ Faltan campos obligatorios.";
    }
}

// CONSULTA AVISOS
$rolEscapado = $conexion->real_escape_string($rol);
$ahora = date('Y-m-d H:i:s');

// Avisos visibles
$consulta = $conexion->prepare("
    SELECT A.ID_AVISO, A.CONTENIDO, A.FECHA, A.FECHA_PROGRAMADA, A.ES_AUTOMATICO, A.DESTINATARIOS,
           U.NOMBRE AS AUTOR, PE.NOMBRE AS PERIODO
    FROM AVISOS A
    LEFT JOIN USUARIO U ON A.RFC_AUTOR = U.RFC
    LEFT JOIN PERIODO_ENCUESTA PE ON A.ID_PERIODO = PE.ID_PERIODO
    WHERE FIND_IN_SET(?, A.DESTINATARIOS) AND A.FECHA_PROGRAMADA <= ?
    ORDER BY A.FECHA_PROGRAMADA DESC
");
$consulta->bind_param("ss", $rolEscapado, $ahora);
$consulta->execute();
$resultado = $consulta->get_result();

// Avisos futuros editables por el autor
$consultaPendientes = null;
if ($puedePublicar) {
    $consultaPendientes = $conexion->prepare("
        SELECT ID_AVISO, CONTENIDO, FECHA_PROGRAMADA, DESTINATARIOS
        FROM AVISOS
        WHERE RFC_AUTOR = ? AND FECHA_PROGRAMADA > ?
        ORDER BY FECHA_PROGRAMADA ASC
    ");
    $consultaPendientes->bind_param("ss", $rfc, $ahora);
    $consultaPendientes->execute();
    $pendientes = $consultaPendientes->get_result();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Avisos</title>
    <link rel="stylesheet" href="css/aviso.css">
</head>
<body>
<h2>Panel de Avisos</h2>

<?php if (!empty($mensaje)): ?>
    <div class="mensaje-aviso"><?= htmlspecialchars($mensaje) ?></div>
<?php endif; ?>

<?php if ($puedePublicar): ?>
    <section class="crear-aviso">
        <h3>Nuevo Aviso</h3>
        <form method="POST">
            <textarea name="contenido" required placeholder="Escribe tu aviso aquí..." rows="3"></textarea><br>
            <label>Fecha de publicación:
                <input type="datetime-local" name="fecha_programada">
            </label>
            <br>
            <strong>Destinatarios:</strong><br>
            <?php
            $roles = ['Administrador', 'Jefe Vinculación', 'Jefe Departamento', 'Egresado'];
            foreach ($roles as $r) {
                echo "<label><input type='checkbox' name='destinatarios[]' value='$r' checked> $r</label> ";
            }
            ?>
            <br><br>
            <button type="submit" name="nuevo">📢 Publicar Aviso</button>
        </form>
    </section>

    <?php if (isset($pendientes) && $pendientes->num_rows > 0): ?>
        <hr>
        <h3>Mis avisos programados</h3>
        <?php while ($av = $pendientes->fetch_assoc()): ?>
            <form method="POST" style="border:1px dashed #aaa; margin:10px; padding:10px;">
                <input type="hidden" name="id_aviso" value="<?= $av['ID_AVISO'] ?>">
                <textarea name="contenido" rows="3"><?= htmlspecialchars($av['CONTENIDO']) ?></textarea><br>
                <input type="datetime-local" name="fecha_programada" value="<?= date('Y-m-d\TH:i', strtotime($av['FECHA_PROGRAMADA'])) ?>"><br>
                <strong>Destinatarios:</strong><br>
                <?php
                foreach ($roles as $r) {
                    $checked = in_array($r, explode(',', $av['DESTINATARIOS'])) ? 'checked' : '';
                    echo "<label><input type='checkbox' name='destinatarios[]' value='$r' $checked> $r</label> ";
                }
                ?>
                <br>
                <button type="submit" name="editar">✏️ Guardar</button>
                <button type="submit" name="eliminar" onclick="return confirm('¿Eliminar este aviso?')">🗑️ Eliminar</button>
            </form>
        <?php endwhile; ?>
    <?php endif; ?>
<?php endif; ?>

<hr>
<h3>Avisos Recientes</h3>
<?php while ($aviso = $resultado->fetch_assoc()): ?>
    <div class="aviso">
        <div><?= nl2br(htmlspecialchars($aviso['CONTENIDO'])) ?></div>
        <div class="meta">
            <?= date('d/m/Y H:i', strtotime($aviso['FECHA_PROGRAMADA'])) ?>
            <?php if ($aviso['AUTOR']): ?>
                | Publicado por <?= htmlspecialchars($aviso['AUTOR']) ?>
            <?php endif; ?>
            <?php if ($aviso['ES_AUTOMATICO']): ?>
                | <em>Aviso automático</em>
            <?php endif; ?>
            <?php if ($aviso['PERIODO']): ?>
                | Periodo: <?= htmlspecialchars($aviso['PERIODO']) ?>
            <?php endif; ?>
        </div>
    </div>
<?php endwhile; ?>
</body>
</html>

<?php
session_start();
require 'db/conexion.php';

if (!isset($_SESSION['tipo_encuesta']) || !isset($_SESSION['id_egresado'])) {
    die("Información insuficiente. Por favor vuelve al inicio.");
}

if (!isset($_SESSION['ya_respondio']) || !isset($_SESSION['id_periodo_actual'])) {
    header("Location: encuestas.php");
    exit;
}

if ($_SESSION['ya_respondio'] === true && $_SESSION['id_periodo_respondido'] == $_SESSION['id_periodo_actual']) {
    echo "<h2>Ya has respondido el cuestionario en el período actual.</h2>";
    exit;
}

$id_egresado = $_SESSION['id_egresado'];
$tipo_encuesta = strtolower(trim($_SESSION['tipo_encuesta']));
$id_periodo_actual = $_SESSION['id_periodo_actual'] ?? null;

if (in_array($tipo_encuesta, ['ingeniería química', 'ingeniería bioquímica'])) {
    $tipo_encuesta = 'quimica';
}

$consultaRespuestas = "
    SELECT r.ID_PREGUNTA, r.ID_OPCION, r.RESPUESTA_TEXTO 
    FROM RESPUESTA r
    INNER JOIN CUESTIONARIO_RESPUESTA c ON r.ID_CUESTIONARIO = c.ID_CUESTIONARIO
    WHERE c.CURP = ? AND c.ID_PERIODO = ?
";
$stmtPrevias = $conexion->prepare($consultaRespuestas);
$stmtPrevias->bind_param("si", $id_egresado, $id_periodo_actual);
$stmtPrevias->execute();
$resultadoPrevio = $stmtPrevias->get_result();

$respuestas_previas = [];
while ($row = $resultadoPrevio->fetch_assoc()) {
    $pid = $row['ID_PREGUNTA'];
    if (!isset($respuestas_previas[$pid])) {
        $respuestas_previas[$pid] = [];
    }

    if ($row['ID_OPCION']) {
        $respuestas_previas[$pid][] = $row['ID_OPCION'];
    } elseif ($row['RESPUESTA_TEXTO']) {
        $respuestas_previas[$pid] = $row['RESPUESTA_TEXTO'];
    }
}

$query = "
    SELECT 
        s.ID_SECCION AS seccion_id, s.NOMBRE AS seccion_nombre, s.ORDEN AS seccion_orden,
        p.ID_PREGUNTA AS pregunta_id, p.TEXTO AS pregunta_texto, p.TIPO AS pregunta_tipo, p.OBLIGATORIA, p.ORDEN AS pregunta_orden,
        o.ID_OPCION AS opcion_id, o.TEXTO AS opcion_texto
    FROM SECCION s
    INNER JOIN PREGUNTA p ON p.ID_SECCION = s.ID_SECCION
    LEFT JOIN OPCION_RESPUESTA o ON o.ID_PREGUNTA = p.ID_PREGUNTA
    WHERE " . ($tipo_encuesta === 'quimica' ? "LOWER(s.PARA_CARRERA) = 'quimica'" : "s.PARA_CARRERA IS NULL") . "
    ORDER BY s.ORDEN, p.ORDEN, o.ID_OPCION
";

$stmt = $conexion->prepare($query);
$stmt->execute();
$resultado = $stmt->get_result();

$secciones = [];

while ($row = $resultado->fetch_assoc()) {
    $sid = $row['seccion_id'];
    $pid = $row['pregunta_id'];

    if (!isset($secciones[$sid])) {
        $secciones[$sid] = [
            'nombre' => $row['seccion_nombre'],
            'preguntas' => []
        ];
    }

    if (!isset($secciones[$sid]['preguntas'][$pid])) {
        $secciones[$sid]['preguntas'][$pid] = [
            'texto' => $row['pregunta_texto'],
            'tipo' => $row['pregunta_tipo'],
            'obligatoria' => $row['OBLIGATORIA'],
            'opciones' => []
        ];
    }

    if ($row['opcion_id'] !== null) {
        $secciones[$sid]['preguntas'][$pid]['opciones'][] = [
            'id' => $row['opcion_id'],
            'texto' => $row['opcion_texto']
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cuestionario</title>
    <base href="/seguimientoEgresados/">
    <link rel="stylesheet" href="css/cuestionario.css">
</head>
<body>

<?php include('includes/header.php'); ?>
<?php include('includes/menu.php'); ?>

<h1 style="color: #004B82;">Encuesta de Seguimiento para Egresados</h1>

<div id="barraProgreso"><div id="progreso"></div></div>

<form method="POST" id="formularioCuestionario">
    <?php $index = 0; foreach ($secciones as $sid => $seccion): ?>
        <div class="seccion" data-index="<?= $index ?>" style="<?= $index === 0 ? '' : 'display:none;' ?>">
            <h2><?= htmlspecialchars($seccion['nombre']) ?></h2>
            <?php foreach ($seccion['preguntas'] as $pid => $pregunta): ?>
                <div class="pregunta" data-tipo="<?= $pregunta['tipo'] ?>">
                    <p><strong><?= htmlspecialchars($pregunta['texto']) ?></strong></p>
                    <?php
                    $inputName = "respuesta[$pid]";
                    $required = $pregunta['obligatoria'] ? 'required' : '';
                    $respuestasUsuario = $respuestas_previas[$pid] ?? null;

                    switch ($pregunta['tipo']) {
                        case 'opcion':
                        case 'boolean':
                        case 'escala':
                            echo "<div class='pregunta-opciones'>";
                            foreach ($pregunta['opciones'] as $i => $opcion) {
                                $opId = "op_{$pid}_{$i}";
                                $checked = is_array($respuestasUsuario) && in_array($opcion['id'], $respuestasUsuario) ? 'checked' : '';
                                echo "<input type='radio' id='{$opId}' name='{$inputName}' value='{$opcion['id']}' $required $checked>";
                                echo "<label for='{$opId}'>" . htmlspecialchars($opcion['texto']) . "</label>";
                            }
                            echo "</div>";
                            break;
                        case 'multiple':
                            echo "<div class='pregunta-opciones'>";
                            foreach ($pregunta['opciones'] as $i => $opcion) {
                                $opId = "chk_{$pid}_{$i}";
                                $checked = is_array($respuestasUsuario) && in_array($opcion['id'], $respuestasUsuario) ? 'checked' : '';
                                echo "<input type='checkbox' id='{$opId}' name='{$inputName}[]' value='{$opcion['id']}' $checked>";
                                echo "<label for='{$opId}'>" . htmlspecialchars($opcion['texto']) . "</label>";
                            }
                            echo "</div>";
                            break;
                        case 'texto':
                            $value = is_string($respuestasUsuario) ? htmlspecialchars($respuestasUsuario) : '';
                            echo "<textarea name='{$inputName}' rows='2' cols='40' $required>$value</textarea>";
                            break;
                    }
                    ?>
                </div>

                <?php if ((int)$pid === 26): ?>
                    <fieldset class="empresa">
                        <legend>Datos de la Empresa</legend>
                        <div class="grid-empresa">
                            <label for="tipo_organismo">Tipo de organismo:</label>
                            <select name="empresa[tipo_organismo]" id="tipo_organismo">
                                <option value="">Seleccione...</option>
                                <option value="Público">Público</option>
                                <option value="Privado">Privado</option>
                                <option value="Social">Social</option>
                            </select>
                            <label for="giro">Giro:</label>
                            <textarea name="empresa[giro]" id="giro"></textarea>
                            <label for="razon_social">Razón social:</label>
                            <input type="text" name="empresa[razon_social]" id="razon_social">
                            <div class="doble-campo">
                                <div>
                                    <label for="calle">Calle:</label>
                                    <input type="text" name="empresa[calle]" id="calle">
                                </div>
                                <div>
                                    <label for="numero">Número:</label>
                                    <input type="text" name="empresa[numero]" id="numero">
                                </div>
                            </div>
                            <div class="doble-campo">
                                <div>
                                    <label for="colonia">Colonia:</label>
                                    <input type="text" name="empresa[colonia]" id="colonia">
                                </div>
                                <div>
                                    <label for="codigo_postal">Código postal:</label>
                                    <input type="text" name="empresa[codigo_postal]" id="codigo_postal">
                                </div>
                            </div>
                            <div class="doble-campo">
                                <div>
                                    <label for="ciudad">Ciudad:</label>
                                    <input type="text" name="empresa[ciudad]" id="ciudad">
                                </div>
                                <div>
                                    <label for="municipio">Municipio:</label>
                                    <input type="text" name="empresa[municipio]" id="municipio">
                                </div>
                            </div>
                            <label for="estado">Estado:</label>
                            <input type="text" name="empresa[estado]" id="estado">
                            <div class="doble-campo">
                                <div>
                                    <label for="telefono">Teléfono:</label>
                                    <input type="text" name="empresa[telefono]" id="telefono">
                                </div>
                                <div>
                                    <label for="email">Email:</label>
                                    <input type="email" name="empresa[email]" id="email">
                                </div>
                            </div>
                            <label for="pagina_web">Página web:</label>
                            <input type="text" name="empresa[pagina_web]" id="pagina_web">
                            <div class="doble-campo">
                                <div>
                                    <label for="jefe_nombre">Nombre del jefe inmediato:</label>
                                    <input type="text" name="empresa[jefe_nombre]" id="jefe_nombre">
                                </div>
                                <div>
                                    <label for="jefe_puesto">Puesto del jefe inmediato:</label>
                                    <input type="text" name="empresa[jefe_puesto]" id="jefe_puesto">
                                </div>
                            </div>
                        </div>
                    </fieldset>
                <?php endif; ?>

            <?php endforeach; ?>
        </div>
        <?php $index++; ?>
    <?php endforeach; ?>

    <div class="navegacion">
        <button type="button" id="anterior">Anterior</button>
        <button type="button" id="siguiente">Siguiente</button>
        <button type="submit" id="enviar" style="display:none;">Enviar respuestas</button>
    </div>
</form>

<div id="modalExito" style="display: none;">
  <div class="modal-content">
    <h2>¡Encuesta completada con éxito!</h2>
    <p>Gracias por tu participación. Tus respuestas han sido registradas correctamente.</p>
    <button id="volverInicio">Volver al inicio</button>
  </div>
</div>

<script>
    const RESPUESTAS_PREVIAS = <?= json_encode($respuestas_previas) ?>;
</script>
<script src="js/cuestionario.js"></script>
</body>
</html>

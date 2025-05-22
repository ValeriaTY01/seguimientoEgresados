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

$id_egresado = $_SESSION['id_egresado'];
$tipo_encuesta = strtolower(trim($_SESSION['tipo_encuesta']));
$id_periodo_actual = $_SESSION['id_periodo_actual'] ?? null;

if (in_array($tipo_encuesta, ['ingeniería química', 'ingeniería bioquímica'])) {
    $tipo_encuesta = 'quimica';
}

// Si ya respondió en el periodo actual, no puede volver a responder
if ($_SESSION['ya_respondio'] === true && $_SESSION['id_periodo_respondido'] == $id_periodo_actual) {
    echo "<h2>Ya has respondido el cuestionario en el período actual.</h2>";
    exit;
}

// 1) Obtener el último periodo en el que respondió el egresado
$queryUltimoPeriodo = "
    SELECT MAX(c.ID_PERIODO) AS ultimo_periodo
    FROM CUESTIONARIO_RESPUESTA c
    WHERE c.CURP = ?
";
$stmtUltimoPeriodo = $conexion->prepare($queryUltimoPeriodo);
$stmtUltimoPeriodo->bind_param("s", $id_egresado);
$stmtUltimoPeriodo->execute();
$resultPeriodo = $stmtUltimoPeriodo->get_result();
$ultimo_periodo = null;
if ($row = $resultPeriodo->fetch_assoc()) {
    $ultimo_periodo = $row['ultimo_periodo'];
}
$stmtUltimoPeriodo->close();

if (!$ultimo_periodo) {
    // No tiene respuestas previas en ningún periodo, se carga el formulario en blanco
    $ultimo_periodo = 0; // o null, pero 0 para evitar errores
}

// 2) Cargar respuestas previas del último periodo en que respondió
$consultaRespuestas = "
    SELECT r.ID_PREGUNTA, r.ID_OPCION, r.RESPUESTA_TEXTO 
    FROM RESPUESTA r
    INNER JOIN CUESTIONARIO_RESPUESTA c ON r.ID_CUESTIONARIO = c.ID_CUESTIONARIO
    WHERE c.CURP = ? AND c.ID_PERIODO = ?
";
$stmtPrevias = $conexion->prepare($consultaRespuestas);
$stmtPrevias->bind_param("si", $id_egresado, $ultimo_periodo);
$stmtPrevias->execute();
$resultadoPrevio = $stmtPrevias->get_result();

$respuestas_previas = [];
while ($row = $resultadoPrevio->fetch_assoc()) {
    $pid = $row['ID_PREGUNTA'];
    if (!isset($respuestas_previas[$pid])) {
        $respuestas_previas[$pid] = [];
    }
    if ($row['ID_OPCION']) {
        // Puede tener múltiples opciones (checkbox)
        $respuestas_previas[$pid][] = $row['ID_OPCION'];
    } elseif ($row['RESPUESTA_TEXTO']) {
        // Respuesta de texto (textarea o similar)
        $respuestas_previas[$pid] = $row['RESPUESTA_TEXTO'];
    }
}
$stmtPrevias->close();

// 3) Cargar datos previos de la empresa si existen (tabla EMPRESA relacionada por CURP y periodo)
$datos_empresa = [
    'tipo_organismo' => '',
    'giro' => '',
    'razon_social' => '',
    'calle' => '',
    'numero' => '',
    'colonia' => '',
    'codigo_postal' => '',
    'ciudad' => '',
    'municipio' => '',
    'estado' => '',
    'telefono' => '',
    'email' => '',
    'pagina_web' => '',
    'jefe_nombre' => '',
    'jefe_puesto' => ''
];

// 3) Cargar datos previos de la empresa si existen (tabla EMPRESA relacionada por CURP y periodo)
// Primero obtener el ID_EMPRESA de CUESTIONARIO_RESPUESTA
$id_empresa = null;

$queryEmpresaId = "
    SELECT ID_EMPRESA FROM CUESTIONARIO_RESPUESTA WHERE CURP = ? AND ID_PERIODO = ? LIMIT 1
";
$stmtEmpresaId = $conexion->prepare($queryEmpresaId);
$stmtEmpresaId->bind_param("si", $id_egresado, $ultimo_periodo);
$stmtEmpresaId->execute();
$resultEmpresaId = $stmtEmpresaId->get_result();
if ($resultEmpresaId && $resultEmpresaId->num_rows > 0) {
    $filaEmpresaId = $resultEmpresaId->fetch_assoc();
    $id_empresa = $filaEmpresaId['ID_EMPRESA'];
}
$stmtEmpresaId->close();

// Si encontramos el ID_EMPRESA, obtener los datos de la empresa
if ($id_empresa !== null) {
    $queryEmpresa = "
        SELECT tipo_organismo, giro, razon_social, calle, numero, colonia, codigo_postal, ciudad, municipio, estado, telefono, email, pagina_web, jefe_inmediato_nombre, jefe_inmediato_puesto
        FROM EMPRESA
        WHERE ID_EMPRESA = ?
        LIMIT 1
    ";
    $stmtEmpresa = $conexion->prepare($queryEmpresa);
    $stmtEmpresa->bind_param("i", $id_empresa);
    $stmtEmpresa->execute();
    $resultEmpresa = $stmtEmpresa->get_result();
    if ($resultEmpresa && $resultEmpresa->num_rows > 0) {
        $datos_empresa = $resultEmpresa->fetch_assoc();
    }
    $stmtEmpresa->close();
}

// 4) Cargar preguntas, secciones y opciones
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
                                <option value="Público" <?= ($datos_empresa['tipo_organismo'] === 'Público') ? 'selected' : '' ?>>Público</option>
                                <option value="Privado" <?= ($datos_empresa['tipo_organismo'] === 'Privado') ? 'selected' : '' ?>>Privado</option>
                                <option value="Social" <?= ($datos_empresa['tipo_organismo'] === 'Social') ? 'selected' : '' ?>>Social</option>
                            </select>
                            <label for="giro">Giro:</label>
                            <textarea name="empresa[giro]" id="giro"><?= htmlspecialchars($datos_empresa['giro']) ?></textarea>
                            <label for="razon_social">Razón social:</label>
                            <input type="text" name="empresa[razon_social]" id="razon_social" value="<?= htmlspecialchars($datos_empresa['razon_social']) ?>">
                            <div class="doble-campo">
                                <div>
                                    <label for="calle">Calle:</label>
                                    <input type="text" name="empresa[calle]" id="calle" value="<?= htmlspecialchars($datos_empresa['calle']) ?>">
                                </div>
                                <div>
                                    <label for="numero">Número:</label>
                                    <input type="text" name="empresa[numero]" id="numero" value="<?= htmlspecialchars($datos_empresa['numero']) ?>">
                                </div>
                            </div>
                            <div class="doble-campo">
                                <div>
                                    <label for="colonia">Colonia:</label>
                                    <input type="text" name="empresa[colonia]" id="colonia" value="<?= htmlspecialchars($datos_empresa['colonia']) ?>">
                                </div>
                                <div>
                                    <label for="codigo_postal">Código postal:</label>
                                    <input type="text" name="empresa[codigo_postal]" id="codigo_postal" value="<?= htmlspecialchars($datos_empresa['codigo_postal']) ?>">
                                </div>
                            </div>
                            <div class="doble-campo">
                                <div>
                                    <label for="ciudad">Ciudad:</label>
                                    <input type="text" name="empresa[ciudad]" id="ciudad" value="<?= htmlspecialchars($datos_empresa['ciudad']) ?>">
                                </div>
                                <div>
                                    <label for="municipio">Municipio:</label>
                                    <input type="text" name="empresa[municipio]" id="municipio" value="<?= htmlspecialchars($datos_empresa['municipio']) ?>">
                                </div>
                            </div>
                            <label for="estado">Estado:</label>
                            <input type="text" name="empresa[estado]" id="estado" value="<?= htmlspecialchars($datos_empresa['estado']) ?>">
                            <label for="telefono">Teléfono:</label>
                            <input type="text" name="empresa[telefono]" id="telefono" value="<?= htmlspecialchars($datos_empresa['telefono']) ?>">
                            <label for="email">Correo electrónico:</label>
                            <input type="email" name="empresa[email]" id="email" value="<?= htmlspecialchars($datos_empresa['email']) ?>">
                            <label for="pagina_web">Página web:</label>
                            <input type="url" name="empresa[pagina_web]" id="pagina_web" value="<?= htmlspecialchars($datos_empresa['pagina_web']) ?>">
                            <label for="jefe_inmediato_nombre">Nombre del jefe inmediato:</label>
                            <input type="text" name="empresa[jefe_nombre]" id="jefe_inmediato_nombre" value="<?= htmlspecialchars($datos_empresa['jefe_inmediato_nombre']) ?>">
                            <label for="jefe_inmediato_puesto">Puesto del jefe inmediato:</label>
                            <input type="text" name="empresa[jefe_inmediato_puesto]" id="jefe_inmediato_puesto" value="<?= htmlspecialchars($datos_empresa['jefe_inmediato_puesto']) ?>">
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

<script src="js/cuestionario.js"></script>
</body>
</html>

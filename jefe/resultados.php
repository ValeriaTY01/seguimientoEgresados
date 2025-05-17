<?php
session_start();
if (!isset($_SESSION['rol'])) {
    header("Location: ../login.php");
    exit();
}
require_once('../db/conexion.php');
include ('../includes/header_admin.php');
include ('../includes/menu.php');

$isJefe = isset($_SESSION['rol']) && $_SESSION['rol'] === 'jefe departamento';
$carreraJefe = isset($_SESSION['carrera']) ? $_SESSION['carrera'] : '';

$carrera = $_GET['carrera'] ?? '';
$anio = $_GET['anio'] ?? '';
$sexo = $_GET['sexo'] ?? '';
$titulado = $_GET['titulado'] ?? '';

if ($isJefe) {
    $carrera = $carreraJefe;
    $_GET['carrera'] = $carreraJefe;
}

// Obtener periodo activo por defecto
$periodos = $conexion->query("SELECT ID_PERIODO, NOMBRE FROM PERIODO_ENCUESTA ORDER BY FECHA_INICIO DESC")->fetch_all(MYSQLI_ASSOC);

$periodo = $_GET['periodo'] ?? '';
if (!$periodo && !empty($periodos)) {
    foreach ($periodos as $p) {
        // Suponiendo que hay una columna ACTIVO (1 o true para el activo)
        $activo = $conexion->query("SELECT ID_PERIODO FROM PERIODO_ENCUESTA WHERE ACTIVO = 1 LIMIT 1")->fetch_assoc();
        if ($activo) {
            $periodo = $activo['ID_PERIODO'];
            $_GET['periodo'] = $periodo;
        }
    }
}

// Determinar si la carrera es química o bioquímica
$carreraLower = strtolower($carrera);
$esQuimica = in_array($carreraLower, ['ingeniería química', 'ingeniería bioquímica', 'quimica']);

if ($isJefe && $esQuimica) {
    // Cargar solo secciones específicas para química (PARA_CARRERA = 'quimica') excluyendo ID 1 y 7
    $secciones = $conexion->query("
        SELECT ID_SECCION, NOMBRE
        FROM SECCION
        WHERE PARA_CARRERA = 'quimica'
          AND ID_SECCION NOT IN (1,7)
        ORDER BY ORDEN
    ")->fetch_all(MYSQLI_ASSOC);
} else {
    // Cargar secciones generales (PARA_CARRERA IS NULL) excluyendo ID 1 y 7
    $secciones = $conexion->query("
        SELECT ID_SECCION, NOMBRE
        FROM SECCION
        WHERE PARA_CARRERA IS NULL
          AND ID_SECCION NOT IN (1,7)
        ORDER BY ORDEN
    ")->fetch_all(MYSQLI_ASSOC);
}

function obtenerPreguntasPorSeccion($conexion, $idSeccion) {
    return $conexion->query("
        SELECT ID_PREGUNTA, TEXTO 
        FROM PREGUNTA 
        WHERE ID_SECCION = $idSeccion AND TIPO != 'texto' 
        ORDER BY ORDEN
    ")->fetch_all(MYSQLI_ASSOC);
}

function obtenerResultadosPregunta($conexion, $idPregunta, $filtros) {
    $condiciones = "";
    $carrera = isset($filtros['carrera']) ? $conexion->real_escape_string($filtros['carrera']) : '';
    $anio = isset($filtros['anio']) ? intval($filtros['anio']) : 0;
    $sexo = isset($filtros['sexo']) ? $conexion->real_escape_string($filtros['sexo']) : '';
    $titulado = isset($filtros['titulado']) && $filtros['titulado'] !== '' ? intval($filtros['titulado']) : null;

    if (!empty($carrera)) {
        $condiciones .= " AND e.CARRERA = '$carrera'";
    }
    if (!empty($anio)) {
        $condiciones .= " AND YEAR(e.FECHA_EGRESO) = $anio";
    }
    if (!empty($sexo)) {
        $condiciones .= " AND e.SEXO = '$sexo'";
    }
    if ($titulado !== null) {
        $condiciones .= " AND e.TITULADO = $titulado";
    }
    $periodo = isset($filtros['periodo']) && $filtros['periodo'] !== '' ? intval($filtros['periodo']) : null;
    if ($periodo !== null) {
        $condiciones .= " AND cr.ID_PERIODO = $periodo";
    }


    $sql = "
        SELECT 
            o.TEXTO AS opcion,
            COUNT(r.ID_OPCION) AS frecuencia,
            (
                SELECT COUNT(*) 
                FROM RESPUESTA r2
                JOIN CUESTIONARIO_RESPUESTA cr2 ON r2.ID_CUESTIONARIO = cr2.ID_CUESTIONARIO
                JOIN EGRESADO e2 ON cr2.CURP = e2.CURP
                WHERE r2.ID_PREGUNTA = $idPregunta $condiciones
            ) AS total
        FROM OPCION_RESPUESTA o
        LEFT JOIN RESPUESTA r ON o.ID_OPCION = r.ID_OPCION
        LEFT JOIN CUESTIONARIO_RESPUESTA cr ON r.ID_CUESTIONARIO = cr.ID_CUESTIONARIO
        LEFT JOIN EGRESADO e ON cr.CURP = e.CURP
        WHERE o.ID_PREGUNTA = $idPregunta
        $condiciones
        GROUP BY o.ID_OPCION, o.TEXTO
        ORDER BY o.TEXTO
    ";

    return $conexion->query($sql)->fetch_all(MYSQLI_ASSOC);
}

$aniosEgreso = $conexion->query("
    SELECT DISTINCT YEAR(FECHA_EGRESO) AS anio
    FROM EGRESADO
    ORDER BY anio DESC
")->fetch_all(MYSQLI_ASSOC);

?>

<!DOCTYPE html>
<html>
<head>
    <title>Resultados Estadísticos</title>
    <link rel="stylesheet" href="css/resultados.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js"></script>
    <script src="js/resultados.js" defer></script>
</head>
<body>
<h1>Resultados Estadísticos de Encuestas</h1>
<?php
$nombrePeriodo = '';
if (!empty($periodo)) {
    $res = $conexion->query("SELECT NOMBRE FROM PERIODO_ENCUESTA WHERE ID_PERIODO = $periodo")->fetch_assoc();
    if ($res) {
        $nombrePeriodo = $res['NOMBRE'];
        echo "<h3>Mostrando resultados del período: " . htmlspecialchars($nombrePeriodo) . "</h3>";
    }
}
?>
<form method="GET" class="filtros-form">
    Carrera: 
    <select name="carrera">
        <option value="" selected>-- Seleccione una carrera --</option>
        <?php if (!$isJefe): ?>
            <?php
            $carreras = [
                'Licenciatura en Administración',
                'Ingeniería Bioquímica',
                'Ingeniería Eléctrica',
                'Ingeniería Electrónica',
                'Ingeniería Industrial',
                'Ingeniería Mecatrónica',
                'Ingeniería Mecánica',
                'Ingeniería en Sistemas Computacionales',
                'Ingeniería Química',
                'Ingeniería en Energías Renovables',
                'Ingeniería en Gestión Empresarial'
            ];
            foreach ($carreras as $c) {
                $selected = ($carrera == $c) ? 'selected' : '';
                echo "<option value=\"$c\">$c</option>";
            }
            ?>
        <?php else: ?>
            <option value="<?= htmlspecialchars($carreraJefe) ?>" selected><?= htmlspecialchars($carreraJefe) ?></option>
        <?php endif; ?>
    </select>

    Año egreso: 
    <select name="anio">
        <option value="">Todos</option>
        <?php foreach ($aniosEgreso as $row): ?>
            <option value="<?= $row['anio'] ?>" <?= $anio == $row['anio'] ? 'selected' : '' ?>>
                <?= $row['anio'] ?>
            </option>
        <?php endforeach; ?>
    </select>

    Sexo: 
    <select name="sexo">
        <option value="">Todos</option>
        <option value="Hombre" <?= $sexo == 'Hombre' ? 'selected' : '' ?>>Hombre</option>
        <option value="Mujer" <?= $sexo == 'Mujer' ? 'selected' : '' ?>>Mujer</option>
    </select>

    Titulado: 
    <select name="titulado">
        <option value="">Todos</option>
        <option value="1" <?= $titulado === '1' ? 'selected' : '' ?>>Sí</option>
        <option value="0" <?= $titulado === '0' ? 'selected' : '' ?>>No</option>
    </select>

    Período:
    <select name="periodo">
        <option value="">Todos</option>
        <?php foreach ($periodos as $p): ?>
            <option value="<?= $p['ID_PERIODO'] ?>" <?= ($periodo == $p['ID_PERIODO']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($p['NOMBRE']) ?>
            </option>
        <?php endforeach; ?>
    </select>
    
    <button type="submit">Filtrar</button>
</form>
<hr>

<div class="secciones-grid">
<?php foreach ($secciones as $seccion): 
    if ($seccion['ID_SECCION'] == 1 || $seccion['ID_SECCION'] == 7) continue;
?>
    <div class="acordeon-section" onclick="openModal('sec<?= $seccion['ID_SECCION'] ?>')">
        <div class="acordeon-header">
            <?= htmlspecialchars($seccion['NOMBRE']) ?>
        </div>
    </div>

    <!-- MODAL -->
    <div class="modal-overlay" id="sec<?= $seccion['ID_SECCION'] ?>">
        <div class="modal-content">
            <span class="close-btn" onclick="closeModal('sec<?= $seccion['ID_SECCION'] ?>')">&times;</span>
            <h3><?= htmlspecialchars($seccion['NOMBRE']) ?></h3>
            <?php 
            $preguntas = obtenerPreguntasPorSeccion($conexion, $seccion['ID_SECCION']);
            foreach ($preguntas as $pregunta): 
                $resultados = obtenerResultadosPregunta($conexion, $pregunta['ID_PREGUNTA'], $_GET);
                $total = array_sum(array_column($resultados, 'frecuencia')) ?: 1;

                if (count($resultados) > 0): 
            ?>
                <h4><?= htmlspecialchars($pregunta['TEXTO']) ?></h4>
                <table border="1" cellpadding="5">
                    <tr><th>Opción</th><th>Frecuencia</th><th>Porcentaje</th></tr>
                    <?php foreach ($resultados as $res): 
                        $porc = round(($res['frecuencia'] / $total) * 100, 1);
                    ?>
                        <tr>
                            <td><?= htmlspecialchars($res['opcion']) ?></td>
                            <td><?= $res['frecuencia'] ?></td>
                            <td><?= $porc ?>%</td>
                        </tr>
                    <?php endforeach; ?>
                </table>
                <canvas id="graf<?= $pregunta['ID_PREGUNTA'] ?>"></canvas>
                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        renderChart('graf<?= $pregunta['ID_PREGUNTA'] ?>', <?= json_encode(array_column($resultados, 'opcion')) ?>, <?= json_encode(array_map(fn($r) => round($r['frecuencia'] / $total * 100, 1), $resultados)) ?>);
                    });
                </script>
                <hr>
            <?php else: ?>
                <h4><?= htmlspecialchars($pregunta['TEXTO']) ?></h4>
                <p style="color: gray; font-style: italic;">No existen datos suficientes para realizar la graficación.</p>
                <hr>
            <?php endif; ?>

            <?php endforeach; ?>
        </div>
    </div>
<?php endforeach; ?>
</div>

<?php
$indicadoresParametros = [
    'Calidad de los docentes' => ['Muy Buena', 75, 'II.1'],
    'Plan de Estudios' => ['Muy Buena', 75, 'II.2'],
    'Oportunidad de participar en proyectos de investigación y desarrollo' => ['Muy Buena', 50, 'II.3'],
    'Énfasis en investigación dentro del proceso de enseñanza' => ['Muy Buena', 50, 'II.4'],
    'Satisfacción con las condiciones de estudio (infraestructura)' => ['Muy Buena', 75, 'II.5'],
    'Experiencia obtenida a través de la residencia profesional' => ['Muy Buena', 90, 'II.6'],

    'Actividad actual (laboral/académica)' => ['Trabaja', 60, 'III.1'],
    'Tiempo para conseguir el primer empleo' => ['Entre seis meses y un año', 60, 'III.2'],
    'Medio para obtener el empleo' => ['Bolsa de trabajo del plantel', 10, 'III.3'],
    'Nivel jerárquico en el trabajo' => ['Jefe de área', 70, 'III.9'],
    'Relación del trabajo con su formación (%)' => ['80%', 90, 'III.11'],  

    'Eficiencia para realizar actividades laborales en relación con su formación académica' => ['Eficiente', 70, 'IV.1'],
    'Formación académica respecto a su desempeño laboral' => ['Malo', 10, 'IV.2'],
    'Utilidad de las residencias profesionales o prácticas para su desarrollo profesional' => ['Bueno', 30, 'IV.3'],

    'Le gustaría tomar algún posgrado:' => ['Sí', 5, 'V.1'],
];

$resultadosIndicadores = [];

foreach ($secciones as $seccion) {
    $preguntas = obtenerPreguntasPorSeccion($conexion, $seccion['ID_SECCION']);

    foreach ($preguntas as $pregunta) {
        $textoPregunta = $pregunta['TEXTO'];
        $resultados = obtenerResultadosPregunta($conexion, $pregunta['ID_PREGUNTA'], $_GET);
        $total = array_sum(array_column($resultados, 'frecuencia')) ?: 1;

        foreach ($indicadoresParametros as $clave => [$opcionEsperada, $parametroMinimo, $codigo]) {
            if (stripos($textoPregunta, $clave) !== false) {
                $frecuenciaEsperada = 0;

                foreach ($resultados as $res) {
                    if (strcasecmp(trim($res['opcion']), trim($opcionEsperada)) === 0) {
                        $frecuenciaEsperada = $res['frecuencia'];
                        break;
                    }
                }

                $porcentaje = round(($frecuenciaEsperada / $total) * 100, 1);
                $cumple = $porcentaje >= $parametroMinimo;

                $resultadosIndicadores[] = [
                    'codigo' => $codigo,
                    'indicador' => $clave,
                    'resultado' => $porcentaje,
                    'minimo' => $parametroMinimo,
                    'cumple' => $cumple
                ];
            }
        }
    }
}

?>

<div class="resumen-indicadores">
    <h2>📊 Resumen de Cumplimiento de Indicadores</h2>
    <table>
        <tr>
            <th>Código</th>
            <th>Indicador</th>
            <th>Resultado</th>
            <th>Parámetro</th>
            <th>¿Cumple?</th>
        </tr>
        <?php foreach ($resultadosIndicadores as $ind): ?>
            <tr class="<?= $ind['cumple'] ? 'cumple-true' : 'cumple-false' ?>">
                <td><?= htmlspecialchars($ind['codigo']) ?></td>
                <td><?= htmlspecialchars($ind['indicador']) ?></td>
                <td><?= $ind['resultado'] ?>%</td>
                <td>≥ <?= $ind['minimo'] ?>%</td>
                <td>
                    <?= $ind['cumple'] ? '✅ Sí' : '❌ No' ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>
<script src="js/resultados.js" defer\></script>
</body>
</html>
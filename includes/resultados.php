<?php
session_start();
require_once('../db/conexion.php');

$isJefe = $_SESSION['rol'] === 'jefe departamento';
$carreraJefe = $_SESSION['carrera'] ?? '';

$carrera = $_GET['carrera'] ?? '';
$anio = $_GET['anio'] ?? '';
$sexo = $_GET['sexo'] ?? '';
$titulado = $_GET['titulado'] ?? '';

// Obtener años únicos de egreso
$aniosEgreso = $conexion->query("
    SELECT DISTINCT YEAR(FECHA_EGRESO) AS anio 
    FROM EGRESADO 
    WHERE FECHA_EGRESO IS NOT NULL 
    ORDER BY anio DESC
")->fetch_all(MYSQLI_ASSOC);

// Si es jefe, solo se muestra la carrera del jefe
if ($isJefe && empty($carrera)) {
    $carrera = $carreraJefe;
}

// Obtener secciones dinámicamente
$secciones = $conexion->query("
    SELECT ID_SECCION, NOMBRE 
    FROM SECCION 
    WHERE PARA_CARRERA IS NULL 
    ORDER BY ORDEN
")->fetch_all(MYSQLI_ASSOC);

// Función para obtener preguntas por sección
function obtenerPreguntasPorSeccion($conexion, $idSeccion) {
    return $conexion->query("
        SELECT ID_PREGUNTA, TEXTO 
        FROM PREGUNTA 
        WHERE ID_SECCION = $idSeccion AND TIPO != 'texto' 
        ORDER BY ORDEN
    ")->fetch_all(MYSQLI_ASSOC);
}

// Función para obtener resultados de una pregunta
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
        GROUP BY o.ID_OPCION, o.TEXTO
        ORDER BY o.TEXTO
    ";

    return $conexion->query($sql)->fetch_all(MYSQLI_ASSOC);
}

include ('../includes/header_admin.php');
include ('../includes/menu.php');
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
<form method="GET">
    Carrera: 
    <select name="carrera">
        <option value="">-- Seleccione una carrera --</option>
        <?php if (!$isJefe): ?>
            <!-- Lista de carreras -->
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
                echo "<option value=\"$c\" $selected>$c</option>";
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
    
    <button type="submit">Filtrar</button>
</form>
<hr>

<?php foreach ($secciones as $seccion): ?>
    <div class="acordeon-section">
        <div class="acordeon-header" onclick="toggleAcordeon('sec<?= $seccion['ID_SECCION'] ?>')">
            <?= htmlspecialchars($seccion['NOMBRE']) ?>
        </div>
        <div class="acordeon-body" id="sec<?= $seccion['ID_SECCION'] ?>">
            <?php 
            $preguntas = obtenerPreguntasPorSeccion($conexion, $seccion['ID_SECCION']);
            foreach ($preguntas as $pregunta): 
                $resultados = obtenerResultadosPregunta($conexion, $pregunta['ID_PREGUNTA'], $_GET);
                $total = array_sum(array_column($resultados, 'frecuencia')) ?: 1;
                if (count($resultados) > 0): // Solo mostrar gráficos si hay respuestas
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
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
<?php endforeach; ?>
</body>
</html>
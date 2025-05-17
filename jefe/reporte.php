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
$carreraJefe = $_SESSION['carrera'] ?? '';

$carrera = $_GET['carrera'] ?? '';
$anio = $_GET['anio'] ?? '';
$sexo = $_GET['sexo'] ?? '';
$titulado = $_GET['titulado'] ?? '';
$tipo_informe = $_GET['tipo_informe'] ?? '';

if (empty($_GET['periodo'])) {
    $activo = $conexion->query("SELECT ID_PERIODO FROM PERIODO_ENCUESTA WHERE ACTIVO = 1 LIMIT 1")->fetch_assoc();
    if ($activo) {
        $periodo = $activo['ID_PERIODO'];
        $_GET['periodo'] = $periodo;
    } else {
        $periodo = '';
    }
} else {
    $periodo = $_GET['periodo'];
}


if ($isJefe) {
    $carrera = $carreraJefe;
    $_GET['carrera'] = $carreraJefe;
}

// Obtener periodos disponibles
$periodos = $conexion->query("SELECT ID_PERIODO, NOMBRE FROM PERIODO_ENCUESTA ORDER BY FECHA_INICIO DESC")->fetch_all(MYSQLI_ASSOC);

// Obtener años únicos de egreso desde la base de datos
$anios = $conexion->query("
    SELECT DISTINCT YEAR(FECHA_EGRESO) AS anio 
    FROM EGRESADO 
    WHERE FECHA_EGRESO IS NOT NULL 
    ORDER BY anio DESC
")->fetch_all(MYSQLI_ASSOC);

// Cargar secciones (puedes adaptar según filtros si lo deseas)
$secciones = $conexion->query("SELECT ID_SECCION, NOMBRE FROM SECCION ORDER BY ORDEN")->fetch_all(MYSQLI_ASSOC);


if ($carreraJefe === 'Ingeniería Química' || $carreraJefe === 'Ingeniería Bioquímica') {
    $filtroCarrera = "WHERE PARA_CARRERA = 'quimica'";
} else {
    $filtroCarrera = "WHERE PARA_CARRERA IS NULL AND ID_SECCION NOT IN (1, 7)";
}

$secciones = $conexion->query("
    SELECT ID_SECCION, NOMBRE 
    FROM SECCION 
    $filtroCarrera
    ORDER BY ORDEN
")->fetch_all(MYSQLI_ASSOC);



// Tipos de informe
$tiposInforme = [
    'estadistico' => 'Informe Estadístico',
    'detallado' => 'Reporte Detallado por Egresado',
    'participacion' => 'Estado de Participación',
    'por_seccion' => 'Informe por Sección',
    'historico' => 'Comparativo Histórico',
];
?>

<link rel="stylesheet" href="css/reporte.css">
<script src="js/reportes.js"></script>
<div class="container">
    <h2>Generación de Informes</h2>
    <form method="POST" action="jefe/generar_reporte.php">
        <label> Carrera: 
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
        </label>

        <label>Año de Egreso:
            <select name="anio">
                <option value="">-- Todos --</option>
                <?php foreach ($anios as $a): ?>
                    <option value="<?= $a['anio'] ?>" <?= $anio == $a['anio'] ? 'selected' : '' ?>>
                        <?= $a['anio'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <label>Sexo: 
            <select name="sexo">
                <option value="">Todos</option>
                <option value="Hombre" <?= $sexo == 'Hombre' ? 'selected' : '' ?>>Hombre</option>
                <option value="Mujer" <?= $sexo == 'Mujer' ? 'selected' : '' ?>>Mujer</option>
            </select>
        </label>

        <label>Titulado:
            <select name="titulado">
                <option value="">-- Todos --</option>
                <option value="1" <?= $titulado === '1' ? 'selected' : '' ?>>Sí</option>
                <option value="0" <?= $titulado === '0' ? 'selected' : '' ?>>No</option>
            </select>
        </label>

        <label>Periodo de Encuesta:
            <select name="periodo">
                <option value="">-- Todos --</option>
                <?php foreach ($periodos as $p): ?>
                    <option value="<?= $p['ID_PERIODO'] ?>" <?= $periodo == $p['ID_PERIODO'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($p['NOMBRE']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <label>Tipo de Informe:
            <select name="tipo_informe" required>
                <option value="">-- Seleccione --</option>
                <?php foreach ($tiposInforme as $valor => $etiqueta): ?>
                    <option value="<?= $valor ?>" <?= $tipo_informe === $valor ? 'selected' : '' ?>>
                        <?= $etiqueta ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <label>Formato:
        <select name="formato" required>
            <option value="excel">Excel</option>
            <option value="pdf">PDF</option>
        </select>
        </label>

        <div id="secciones_container">
            <label>Seleccione Sección:
                <select name="seccion" id="seccion">
                    <option value="">-- Seleccione una sección --</option>
                    <?php foreach ($secciones as $s): ?>
                        <option value="<?= $s['ID_SECCION'] ?>"><?= htmlspecialchars($s['NOMBRE']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
        </div>

        <div id="busqueda_egresado_container">
            <label for="busqueda_egresado">🔍 Buscar Egresado:</label><br>
            <input type="text" id="busqueda_egresado" name="busqueda_egresado"
                autocomplete="off" placeholder="Nombre, Apellido, CURP o No. Control">
            <input type="hidden" id="curp_seleccionado" name="curp_seleccionado">
            <div id="resultados_busqueda"></div>
        </div>

        <button type="submit">Generar</button>
    </form>
</div>
<?php 
session_start();
if (!isset($_SESSION['rol'])) {
    header("Location: ../login.php");
    exit();
}

require_once('../db/conexion.php');
include ('header.php');
include ('menu.php');

$rol = $_SESSION['rol'];
$carreraJefe = $_SESSION['carrera'] ?? '';

$isAdmin = $rol === 'administrador';
$isJefeVinculacion = $rol === 'jefe vinculacion';
$isJefe = $rol === 'jefe departamento';

$puedeVerTodas = $isAdmin || $isJefeVinculacion;

$carrera = $_GET['carrera'] ?? '';
$anio = $_GET['anio'] ?? '';
$sexo = $_GET['sexo'] ?? '';
$titulado = $_GET['titulado'] ?? '';
$tipo_informe = $_GET['tipo_informe'] ?? '';
$periodo = $_GET['periodo'] ?? '';

if ($isJefe) {
    $carrera = $carreraJefe;
    $_GET['carrera'] = $carreraJefe;
}

// Obtener periodos disponibles
$periodos = $conexion->query("SELECT ID_PERIODO, NOMBRE FROM PERIODO_ENCUESTA ORDER BY FECHA_INICIO DESC")->fetch_all(MYSQLI_ASSOC);

// Obtener años únicos de egreso
$anios = $conexion->query("
    SELECT DISTINCT YEAR(FECHA_EGRESO) AS anio 
    FROM EGRESADO 
    WHERE FECHA_EGRESO IS NOT NULL 
    ORDER BY anio DESC
")->fetch_all(MYSQLI_ASSOC);

// Verificar si la carrera es del tipo 'química'
$carreraLower = strtolower($carrera);
$esCarreraQuimica = in_array($carreraLower, ['ingeniería química', 'ingeniería bioquímica']);

if ($esCarreraQuimica) {
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
    'historico' => 'Histórico Acumulado',
];
?>

<link rel="stylesheet" href="css/reporte.css">
<script src="js/reportes.js"></script>

<div class="container">
    <h2>Generación de Informes</h2>
    <form method="POST" action="includes/generar_reporte.php">

        <label> Carrera: 
            <select name="carrera">
                <?php if ($isJefe): ?>
                    <option value="<?= htmlspecialchars($carreraJefe) ?>" selected><?= htmlspecialchars($carreraJefe) ?></option>
                <?php else: ?>
                    <option value="">-- Seleccione una carrera --</option>
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
                <?php endif; ?>
            </select>
        </label>

        <label>Año de Egreso:
            <select name="anio">
                <option value="">Todos</option>
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
                <option value="">Todos</option>
                <option value="1" <?= $titulado === '1' ? 'selected' : '' ?>>Sí</option>
                <option value="0" <?= $titulado === '0' ? 'selected' : '' ?>>No</option>
            </select>
        </label>

        <label>Periodo de Encuesta:
            <select name="periodo">
                <option value="">Todos</option>
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

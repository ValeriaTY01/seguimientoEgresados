<?php
session_start();
if (!isset($_SESSION['rol']) || !in_array($_SESSION['rol'], ['administrador', 'jefe departamento', 'jefe vinculacion'])) {
    header("Location: ../login.php");
    exit();
}

require_once('../db/conexion.php');
require '../vendor/autoload.php';

$rfc_usuario = $_SESSION['rfc'] ?? '';
$rol = $_SESSION['rol'] ?? '';
$carreraSesion = $_SESSION['carrera'] ?? '';

$filtros = [
    'carrera' => $_POST['carrera'] ?? '',
    'anio' => $_POST['anio'] ?? '',
    'sexo' => $_POST['sexo'] ?? '',
    'titulado' => $_POST['titulado'] ?? '',
    'periodo' => $_POST['periodo'] ?? '',
    'tipo_informe' => $_POST['tipo_informe'] ?? '',
    'formato' => $_POST['formato'] ?? 'excel',
    'seccion' => $_POST['seccion'] ?? '',
    'curp_seleccionado' => $_POST['curp_seleccionado'] ?? '',
];

// Si es jefe, forzamos su carrera
if ($rol === 'jefe departamento') {
    $filtros['carrera'] = $carreraSesion;
}

$carrera = $filtros['carrera'];

// Validar si se intenta generar un reporte de encuesta 'química'
$tipoEncuesta = null;
if (in_array($carrera, ['Ingeniería Química', 'Ingeniería Bioquímica'])) {
    $tipoEncuesta = 'quimica';
} else {
    $tipoEncuesta = 'general';
}

// Bloquear generación si se intenta acceder a encuesta 'química' sin ser de IQ o IBQ
if ($tipoEncuesta === 'quimica' && !in_array($carrera, ['Ingeniería Química', 'Ingeniería Bioquímica'])) {
    echo "<h3 style='color:red;'>Acceso denegado: Solo Ingeniería Química o Ingeniería Bioquímica pueden generar reportes de encuestas tipo 'química'.</h3>";
    exit();
}

$mapTipoInforme = [
    'estadistico' => 'Informe Estadístico',
    'detallado' => 'Reporte Detallado por Egresado',
    'participacion' => 'Estado de Participación',
    'por_seccion' => 'Informe por Sección',
    'historico' => 'Histórico Acumulado', // <-- aquí
];


$tipoInformeEtiqueta = $mapTipoInforme[$filtros['tipo_informe']] ?? 'Desconocido';

// Registrar en historial
$stmt = $conexion->prepare("
    INSERT INTO HISTORIAL_CONSULTA (RFC, FILTROS, TIPO_INFORME)
    VALUES (?, ?, ?)
");
$jsonFiltros = json_encode($filtros);
$stmt->bind_param("sss", $rfc_usuario, $jsonFiltros, $tipoInformeEtiqueta);
$stmt->execute();

// Incluir y generar el reporte solicitado
switch ($filtros['tipo_informe']) {
    case 'estadistico':
        include 'reportes/estadistico.php';
        generarInformeEstadistico($conexion, $carrera, $filtros);
        break;

    case 'detallado':
        include 'reportes/detallado.php';
        generarReporteDetallado($conexion, $carrera, $filtros);
        break;

    case 'participacion':
        include 'reportes/participacion.php';
        generarReporteParticipacion($conexion, $carrera, $filtros);
        break;

    case 'por_seccion':
        include 'reportes/por_seccion.php';
        generarInformePorSeccion($conexion, $carrera, $filtros);
        break;

    case 'historico':
        include 'reportes/historico.php';
        generarInformeHistoricoAcumulado($conexion, $carrera, $filtros);
        break;

    default:
        echo "Tipo de informe no implementado aún.";
        exit();
}

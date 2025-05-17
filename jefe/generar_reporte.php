<?php
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'jefe departamento') {
    header("Location: ../login.php");
    exit();
}

require_once('../db/conexion.php');
require '../vendor/autoload.php';

$rfc_usuario = $_SESSION['rfc'] ?? '';
$carrera = $_SESSION['carrera'] ?? '';

$filtros = [
    'carrera' => $carrera,
    'anio' => $_POST['anio'] ?? '',
    'sexo' => $_POST['sexo'] ?? '',
    'titulado' => $_POST['titulado'] ?? '',
    'periodo' => $_POST['periodo'] ?? '',
    'tipo_informe' => $_POST['tipo_informe'] ?? '',
    'formato' => $_POST['formato'] ?? 'excel',
    'seccion' => $_POST['seccion'] ?? '',
    'curp_seleccionado' => $_POST['curp_seleccionado'] ?? '',
];

$mapTipoInforme = [
    'estadistico' => 'Informe Estadístico',
    'detallado' => 'Reporte Detallado por Egresado',
    'participacion' => 'Estado de Participación',
    'por_seccion' => 'Informe por Sección',
    'historico' => 'Comparativo Histórico',
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

// Incluir reporte correspondiente
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
        generarInformeHistorico($conexion, $carrera, $filtros);
        break;

    default:
        echo "Tipo de informe no implementado aún.";
        exit();
}

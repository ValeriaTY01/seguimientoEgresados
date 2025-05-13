<?php
require('../vendor/autoload.php');
require('../db/conexion.php');
session_start();

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$isJefe = $_SESSION['rol'] === 'jefe departamento';
$carreraJefe = $_SESSION['carrera'] ?? '';

$spreadsheet = new Spreadsheet();
$spreadsheet->removeSheetByIndex(0);

if ($carreraJefe === 'Ingeniería Química' || $carreraJefe === 'Ingeniería Bioquímica') {
    $seccionesQuery = "SELECT ID_SECCION, NOMBRE FROM SECCION WHERE PARA_CARRERA = 'quimica' ORDER BY ORDEN";
    $stmtSecciones = $conexion->prepare($seccionesQuery);
} else {
    $seccionesQuery = "SELECT ID_SECCION, NOMBRE FROM SECCION WHERE PARA_CARRERA IS NULL ORDER BY ORDEN";
    $stmtSecciones = $conexion->prepare($seccionesQuery);
}

$stmtSecciones->execute();
$secciones = $stmtSecciones->get_result();

while ($seccion = $secciones->fetch_assoc()) {
    $idSeccion = $seccion['ID_SECCION'];
    $nombreSeccion = $seccion['NOMBRE'];

    $preguntasQuery = "SELECT ID_PREGUNTA, TEXTO FROM PREGUNTA WHERE ID_SECCION = ? ORDER BY ID_PREGUNTA";
    $stmtPreguntas = $conexion->prepare($preguntasQuery);
    $stmtPreguntas->bind_param("i", $idSeccion);
    $stmtPreguntas->execute();
    $preguntasResult = $stmtPreguntas->get_result();

    $preguntas = [];
    while ($pregunta = $preguntasResult->fetch_assoc()) {
        $preguntas[$pregunta['ID_PREGUNTA']] = $pregunta['TEXTO'];
    }

    if (empty($preguntas)) continue;

    $hoja = $spreadsheet->createSheet();
    $hoja->setTitle(substr($nombreSeccion, 0, 31));
    $encabezados = array_merge(['CURP', 'Nombre'], array_values($preguntas));
    $hoja->fromArray($encabezados, null, 'A1');

    $idsPreguntas = implode(",", array_keys($preguntas));
    $sql = "
        SELECT e.CURP, e.NOMBRE, r.ID_PREGUNTA, r.ID_OPCION, r.RESPUESTA_TEXTO, o.TEXTO AS TEXTO_OPCION
        FROM RESPUESTA r
        INNER JOIN CUESTIONARIO_RESPUESTA cr ON cr.ID_CUESTIONARIO = r.ID_CUESTIONARIO
        INNER JOIN EGRESADO e ON e.CURP = cr.CURP
        LEFT JOIN OPCION_RESPUESTA o ON o.ID_OPCION = r.ID_OPCION
        WHERE r.ID_PREGUNTA IN ($idsPreguntas) AND e.CARRERA = ?
        ORDER BY e.CURP";
    $stmtRespuestas = $conexion->prepare($sql);
    $stmtRespuestas->bind_param("s", $carreraJefe);
    $stmtRespuestas->execute();
    $respuestas = $stmtRespuestas->get_result();

    $datosPorEgresado = [];
    while ($row = $respuestas->fetch_assoc()) {
        $curp = $row['CURP'];
        if (!isset($datosPorEgresado[$curp])) {
            $datosPorEgresado[$curp] = ['NOMBRE' => $row['NOMBRE'], 'RESPUESTAS' => []];
        }

        $respuesta = $row['TEXTO_OPCION'] ?? $row['RESPUESTA_TEXTO'] ?? '';
        $datosPorEgresado[$curp]['RESPUESTAS'][$row['ID_PREGUNTA']] = $respuesta;
    }

    $fila = 2;
    foreach ($datosPorEgresado as $curp => $info) {
        $filaData = [$curp, $info['NOMBRE']];
        foreach (array_keys($preguntas) as $idPregunta) {
            $filaData[] = $info['RESPUESTAS'][$idPregunta] ?? '';
        }
        $hoja->fromArray($filaData, null, "A$fila");
        $fila++;
    }
}

$hojaEgresados = $spreadsheet->createSheet();
$hojaEgresados->setTitle('Egresados');

$sqlEgresados = "
    SELECT CURP, NUM_CONTROl, NOMBRE, APELLIDO_PATERNO, APELLIDO_MATERNO, FECHA_NACIMIENTO, SEXO, ESTADO_CIVIL, 
           CALLE, COLONIA, CODIGO_POSTAL, CIUDAD, MUNICIPIO, ESTADO, EMAIL, CONTRASENA, 
           TELEFONO, CARRERA, FECHA_EGRESO, TITULADO, VERIFICADO 
    FROM EGRESADO 
    WHERE CARRERA = ?";
$stmtEgresados = $conexion->prepare($sqlEgresados);
$stmtEgresados->bind_param("s", $carreraJefe);
$stmtEgresados->execute();
$resEgresados = $stmtEgresados->get_result();

$hojaEgresados->fromArray([
    'CURP', 'No. Control','Nombre', 'Apellido Paterno', 'Apellido Materno', 'Fecha Nacimiento', 'Sexo', 'Estado Civil',
    'Calle', 'Colonia', 'Código Postal', 'Ciudad', 'Municipio', 'Estado', 'Correo', 'Contraseña', 
    'Teléfono', 'Carrera', 'Fecha de Egreso', 'Titulado', 'Verificado'
], null, 'A1');

$fila = 2;
while ($row = $resEgresados->fetch_assoc()) {
    $hojaEgresados->fromArray([
        $row['CURP'], $row['NUM_CONTROl'], $row['NOMBRE'], $row['APELLIDO_PATERNO'], $row['APELLIDO_MATERNO'], $row['FECHA_NACIMIENTO'],
        $row['SEXO'], $row['ESTADO_CIVIL'], $row['CALLE'], $row['COLONIA'], $row['CODIGO_POSTAL'],
        $row['CIUDAD'], $row['MUNICIPIO'], $row['ESTADO'], $row['EMAIL'], $row['CONTRASENA'],
        $row['TELEFONO'], $row['CARRERA'], $row['FECHA_EGRESO'], $row['TITULADO'], $row['VERIFICADO']
    ], null, "A$fila");
    $fila++;
}

if ($carreraJefe !== 'Ingeniería Química' && $carreraJefe !== 'Ingeniería Bioquímica') {
    $hojaEmpresas = $spreadsheet->createSheet();
    $hojaEmpresas->setTitle('Empresas');

    $sqlEmpresas = "
        SELECT e.CURP, emp.ID_EMPRESA, emp.TIPO_ORGANISMO, emp.GIRO, emp.RAZON_SOCIAL, 
               emp.CALLE, emp.NUMERO, emp.COLONIA, emp.CODIGO_POSTAL, emp.CIUDAD, emp.MUNICIPIO, 
               emp.ESTADO, emp.TELEFONO, emp.EMAIL, emp.PAGINA_WEB, emp.JEFE_INMEDIATO_NOMBRE, emp.JEFE_INMEDIATO_PUESTO
        FROM CUESTIONARIO_RESPUESTA cr
        INNER JOIN EGRESADO e ON e.CURP = cr.CURP
        INNER JOIN EMPRESA emp ON emp.ID_EMPRESA = cr.ID_EMPRESA
        WHERE e.CARRERA = ?";
    $stmtEmpresas = $conexion->prepare($sqlEmpresas);
    $stmtEmpresas->bind_param("s", $carreraJefe);
    $stmtEmpresas->execute();
    $resEmpresas = $stmtEmpresas->get_result();

    $hojaEmpresas->fromArray([
        'CURP', 'ID Empresa', 'Tipo de Organismo', 'Giro', 'Razón Social', 'Calle', 'Número', 'Colonia',
        'Código Postal', 'Ciudad', 'Municipio', 'Estado', 'Teléfono', 'Email', 'Página Web',
        'Jefe Inmediato Nombre', 'Jefe Inmediato Puesto'
    ], null, 'A1');

    $fila = 2;
    while ($row = $resEmpresas->fetch_assoc()) {
        $hojaEmpresas->fromArray([
            $row['CURP'], $row['ID_EMPRESA'], $row['TIPO_ORGANISMO'], $row['GIRO'], $row['RAZON_SOCIAL'],
            $row['CALLE'], $row['NUMERO'], $row['COLONIA'], $row['CODIGO_POSTAL'], $row['CIUDAD'],
            $row['MUNICIPIO'], $row['ESTADO'], $row['TELEFONO'], $row['EMAIL'], $row['PAGINA_WEB'],
            $row['JEFE_INMEDIATO_NOMBRE'], $row['JEFE_INMEDIATO_PUESTO']
        ], null, "A$fila");
        $fila++;
    }
}

$spreadsheet->setActiveSheetIndexByName('Egresados');

while (ob_get_level()) ob_end_clean();
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="reporte_completo_encuestas.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>
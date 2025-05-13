<?php
require '../vendor/autoload.php';
require('../db/conexion.php');
header('Content-Type: application/json');

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

if (!isset($_FILES['archivoExcel']) || $_FILES['archivoExcel']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['error' => 'No se recibió un archivo válido']);
    exit;
}

$archivo = $_FILES['archivoExcel']['tmp_name'];

try {
    $spreadsheet = IOFactory::load($archivo);
    $datos = [];

    function getColumnLetter($colIndex) {
        $letter = '';
        while ($colIndex > 0) {
            $colIndex--;
            $letter = chr($colIndex % 26 + 65) . $letter;
            $colIndex = floor($colIndex / 26);
        }
        return $letter;
    }

    function obtenerValorCeldaFormateada($celda) {
        $valor = $celda->getValue();

        if (Date::isDateTime($celda)) {
            try {
                $timestamp = Date::excelToTimestamp($valor);
                return date('Y-m-d', $timestamp);
            } catch (\Exception $e) {
                return null;
            }
        }

        return $valor;
    }

    // Obtener el mapeo pregunta -> ID_PREGUNTA
    function obtenerMapeoPreguntas($conexion) {
        $mapa = [];
        $res = mysqli_query($conexion, "SELECT ID_PREGUNTA, TEXTO FROM PREGUNTA");
        while ($row = mysqli_fetch_assoc($res)) {
            $mapa[$row['TEXTO']] = $row['ID_PREGUNTA'];
        }
        return $mapa;
    }

    // Obtener el mapeo ID_PREGUNTA + TEXTO_OPCION -> ID_OPCION
    function obtenerMapeoOpciones($conexion) {
        $mapa = [];
        $res = mysqli_query($conexion, "SELECT ID_OPCION, ID_PREGUNTA, TEXTO FROM OPCION_RESPUESTA");
        while ($row = mysqli_fetch_assoc($res)) {
            $clave = $row['ID_PREGUNTA'] . '|' . $row['TEXTO'];
            $mapa[$clave] = $row['ID_OPCION'];
        }
        return $mapa;
    }

    $mapaPreguntas = obtenerMapeoPreguntas($conexion);
    $mapaOpciones = obtenerMapeoOpciones($conexion);

    // EGRESADOS
    $hojaEgresados = $spreadsheet->getSheetByName('Egresados');
    if (!$hojaEgresados) throw new Exception("Falta hoja 'Egresados'");

    $egresadosDatos = [];
    $highestRow = $hojaEgresados->getHighestRow();
    $highestCol = $hojaEgresados->getHighestColumn();
    $colCount = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestCol);

    $headers = [];
    for ($c = 1; $c <= $colCount; $c++) {
        $headers[] = trim((string)$hojaEgresados->getCell(getColumnLetter($c) . '1')->getValue());
    }

    for ($r = 2; $r <= $highestRow; $r++) {
        $fila = [];
        for ($c = 1; $c <= $colCount; $c++) {
            $celda = $hojaEgresados->getCell(getColumnLetter($c) . $r);
            $fila[$headers[$c - 1]] = obtenerValorCeldaFormateada($celda);
        }
        $egresadosDatos[] = $fila;
    }
    $datos['Egresados'] = $egresadosDatos;

    // EMPRESAS
    $hojaEmpresas = $spreadsheet->getSheetByName('Empresas');
    if ($hojaEmpresas) {
        $empresasDatos = [];
        $highestRow = $hojaEmpresas->getHighestRow();
        $highestCol = $hojaEmpresas->getHighestColumn();
        $colCount = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestCol);

        $headers = [];
        for ($c = 1; $c <= $colCount; $c++) {
            $headers[] = trim((string)$hojaEmpresas->getCell(getColumnLetter($c) . '1')->getValue());
        }

        for ($r = 2; $r <= $highestRow; $r++) {
            $fila = [];
            for ($c = 1; $c <= $colCount; $c++) {
                $celda = $hojaEmpresas->getCell(getColumnLetter($c) . $r);
                $fila[$headers[$c - 1]] = obtenerValorCeldaFormateada($celda);
            }
            $empresasDatos[] = $fila;
        }
        $datos['Empresas'] = $empresasDatos;
    }

    // SECCIONES
    foreach ($spreadsheet->getSheetNames() as $hojaNombre) {
        if (in_array($hojaNombre, ['Egresados', 'Empresas'])) continue;

        $hoja = $spreadsheet->getSheetByName($hojaNombre);
        $highestRow = $hoja->getHighestRow();
        $highestCol = $hoja->getHighestColumn();
        $colCount = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestCol);

        $headers = [];
        for ($c = 1; $c <= $colCount; $c++) {
            $headers[] = trim((string)$hoja->getCell(getColumnLetter($c) . '1')->getValue());
        }

        $filas = [];
        for ($r = 2; $r <= $highestRow; $r++) {
            $fila = [];
            for ($c = 1; $c <= $colCount; $c++) {
                $encabezado = $headers[$c - 1];
                $celda = $hoja->getCell(getColumnLetter($c) . $r);
                $valor = obtenerValorCeldaFormateada($celda);

                // Si el encabezado es una pregunta
                if (isset($mapaPreguntas[$encabezado])) {
                    $idPregunta = $mapaPreguntas[$encabezado];
                    $clave = $idPregunta . '|' . $valor;
                    $fila[$idPregunta] = isset($mapaOpciones[$clave]) ? $mapaOpciones[$clave] : $valor;
                } else {
                    $fila[$encabezado] = $valor;
                }
            }
            $filas[] = $fila;
        }
        $datos[$hojaNombre] = $filas;
    }

    echo json_encode($datos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    echo json_encode(['error' => 'Error al procesar el archivo: ' . $e->getMessage()]);
}

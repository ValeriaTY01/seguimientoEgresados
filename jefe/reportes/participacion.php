<?php
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

use Dompdf\Dompdf;
use Dompdf\Options;

function generarReporteParticipacion($conexion, $carrera, $filtros) {
    if ($filtros['formato'] === 'pdf') {
        generarPDFParticipacion($conexion, $carrera, $filtros);
    } else {
        generarExcelParticipacion($conexion, $carrera, $filtros);
    }
}

function generarExcelParticipacion($conexion, $carrera, $filtros) {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    $encabezados = ['CURP', 'Nombre completo', 'Carrera', 'Sexo', 'Titulado', 'Año de Egreso', 'Estado', 'Fecha de Respuesta', 'Completado'];
    $col = 'A';
    foreach ($encabezados as $titulo) {
        $sheet->setCellValue($col . '1', $titulo);
        $col++;
    }

    // Estilo de encabezados
    $sheet->getStyle('A1:I1')->applyFromArray([
        'font' => ['bold' => true],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => 'D9D9D9']
        ],
        'borders' => [
            'allBorders' => ['borderStyle' => Border::BORDER_THIN]
        ],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
    ]);

    $egresados = obtenerEgresados($conexion, $carrera, $filtros);
    $respuestas = obtenerCUESTIONARIOsRespondidos($conexion, $filtros);

    $fila = 2;
    foreach ($egresados as $egresado) {
        $curp = $egresado['CURP'];
        $col = 'A';

        $sheet->setCellValue($col++ . $fila, $curp);
        $sheet->setCellValue($col++ . $fila, $egresado['NOMBRE_COMPLETO']);
        $sheet->setCellValue($col++ . $fila, $egresado['CARRERA']);
        $sheet->setCellValue($col++ . $fila, $egresado['SEXO']);
        $sheet->setCellValue($col++ . $fila, $egresado['TITULADO'] ? 'Sí' : 'No');
        $sheet->setCellValue($col++ . $fila, date('Y', strtotime($egresado['FECHA_EGRESO'])));

        if (isset($respuestas[$curp])) {
            $sheet->setCellValue($col++ . $fila, 'Respondió');
            $sheet->setCellValue($col++ . $fila, $respuestas[$curp]['FECHA_APLICACION']);
            $sheet->setCellValue($col++ . $fila, $respuestas[$curp]['COMPLETO'] ? 'Sí' : 'No');
        } else {
            $sheet->setCellValue($col++ . $fila, 'No respondió');
            $sheet->setCellValue($col++ . $fila, '');
            $sheet->setCellValue($col++ . $fila, '');
        }

        $fila++;
    }

    // Autoajustar columnas
    foreach (range('A', 'I') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    // Bordes
    $sheet->getStyle("A1:I" . ($fila - 1))->applyFromArray([
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
    ]);

    // Alineación centrada para columnas clave
    foreach (['D', 'E', 'G', 'I'] as $col) {
        $sheet->getStyle($col . '2:' . $col . $fila)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    }

    // Formato condicional: Estado
    for ($i = 2; $i < $fila; $i++) {
        $estado = $sheet->getCell("G$i")->getValue();
        if ($estado === 'Respondió') {
            $sheet->getStyle("G$i")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('C6EFCE'); // verde claro
        } elseif ($estado === 'No respondió') {
            $sheet->getStyle("G$i")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F4CCCC'); // rojo claro
        }
    }

    // Descargar Excel
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="estado_participacion.xlsx"');
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit();
}

function generarPDFParticipacion($conexion, $carrera, $filtros) {
    $egresados = obtenerEgresados($conexion, $carrera, $filtros);
    $respuestas = obtenerCUESTIONARIOsRespondidos($conexion, $filtros);
    $nombrePeriodo = obtenerNombrePeriodo($conexion, $filtros['periodo']);

    ob_start();
    ?>

    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; margin: 0; padding: 0; }
        .header {
            text-align: center;
            margin-bottom: 10px;
        }
        .titulo {
            text-align: center;
            font-weight: bold;
            font-size: 16px;
        }
        .info-periodo {
            text-align: center;
            font-size: 12px;
            margin-top: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            border: 1px solid #000;
            padding: 5px;
            text-align: center;
        }
        th {
            background-color: #e0e0e0;
        }
        .respondio {
            background-color: #d4edda;
        }
        .no-respondio {
            background-color: #f8d7da;
        }
        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }
    </style>

    <div class="header clearfix">
        <div class="titulo">Reporte: Estado de Participación</div>
        <div class="info-periodo">
            Periodo de encuesta: <strong><?= htmlspecialchars($nombrePeriodo) ?></strong><br>
            Carrera: <strong><?= htmlspecialchars($carrera) ?></strong>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>CURP</th>
                <th>Nombre completo</th>
                <th>Carrera</th>
                <th>Sexo</th>
                <th>Titulado</th>
                <th>Año de Egreso</th>
                <th>Estado</th>
                <th>Fecha de Respuesta</th>
                <th>Completado</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($egresados as $egresado):
                $curp = $egresado['CURP'];
                $respondio = isset($respuestas[$curp]);
                $estado = $respondio ? 'Respondió' : 'No respondió';
                $clase = $respondio ? 'respondio' : 'no-respondio';
                ?>
                <tr class="<?= $clase ?>">
                    <td><?= $curp ?></td>
                    <td><?= $egresado['NOMBRE_COMPLETO'] ?></td>
                    <td><?= $egresado['CARRERA'] ?></td>
                    <td><?= $egresado['SEXO'] ?></td>
                    <td><?= $egresado['TITULADO'] ? 'Sí' : 'No' ?></td>
                    <td><?= date('Y', strtotime($egresado['FECHA_EGRESO'])) ?></td>
                    <td><?= $estado ?></td>
                    <td><?= $respondio ? $respuestas[$curp]['FECHA_APLICACION'] : '' ?></td>
                    <td><?= $respondio ? ($respuestas[$curp]['COMPLETO'] ? 'Sí' : 'No') : '' ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <?php
    $html = ob_get_clean();

    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isRemoteEnabled', true); // por si usas logo remoto
    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'landscape');
    $dompdf->render();

    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="estado_participacion.pdf"');
    echo $dompdf->output();
    exit();
}

function obtenerNombrePeriodo($conexion, $idPeriodo) {
    $stmt = $conexion->prepare("SELECT NOMBRE FROM PERIODO_ENCUESTA WHERE ID_PERIODO = ?");
    $stmt->bind_param("i", $idPeriodo);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return $result ? $result['NOMBRE'] : 'Sin especificar';
}

// Obtener todos los egresados que aplican a los filtros
function obtenerEgresados($conexion, $carrera, $filtros) {
    $condiciones = "CARRERA = ?";
    $params = [$carrera];
    $tipos = "s";

    if (!empty($filtros['anio'])) {
        $condiciones .= " AND YEAR(FECHA_EGRESO) = ?";
        $params[] = $filtros['anio'];
        $tipos .= "i";
    }

    if (!empty($filtros['sexo'])) {
        $condiciones .= " AND SEXO = ?";
        $params[] = $filtros['sexo'];
        $tipos .= "s";
    }

    if ($filtros['titulado'] !== '') {
        $condiciones .= " AND TITULADO = ?";
        $params[] = $filtros['titulado'];
        $tipos .= "i";
    }

    $sql = "SELECT CURP, CONCAT(NOMBRE, ' ', APELLIDO_PATERNO, ' ', APELLIDO_MATERNO) AS NOMBRE_COMPLETO, 
                   CARRERA, SEXO, TITULADO, FECHA_EGRESO
            FROM EGRESADO
            WHERE $condiciones";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param($tipos, ...$params);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Obtener respuestas de cuestionario, con fecha y si fue completado
function obtenerCUESTIONARIOsRespondidos($conexion, $filtros) {
    $condiciones = "1=1";
    $params = [];
    $tipos = "";

    if (!empty($filtros['periodo'])) {
        $condiciones .= " AND ID_PERIODO = ?";
        $params[] = $filtros['periodo'];
        $tipos .= "i";
    }

    if (!empty($filtros['tipo'])) {
        $condiciones .= " AND TIPO = ?";
        $params[] = $filtros['tipo'];
        $tipos .= "s";
    }

    $sql = "SELECT CURP, FECHA_APLICACION, COMPLETO
            FROM CUESTIONARIO_RESPUESTA
            WHERE $condiciones";

    $stmt = $conexion->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($tipos, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    $respondidos = [];
    while ($row = $result->fetch_assoc()) {
        $respondidos[$row['CURP']] = [
            'FECHA_APLICACION' => $row['FECHA_APLICACION'],
            'COMPLETO' => $row['COMPLETO']
        ];
    }
    return $respondidos;
}

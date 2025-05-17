<?php
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Chart\Chart;
use PhpOffice\PhpSpreadsheet\Chart\Legend;
use PhpOffice\PhpSpreadsheet\Chart\PlotArea;
use PhpOffice\PhpSpreadsheet\Chart\Title;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
use PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues;

use Dompdf\Dompdf;
use Dompdf\Options;

function generarInformePorSeccion($conexion, $carrera, $filtros) {
    if ($filtros['formato'] === 'pdf') {
        generarPDFSeccion($conexion, $carrera, $filtros);
    } else {
        generarExcelSeccion($conexion, $carrera, $filtros);
    }
}

function generarExcelSeccion($conexion, $carrera, $filtros) {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Por Sección');
    $row = 1;

    $periodos = obtenerNombrePeriodo($conexion, $filtros['periodo']);

    // Validar que 'seccion' esté definido y no sea null
    $seccion = null;
    if (!empty($filtros['seccion'])) {
        $seccion = obtenerSeccionPorId($conexion, $filtros['seccion']);
    }

    if (!$seccion) {
        die('Error: Sección no válida o no especificada.');
    }

    $sheet->mergeCells("A$row:D$row");
    $sheet->setCellValue("A$row", 'Informe por Sección');
    $sheet->getStyle("A$row")->getFont()->setBold(true)->setSize(16);
    $sheet->getStyle("A$row")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $row += 2;

    $info = [
        'Período de Encuesta' => $periodos,
        'Carrera' => $carrera,
        'Sección' => $seccion['NOMBRE']
    ];
    if (!empty($filtros['anio'])) $info['Año'] = $filtros['anio'];
    if (!empty($filtros['sexo'])) $info['Sexo'] = $filtros['sexo'];
    if ($filtros['titulado'] !== '') $info['Titulado'] = $filtros['titulado'] ? 'Sí' : 'No';

    foreach ($info as $label => $value) {
        $sheet->setCellValue("A$row", $label . ':');
        $sheet->setCellValue("B$row", $value);
        $sheet->getStyle("A$row")->getFont()->setBold(true);
        $row++;
    }

    $row++;

    $preguntas = obtenerPreguntasPorSeccion($conexion, $seccion['ID_SECCION']);
    foreach ($preguntas as $pregunta) {
        $sheet->setCellValue("A$row", $pregunta['TEXTO']);
        $sheet->getStyle("A$row")->getFont()->setBold(true)->setSize(12);
        $row++;

        $sheet->setCellValue("A$row", "Opción");
        $sheet->setCellValue("B$row", "Frecuencia");
        $sheet->setCellValue("C$row", "Total");
        $sheet->getStyle("A$row:C$row")->getFont()->setBold(true);
        $sheet->getStyle("A$row:C$row")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFD9D9D9');
        $sheet->getStyle("A$row:C$row")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle("A$row:C$row")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $row++;

        $inicioDatos = $row;
        $resultados = obtenerResultadosPregunta($conexion, $pregunta['ID_PREGUNTA'], $filtros);
        foreach ($resultados as $respuesta) {
            $sheet->setCellValue("A$row", $respuesta['opcion']);
            $sheet->setCellValue("B$row", $respuesta['frecuencia']);
            $sheet->setCellValue("C$row", $respuesta['total']);
            $sheet->getStyle("A$row:C$row")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle("B$row:C$row")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $row++;
        }

        $finDatos = $row - 1;

        if ($finDatos >= $inicioDatos) {
            $labels = [
                new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, "'Por Sección'!\$A\$$inicioDatos:\$A\$$finDatos", null, ($finDatos - $inicioDatos + 1))
            ];
            $values = [
                new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_NUMBER, "'Por Sección'!\$B\$$inicioDatos:\$B\$$finDatos", null, ($finDatos - $inicioDatos + 1))
            ];

            $series = new DataSeries(DataSeries::TYPE_PIECHART, null, range(0, count($values) - 1), [], $labels, $values);
            $plotArea = new PlotArea(null, [$series]);
            $chartTitle = new Title($pregunta['TEXTO']);
            $chart = new Chart('grafica_' . $pregunta['ID_PREGUNTA'], $chartTitle, new Legend(Legend::POSITION_RIGHT, null, false), $plotArea, true, 0, null, null);

            $rowChartStart = $row + 1;
            $rowChartEnd = $rowChartStart + 15;
            $chart->setTopLeftPosition('A' . $rowChartStart);
            $chart->setBottomRightPosition('D' . $rowChartEnd);
            $sheet->addChart($chart);
            $row = $rowChartEnd + 2;
        } else {
            $row++;
        }
    }

    foreach (['A', 'B', 'C'] as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="reporte_por_seccion.xlsx"');
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->setIncludeCharts(true);
    $writer->save('php://output');
    exit();
}
function generarPDFSeccion($conexion, $carrera, $filtros) {
    $periodos = obtenerNombrePeriodo($conexion, $filtros['periodo']);

    // Validar sección
    $seccion = null;
    if (!empty($filtros['seccion'])) {
        $seccion = obtenerSeccionPorId($conexion, $filtros['seccion']);
    }
    if (!$seccion) {
        die('Error: Sección no válida o no especificada.');
    }

    $html = '<h2 style="text-align:center;">Informe por Sección</h2>';
    $html .= '<p><strong>Período de Encuesta:</strong> ' . htmlspecialchars($periodos) . '</p>';
    $html .= '<p><strong>Carrera:</strong> ' . htmlspecialchars($carrera) . '</p>';
    $html .= '<p><strong>Sección:</strong> ' . htmlspecialchars($seccion['NOMBRE']) . '</p>';

    if (!empty($filtros['anio'])) {
        $html .= '<p><strong>Año:</strong> ' . htmlspecialchars($filtros['anio']) . '</p>';
    }

    if (!empty($filtros['sexo'])) {
        $html .= '<p><strong>Sexo:</strong> ' . htmlspecialchars($filtros['sexo']) . '</p>';
    }

    if ($filtros['titulado'] !== '') {
        $html .= '<p><strong>Titulado:</strong> ' . ($filtros['titulado'] ? 'Sí' : 'No') . '</p>';
    }

    $preguntas = obtenerPreguntasPorSeccion($conexion, $seccion['ID_SECCION']);
    foreach ($preguntas as $pregunta) {
        $html .= '<p><strong>' . htmlspecialchars($pregunta['TEXTO']) . '</strong></p>';
        $html .= '<table border="1" cellpadding="5" cellspacing="0" width="100%">';
        $html .= '<tr><th>Opción</th><th>Frecuencia</th><th>Total</th></tr>';

        $resultados = obtenerResultadosPregunta($conexion, $pregunta['ID_PREGUNTA'], $filtros);

        $labels = [];
        $values = [];

        foreach ($resultados as $respuesta) {
            $html .= '<tr>';
            $html .= '<td>' . htmlspecialchars($respuesta['opcion']) . '</td>';
            $html .= '<td>' . $respuesta['frecuencia'] . '</td>';
            $html .= '<td>' . $respuesta['total'] . '</td>';
            $html .= '</tr>';

            $labels[] = $respuesta['opcion'];
            $values[] = $respuesta['frecuencia'];
        }
        $html .= '</table><br>';

        // Agregar gráfica si hay datos
        if (!empty($labels) && !empty($values)) {
            $chartConfig = [
                'type' => 'bar',
                'data' => [
                    'labels' => $labels,
                    'datasets' => [[
                        'label' => $pregunta['TEXTO'],
                        'data' => $values,
                        'backgroundColor' => 'rgba(54, 162, 235, 0.7)'
                    ]]
                ],
                'options' => [
                    'plugins' => ['legend' => false],
                    'scales' => [
                        'y' => ['beginAtZero' => true]
                    ]
                ]
            ];

            $url = 'https://quickchart.io/chart?c=' . urlencode(json_encode($chartConfig));
            $imgData = @file_get_contents($url);

            if ($imgData !== false) {
                $base64 = base64_encode($imgData);
                $html .= '<div style="text-align:center; margin-bottom: 20px;"><img src="data:image/png;base64,' . $base64 . '" style="width:350px; height:auto;" /></div>';
            } else {
                $html .= '<p><em>Error al generar la gráfica.</em></p><br>';
            }
        } else {
            $html .= '<p><em>No hay datos suficientes para generar una gráfica.</em></p><br>';
        }
    }

    $options = new \Dompdf\Options();
    $options->set('isRemoteEnabled', true);
    $dompdf = new \Dompdf\Dompdf($options);

    // Carga un CSS simple si quieres, aquí puedes poner estilos para tablas, textos, etc.
    $css = file_exists(__DIR__ . '/por_seccion_pdf.css') ? file_get_contents(__DIR__ . '/por_seccion_pdf.css') : '';

    $dompdf->loadHtml('
        <html>
        <head>
            <style>' . $css . '</style>
        </head>
        <body>' . $html . '</body>
        </html>
    ');

    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    $dompdf->stream("reporte_por_seccion.pdf", ["Attachment" => true]);
    exit();
}

function obtenerNombrePeriodo($conexion, $idPeriodo) {
    $stmt = $conexion->prepare("SELECT NOMBRE FROM PERIODO_ENCUESTA WHERE ID_PERIODO = ?");
    $stmt->bind_param("i", $idPeriodo);
    $stmt->execute();
    $stmt->bind_result($nombre);
    $stmt->fetch();
    $stmt->close();
    return $nombre ?: 'No especificado';
}

function obtenerSeccionPorId($conexion, $idSeccion) {
    $stmt = $conexion->prepare("SELECT ID_SECCION, NOMBRE FROM SECCION WHERE ID_SECCION = ?");
    $stmt->bind_param("i", $idSeccion);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $seccion = $resultado->fetch_assoc();
    $stmt->close();

    if (!$seccion) {
        return null;  // Retorna null si no encontró la sección
    }

    return $seccion;
}

function obtenerPreguntasPorSeccion($conexion, $idSeccion) {
    if (empty($idSeccion) || !is_numeric($idSeccion)) {
        return [];  // Retorna arreglo vacío si id no es válido
    }
    $idSeccion = intval($idSeccion);

    $query = "
        SELECT ID_PREGUNTA, TEXTO 
        FROM PREGUNTA 
        WHERE ID_SECCION = $idSeccion AND TIPO != 'texto' 
        ORDER BY ORDEN
    ";

    $resultado = $conexion->query($query);
    if ($resultado) {
        return $resultado->fetch_all(MYSQLI_ASSOC);
    }
    return [];
}

function obtenerResultadosPregunta($conexion, $idPregunta, $filtros) {
    $condiciones = "";
    $condicionesSubconsulta = "";

    // Año
    if (!empty($filtros['anio'])) {
        $anio = intval($filtros['anio']);
        $condiciones .= " AND YEAR(e.FECHA_EGRESO) = $anio";
        $condicionesSubconsulta .= " AND YEAR(e2.FECHA_EGRESO) = $anio";
    }

    // Sexo
    if (!empty($filtros['sexo'])) {
        $sexo = $conexion->real_escape_string($filtros['sexo']);
        $condiciones .= " AND e.SEXO = '$sexo'";
        $condicionesSubconsulta .= " AND e2.SEXO = '$sexo'";
    }

    // Titulado
    if ($filtros['titulado'] !== '') {
        $titulado = intval($filtros['titulado']);
        $condiciones .= " AND e.TITULADO = $titulado";
        $condicionesSubconsulta .= " AND e2.TITULADO = $titulado";
    }

    // Carrera
    $carrera = $conexion->real_escape_string($filtros['carrera']);
    $condiciones .= " AND e.CARRERA = '$carrera'";
    $condicionesSubconsulta .= " AND e2.CARRERA = '$carrera'";

    // Periodo
    if (!empty($filtros['periodo'])) {
        $periodo = intval($filtros['periodo']);
        $condiciones .= " AND cr.ID_PERIODO = $periodo";
        $condicionesSubconsulta .= " AND cr2.ID_PERIODO = $periodo";
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
                WHERE r2.ID_PREGUNTA = $idPregunta
                $condicionesSubconsulta
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

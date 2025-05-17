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
use PhpOffice\PhpSpreadsheet\Chart\XAxis;
use PhpOffice\PhpSpreadsheet\Chart\YAxis;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
use PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues;

use Dompdf\Dompdf;
use Dompdf\Options;

function generarInformeEstadistico($conexion, $carrera, $filtros) {
    if ($filtros['formato'] === 'pdf') {
        generarPDF($conexion, $carrera, $filtros);
    } else {
        generarExcel($conexion, $carrera, $filtros);
    }
}

function generarExcel($conexion, $carrera, $filtros) {
    $spreadsheet = new Spreadsheet();
    $periodos = obtenerNombrePeriodo($conexion, $filtros['periodo']);

    $sheetMain = $spreadsheet->getActiveSheet();
    $sheetMain->setTitle('Resumen');
    $row = 1;

    $sheetMain->mergeCells("A$row:D$row");
    $sheetMain->setCellValue("A$row", 'Informe Estadístico');
    $sheetMain->getStyle("A$row")->getFont()->setBold(true)->setSize(16);
    $sheetMain->getStyle("A$row")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $row += 2;

    $info = [
        'Período de Encuesta' => $periodos,
        'Carrera' => $carrera
    ];
    if (!empty($filtros['anio'])) $info['Año'] = $filtros['anio'];
    if (!empty($filtros['sexo'])) $info['Sexo'] = $filtros['sexo'];
    if ($filtros['titulado'] !== '') $info['Titulado'] = $filtros['titulado'] ? 'Sí' : 'No';

    foreach ($info as $label => $value) {
        $sheetMain->setCellValue("A$row", $label . ':');
        $sheetMain->setCellValue("B$row", $value);
        $sheetMain->getStyle("A$row")->getFont()->setBold(true);
        $row++;
    }

    foreach (['A', 'B'] as $col) {
        $sheetMain->getColumnDimension($col)->setAutoSize(true);
    }

    $secciones = obtenerSecciones($conexion, $carrera);
    foreach ($secciones as $index => $seccion) {
        $sheet = $spreadsheet->createSheet($index + 1);
        $nombreHoja = substr($seccion['NOMBRE'], 0, 31);
        $sheet->setTitle($nombreHoja);

        $fila = 1;
        $sheet->setCellValue("A$fila", "Sección: {$seccion['NOMBRE']}");
        $sheet->getStyle("A$fila")->getFont()->setBold(true)->setSize(14);
        $fila += 2;

        $preguntas = obtenerPreguntasPorSeccion($conexion, $seccion['ID_SECCION']);
        foreach ($preguntas as $pregunta) {
            $sheet->setCellValue("A$fila", $pregunta['TEXTO']);
            $sheet->getStyle("A$fila")->getFont()->setBold(true)->setSize(12);
            $fila++;

            $sheet->setCellValue("A$fila", "Opción");
            $sheet->setCellValue("B$fila", "Frecuencia");
            $sheet->setCellValue("C$fila", "Total");
            $sheet->getStyle("A$fila:C$fila")->getFont()->setBold(true);
            $sheet->getStyle("A$fila:C$fila")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFD9D9D9');
            $sheet->getStyle("A$fila:C$fila")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle("A$fila:C$fila")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $fila++;

            $inicioDatos = $fila;
            $resultados = obtenerResultadosPregunta($conexion, $pregunta['ID_PREGUNTA'], $filtros);
            foreach ($resultados as $respuesta) {
                $sheet->setCellValue("A$fila", $respuesta['opcion']);
                $sheet->setCellValue("B$fila", $respuesta['frecuencia']);
                $sheet->setCellValue("C$fila", $respuesta['total']);
                $sheet->getStyle("A$fila:C$fila")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $sheet->getStyle("B$fila:C$fila")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $fila++;
            }
            $finDatos = $fila - 1;

            if ($finDatos >= $inicioDatos) {
                $labels = [
                    new DataSeriesValues(
                        DataSeriesValues::DATASERIES_TYPE_STRING,
                        "'$nombreHoja'!\$A\$$inicioDatos:\$A\$$finDatos",
                        null,
                        ($finDatos - $inicioDatos + 1)
                    )
                ];
                $values = [
                    new DataSeriesValues(
                        DataSeriesValues::DATASERIES_TYPE_NUMBER,
                        "'$nombreHoja'!\$B\$$inicioDatos:\$B\$$finDatos",
                        null,
                        ($finDatos - $inicioDatos + 1)
                    )
                ];

                $series = new DataSeries(
                    DataSeries::TYPE_PIECHART,
                    null,
                    range(0, count($values) - 1),
                    [],
                    $labels,
                    $values
                );

                $plotArea = new PlotArea(null, [$series]);
                
                $chartTitle = new Title($pregunta['TEXTO']);

                $chart = new Chart(
                    'grafica_' . $pregunta['ID_PREGUNTA'],
                    $chartTitle,
                    new Legend(Legend::POSITION_RIGHT, null, false),
                    $plotArea,
                    true,
                    0,
                    null,
                    null
                );

                $filaGraficaInicio = $fila + 1;
                $filaGraficaFin = $filaGraficaInicio + 15;

                $chart->setTopLeftPosition('A' . $filaGraficaInicio);
                $chart->setBottomRightPosition('D' . $filaGraficaFin);

                $sheet->addChart($chart);

                $fila = $filaGraficaFin + 2;
            } else {
            $fila++;
        }
    }

        foreach (['A', 'B', 'C'] as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="reporte_estadistico.xlsx"');
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->setIncludeCharts(true);
    $writer->save('php://output');
    exit();
}

function generarPDF($conexion, $carrera, $filtros) {
    $periodos = obtenerNombrePeriodo($conexion, $filtros['periodo']);

    $html = '<h2 style="text-align:center;">Informe Estadístico</h2>';
    $html .= '<p><strong>Período de Encuesta:</strong> ' . htmlspecialchars($periodos) . '</p>';
    $html .= '<p><strong>Carrera:</strong> ' . htmlspecialchars($carrera) . '</p>';

    if (!empty($filtros['anio'])) {
        $html .= '<p><strong>Año:</strong> ' . htmlspecialchars($filtros['anio']) . '</p>';
    }

    if (!empty($filtros['sexo'])) {
        $html .= '<p><strong>Sexo:</strong> ' . htmlspecialchars($filtros['sexo']) . '</p>';
    }

    if ($filtros['titulado'] !== '') {
        $html .= '<p><strong>Titulado:</strong> ' . ($filtros['titulado'] ? 'Sí' : 'No') . '</p>';
    }

    $secciones = obtenerSecciones($conexion, $carrera);
    foreach ($secciones as $seccion) {
        $html .= '<h3>Sección: ' . htmlspecialchars($seccion['NOMBRE']) . '</h3>';

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

                $imgData = file_get_contents($url);
                if ($imgData !== false) {
                    $base64 = base64_encode($imgData);
                    $html .= '<div style="text-align:center;"><img src="data:image/png;base64,' . $base64 . '" style="width:350px; height:auto;" /></div><br>';

                } else {
                    $html .= '<p><em>Error al generar la gráfica.</em></p><br>';
                }
            } else {
                $html .= '<p><em>No hay datos suficientes para generar una gráfica.</em></p><br>';
            }
        }
    }

    $css = file_get_contents(__DIR__ . '/estadistico_pdf.css');

    $options = new Options();
    $options->set('isRemoteEnabled', true);
    $dompdf = new Dompdf($options);


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

    $dompdf->stream('reporte_estadistico.pdf', ['Attachment' => true]);
    exit();
}

function generarGraficaPNG($titulo, $datos, $rutaImagen) {
    if (empty($datos)) return;

    $labels = [];
    $valores = [];
    $colores = [];

    foreach ($datos as $d) {
        $labels[] = $d['opcion'];
        $valores[] = $d['frecuencia'];

        $colores[] = 'rgba(' . rand(50, 200) . ',' . rand(100, 200) . ',' . rand(200, 255) . ',0.7)';
    }

    $chartConfig = [
        'type' => 'bar',
        'data' => [
            'labels' => $labels,
            'datasets' => [[
                'label' => $titulo,
                'data' => $valores,
                'backgroundColor' => $colores,
            ]]
        ],
        'options' => [
            'responsive' => false,
            'plugins' => [
                'legend' => ['display' => false],
                'title' => [
                    'display' => true,
                    'text' => $titulo,
                    'font' => ['size' => 18]
                ]
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => ['precision' => 0]
                ]
            ]
        ]
    ];

    $url = 'https://quickchart.io/chart';
    $query = http_build_query([
        'width' => 800,
        'height' => 400,
        'format' => 'png',
        'c' => json_encode($chartConfig)
    ]);

    $imagen = file_get_contents("$url?$query");
    if ($imagen !== false) {
        file_put_contents($rutaImagen, $imagen);
    }
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

function obtenerSecciones($conexion, $carrera) {
    $esQuimica = in_array(strtolower($carrera), ['ingeniería química', 'ingeniería bioquímica', 'quimica']);
    $sql = $esQuimica ?
        "SELECT ID_SECCION, NOMBRE FROM SECCION WHERE PARA_CARRERA = 'quimica' AND ID_SECCION NOT IN (1,7) ORDER BY ORDEN" :
        "SELECT ID_SECCION, NOMBRE FROM SECCION WHERE PARA_CARRERA IS NULL AND ID_SECCION NOT IN (1,7) ORDER BY ORDEN";
    return $conexion->query($sql)->fetch_all(MYSQLI_ASSOC);
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
    $condicionesSubconsulta = "";

    // Año de egreso
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

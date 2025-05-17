<?php
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Chart\Chart;
use PhpOffice\PhpSpreadsheet\Chart\Legend;
use PhpOffice\PhpSpreadsheet\Chart\PlotArea;
use PhpOffice\PhpSpreadsheet\Chart\Title;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
use PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues;

use Dompdf\Dompdf;
use Dompdf\Options;

function generarInformeHistorico($conexion, $carrera, $filtros) {
    if ($filtros['formato'] === 'pdf') {
        generarComparativoHistoricoPDF($conexion, $carrera, $filtros);
    } else {
        generarComparativoHistoricoExcel($conexion, $carrera, $filtros);
    }
}

function generarComparativoHistoricoExcel($conexion, $carrera, $filtros) {
    $spreadsheet = new Spreadsheet();
    $spreadsheet->removeSheetByIndex(0); // Quitamos la hoja por defecto

    $secciones = obtenerSecciones($conexion, $carrera);
    $anios = obtenerAniosDeEgreso($conexion, $carrera, $filtros);

    foreach ($secciones as $seccion) {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle(substr($seccion['NOMBRE'], 0, 31));
        $fila = 1;

        $preguntas = obtenerPreguntasPorSeccion($conexion, $seccion['ID_SECCION']);
        foreach ($preguntas as $pregunta) {
            $resumen = obtenerResumenPorPreguntaYAnio($conexion, $pregunta['ID_PREGUNTA'], $carrera, $filtros);

            // Título pregunta
            $sheet->setCellValue("A$fila", $pregunta['TEXTO']);
            $sheet->mergeCells("A$fila:F$fila");
            $sheet->getStyle("A$fila")->getFont()->setBold(true)->setSize(12);
            $fila++;

            // Encabezados tabla
            $sheet->setCellValue("A$fila", "Opción");
            $col = 'B';
            foreach ($anios as $anio) {
                $sheet->setCellValue($col++ . $fila, $anio);
            }
            $sheet->getStyle("A$fila:" . chr(ord('A') + count($anios)) . "$fila")->applyFromArray(estiloEncabezado());
            $fila++;

            // Opciones y porcentajes
            $opciones = obtenerOpcionesDePregunta($conexion, $pregunta['ID_PREGUNTA']);
            $inicioDatos = $fila;

            foreach ($opciones as $opcion) {
                $col = 'A';
                $sheet->setCellValue($col++ . $fila, $opcion);
                foreach ($anios as $anio) {
                    $cantidad = $resumen[$anio][$opcion] ?? 0;
                    $total = array_sum($resumen[$anio] ?? []);
                    $porcentaje = $total > 0 ? round($cantidad * 100 / $total, 2) : 0;
                    $sheet->setCellValue($col++ . $fila, $porcentaje);
                }
                $fila++;
            }
            $finDatos = $fila - 1;

            // Aplicar estilo filas alternas para la tabla de datos
            for ($i = $inicioDatos; $i <= $finDatos; $i++) {
                $sheet->getStyle("A$i:" . chr(ord('A') + count($anios)) . "$i")->applyFromArray(estiloFilaAlterna($i));
            }

            $fila++; // Espacio debajo de la tabla

            // Crear gráfico de líneas
            $dataSeriesValues = [];
            for ($i = 0; $i < count($opciones); $i++) {
                $row = $inicioDatos + $i;
                $rango = "'{$sheet->getTitle()}'!" . "B$row:" . chr(ord('A') + count($anios)) . "$row";
                $dataSeriesValues[] = new DataSeriesValues('Number', $rango, null, count($anios));
            }

            $labels = [];
            for ($i = 0; $i < count($opciones); $i++) {
                $labels[] = new DataSeriesValues('String', "'{$sheet->getTitle()}'!A" . ($inicioDatos + $i), null, 1);
            }

            $categories = [new DataSeriesValues(
                'String',
                "'{$sheet->getTitle()}'!B" . ($inicioDatos - 1) . ":" . chr(ord('A') + count($anios)) . ($inicioDatos - 1),
                null,
                count($anios)
            )];

            $series = new DataSeries(
                DataSeries::TYPE_LINECHART,
                DataSeries::GROUPING_STANDARD,
                range(0, count($opciones) - 1),
                $labels,
                $categories,
                $dataSeriesValues
            );

            $plotArea = new PlotArea(null, [$series]);
            $legend = new Legend(Legend::POSITION_RIGHT, null, false);
            $title = new Title($pregunta['TEXTO']);
            $yAxisLabel = new Title('Porcentaje (%)');

            $chart = new Chart(
                'chart_' . $pregunta['ID_PREGUNTA'],
                $title,
                $legend,
                $plotArea,
                true,
                DataSeries::EMPTY_AS_GAP,
                null,
                $yAxisLabel
            );

            $chartTopLeft = 'B' . ($fila);
            $chartBottomRight = 'J' . ($fila + 15);
            $chart->setTopLeftPosition($chartTopLeft);
            $chart->setBottomRightPosition($chartBottomRight);

            $sheet->addChart($chart);
            $fila += 17; // Mover fila para próxima pregunta
        }
    }

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="comparativo_historico.xlsx"');
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->setIncludeCharts(true);
    $writer->save('php://output');
    exit();
}

// Estilo encabezado azul oscuro y texto blanco en negrita
function estiloEncabezado() {
    return [
        'font' => [
            'bold' => true,
            'color' => ['rgb' => 'FFFFFF'],
            'size' => 12,
        ],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => '1F4E78'], // Azul oscuro
        ],
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER,
            'vertical' => Alignment::VERTICAL_CENTER,
        ],
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['rgb' => '000000'],
            ],
        ],
    ];
}

// Estilo filas alternas para mejor legibilidad
function estiloFilaAlterna($fila) {
    if ($fila % 2 == 0) {
        return [
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'D9E1F2'], // Azul claro
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ];
    } else {
        return [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ];
    }
}

function generarComparativoHistoricoPDF($conexion, $carrera, $filtros) {
    set_time_limit(300);

    $periodoNombre = obtenerNombrePeriodo($conexion, $filtros['periodo'] ?? null);

    $html = '<h2 style="text-align:center;">Informe Comparativo Histórico</h2>';
    $html .= '<p><strong>Período de Encuesta:</strong> ' . htmlspecialchars($periodoNombre) . '</p>';
    $html .= '<p><strong>Carrera:</strong> ' . htmlspecialchars($carrera) . '</p>';

    if (!empty($filtros['anio'])) {
        $html .= '<p><strong>Año:</strong> ' . htmlspecialchars($filtros['anio']) . '</p>';
    }

    if (!empty($filtros['sexo'])) {
        $html .= '<p><strong>Sexo:</strong> ' . htmlspecialchars($filtros['sexo']) . '</p>';
    }

    if (isset($filtros['titulado'])) {
        $html .= '<p><strong>Titulado:</strong> ' . ($filtros['titulado'] ? 'Sí' : 'No') . '</p>';
    }

    $secciones = obtenerSecciones($conexion, $carrera);
    $anios = obtenerAniosDeEgreso($conexion, $carrera, $filtros);

    foreach ($secciones as $seccion) {
        $html .= '<h3>Sección: ' . htmlspecialchars($seccion['NOMBRE']) . '</h3>';

        $preguntas = obtenerPreguntasPorSeccion($conexion, $seccion['ID_SECCION']);
        foreach ($preguntas as $pregunta) {
            $html .= '<p><strong>' . htmlspecialchars($pregunta['TEXTO']) . '</strong></p>';
            $html .= '<table border="1" cellpadding="5" cellspacing="0" width="100%">';
            $html .= '<tr><th>Opción</th>';

            foreach ($anios as $anio) {
                $html .= '<th>' . htmlspecialchars($anio) . '</th>';
            }
            $html .= '</tr>';

            $resumen = obtenerResumenPorPreguntaYAnio($conexion, $pregunta['ID_PREGUNTA'], $carrera, $filtros);
            $opciones = obtenerOpcionesDePregunta($conexion, $pregunta['ID_PREGUNTA']);

            $labels = $anios;
            $datasets = [];

            foreach ($opciones as $opcionIndex => $opcion) {
                $html .= '<tr>';
                $html .= '<td>' . htmlspecialchars($opcion) . '</td>';

                $datos = [];

                foreach ($anios as $anio) {
                    $cantidad = $resumen[$anio][$opcion] ?? 0;
                    $total = array_sum($resumen[$anio] ?? []);
                    $porcentaje = $total > 0 ? round($cantidad * 100 / $total, 2) : 0;

                    $html .= '<td style="text-align:center;">' . $porcentaje . '%</td>';
                    $datos[] = $porcentaje;
                }

                $html .= '</tr>';

                $datasets[] = [
                    'label' => $opcion,
                    'data' => $datos,
                    'fill' => false,
                    'borderColor' => randomColorRGBA(),
                    'tension' => 0.1
                ];
            }

            $html .= '</table><br>';

            // Generar gráfica
            if (!empty($datasets)) {
                $chartConfig = [
                    'type' => 'line',
                    'data' => [
                        'labels' => $labels,
                        'datasets' => $datasets
                    ],
                    'options' => [
                        'plugins' => ['legend' => ['display' => true]],
                        'scales' => [
                            'y' => [
                                'beginAtZero' => true,
                                'max' => 100,
                                'ticks' => ['callback' => 'function(value){return value + "%";}']
                            ]
                        ]
                    ]
                ];

                $url = 'https://quickchart.io/chart?c=' . urlencode(json_encode($chartConfig)) . '&width=350&height=200&format=png';
                $imgData = @file_get_contents($url);

                if ($imgData !== false) {
                    $base64 = base64_encode($imgData);
                    $html .= '<div style="text-align:center; margin-bottom:30px;">';
                    $html .= '<img src="data:image/png;base64,' . $base64 . '" style="width:350px; height:auto;">';
                    $html .= '</div>';
                } else {
                    $html .= '<p><em>No se pudo generar la gráfica.</em></p><br>';
                }
            } else {
                $html .= '<p><em>No hay datos suficientes para la gráfica.</em></p><br>';
            }
        }
    }

    $cssPath = __DIR__ . '/estadistico_pdf.css';
    $css = file_exists($cssPath) ? file_get_contents($cssPath) : '';

    $options = new Options();
    $options->setIsRemoteEnabled(true);
    $options->setIsHtml5ParserEnabled(true);

    $dompdf = new Dompdf($options);
    $dompdf->loadHtml('
        <html>
        <head><style>' . $css . '</style></head>
        <body>' . $html . '</body>
        </html>
    ');

    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    $dompdf->stream('comparativo_historico.pdf', ['Attachment' => true]);
    exit();
}

// Función para generar colores RGBA aleatorios para líneas de gráfico
function randomColorRGBA() {
    $r = rand(50, 200);
    $g = rand(50, 200);
    $b = rand(50, 200);
    return "rgba($r,$g,$b,0.8)";
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

function obtenerAniosDeEgreso($conexion, $carrera, $filtros) {
    $sql = "SELECT DISTINCT YEAR(E.FECHA_EGRESO) AS anio
            FROM EGRESADO E
            INNER JOIN CUESTIONARIO_RESPUESTA C ON E.CURP = C.CURP
            WHERE E.CARRERA = ? AND C.COMPLETO = 0";
    $params = [$carrera];
    $tipos = "s";

    if (!empty($filtros['periodo'])) {
        $sql .= " AND C.ID_PERIODO = ?";
        $params[] = $filtros['periodo'];
        $tipos .= "i";
    }

    $sql .= " ORDER BY anio";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param($tipos, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    $anios = [];
    while ($row = $result->fetch_assoc()) {
        $anios[] = $row['anio'];
    }
    return $anios;
}

function obtenerResumenPorPreguntaYAnio($conexion, $idPregunta, $carrera, $filtros) {
    $sql = "
        SELECT YEAR(E.FECHA_EGRESO) AS anio, 
               COALESCE(O.TEXTO, R.RESPUESTA_TEXTO) AS opcion, 
               COUNT(*) AS cantidad
        FROM RESPUESTA R
        INNER JOIN CUESTIONARIO_RESPUESTA C ON R.ID_CUESTIONARIO = C.ID_CUESTIONARIO
        INNER JOIN EGRESADO E ON C.CURP = E.CURP
        LEFT JOIN OPCION_RESPUESTA O ON R.ID_OPCION = O.ID_OPCION
        WHERE R.ID_PREGUNTA = ? AND E.CARRERA = ? AND C.COMPLETO = 0";

    $params = [$idPregunta, $carrera];
    $tipos = "is";

    if (!empty($filtros['periodo'])) {
        $sql .= " AND C.ID_PERIODO = ?";
        $params[] = $filtros['periodo'];
        $tipos .= "i";
    }

    $sql .= " GROUP BY anio, opcion ORDER BY anio";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param($tipos, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    $resumen = [];
    while ($row = $result->fetch_assoc()) {
        $anio = $row['anio'];
        $opcion = $row['opcion'];
        $resumen[$anio][$opcion] = $row['cantidad'];
    }
    return $resumen;
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

function obtenerOpcionesDePregunta($conexion, $idPregunta) {
    $sql = "SELECT TEXTO FROM OPCION_RESPUESTA WHERE ID_PREGUNTA = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $idPregunta);
    $stmt->execute();
    $result = $stmt->get_result();
    $opciones = [];
    while ($row = $result->fetch_assoc()) {
        $opciones[] = $row['TEXTO'];
    }
    return $opciones;
}

<?php
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Chart\Chart;
use PhpOffice\PhpSpreadsheet\Chart\Legend;
use PhpOffice\PhpSpreadsheet\Chart\PlotArea;
use PhpOffice\PhpSpreadsheet\Chart\Title;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
use PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues;

function generarInformeHistoricoAcumulado($conexion, $carrera, $filtros) {
    $anio     = $filtros['anio'];
    $sexo     = $filtros['sexo'];
    $titulado = $filtros['titulado'];

    // Construcción dinámica del WHERE
    $condiciones = [];

    if (!empty($carrera)) {
        $condiciones[] = "e.CARRERA = '" . $conexion->real_escape_string($carrera) . "'";
    }
    if (!empty($anio)) {
        $condiciones[] = "YEAR(e.FECHA_EGRESO) = '" . $conexion->real_escape_string($anio) . "'";
    }
    if (!empty($sexo)) {
        $condiciones[] = "e.SEXO = '" . $conexion->real_escape_string($sexo) . "'";
    }
    if ($titulado !== '') {
        $condiciones[] = "e.TITULADO = " . ($titulado == '1' ? '1' : '0');
    }

    $where = !empty($condiciones) ? 'WHERE ' . implode(' AND ', $condiciones) : '';

    // Obtener los ID_CUESTIONARIO más recientes por CURP
    $sqlEgresados = "
        SELECT cr.ID_CUESTIONARIO
        FROM CUESTIONARIO_RESPUESTA cr
        INNER JOIN (
            SELECT CURP, MAX(ID_CUESTIONARIO) AS ULTIMO
            FROM CUESTIONARIO_RESPUESTA
            GROUP BY CURP
        ) ult ON cr.CURP = ult.CURP AND cr.ID_CUESTIONARIO = ult.ULTIMO
        INNER JOIN EGRESADO e ON cr.CURP = e.CURP
        $where
    ";

    $resultado = $conexion->query($sqlEgresados);
    $ids = [];

    while ($fila = $resultado->fetch_assoc()) {
        $ids[] = $fila['ID_CUESTIONARIO'];
    }

    if (empty($ids)) {
        echo "No hay datos para mostrar.";
        return;
    }

    $idsString = implode(',', $ids);

    // Consulta principal con JOINs a secciones, preguntas, opciones y respuestas
    $sql = "
        SELECT 
            s.ID_SECCION,
            s.NOMBRE AS SECCION,
            s.ORDEN AS ORDEN_SECCION,
            p.ID_PREGUNTA,
            p.TEXTO AS PREGUNTA,
            o.ID_OPCION AS ID_OPCION_RESPUESTA,
            o.TEXTO AS OPCION,
            COUNT(r.ID_OPCION) AS FRECUENCIA
        FROM SECCION s
        INNER JOIN PREGUNTA p ON p.ID_SECCION = s.ID_SECCION
        INNER JOIN OPCION_RESPUESTA o ON o.ID_PREGUNTA = p.ID_PREGUNTA
        LEFT JOIN RESPUESTA r 
            ON r.ID_PREGUNTA = p.ID_PREGUNTA 
            AND r.ID_OPCION = o.ID_OPCION
            AND r.ID_CUESTIONARIO IN ($idsString)
        WHERE s.PARA_CARRERA IS NULL 
           OR s.PARA_CARRERA = '" . $conexion->real_escape_string($carrera) . "'
        GROUP BY s.ID_SECCION, p.ID_PREGUNTA, o.ID_OPCION
        ORDER BY s.ORDEN, p.ID_PREGUNTA, o.ID_OPCION
    ";

    $resultado = $conexion->query($sql);
    $datos = [];
    $totalesPorPregunta = [];

    while ($row = $resultado->fetch_assoc()) {
        $seccion   = $row['SECCION'];
        $pregunta  = $row['PREGUNTA'];
        $opcion    = $row['OPCION'];
        $frecuencia = (int)$row['FRECUENCIA'];

        $datos[$seccion][$pregunta][] = [
            'opcion'     => $opcion,
            'frecuencia' => $frecuencia
        ];

        if (!isset($totalesPorPregunta[$pregunta])) {
            $totalesPorPregunta[$pregunta] = 0;
        }
        $totalesPorPregunta[$pregunta] += $frecuencia;
    }

    $spreadsheet = new Spreadsheet();
    $spreadsheet->removeSheetByIndex(0); // Quitar hoja por defecto

    // Estilos
    $styleHeader = [
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '003366']],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
    ];

    $styleCell = [
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
    ];

    $stylePregunta = [
        'font' => ['bold' => true, 'size' => 12],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
    ];

    foreach ($datos as $seccion => $preguntas) {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle(substr($seccion, 0, 31));
        $fila = 1;

        foreach ($preguntas as $pregunta => $opciones) {
            // Título de la pregunta
            $sheet->setCellValue("A{$fila}", $pregunta);
            $sheet->mergeCells("A{$fila}:D{$fila}");
            $sheet->getStyle("A{$fila}")->applyFromArray($stylePregunta);
            $fila++;

            // Encabezados
            $sheet->setCellValue("A{$fila}", 'Opción');
            $sheet->setCellValue("B{$fila}", 'Frecuencia');
            $sheet->setCellValue("C{$fila}", 'Porcentaje');
            $sheet->getStyle("A{$fila}:C{$fila}")->applyFromArray($styleHeader);
            $fila++;

            $startDataRow = $fila;

            // Opciones
            foreach ($opciones as $dato) {
                $frecuencia = $dato['frecuencia'];
                $total = $totalesPorPregunta[$pregunta];
                $porcentaje = $total > 0 ? round(($frecuencia / $total) * 100, 1) : 0;

                $sheet->setCellValue("A{$fila}", $dato['opcion']);
                $sheet->setCellValue("B{$fila}", $frecuencia);
                $sheet->setCellValue("C{$fila}", $porcentaje . '%');

                $sheet->getStyle("A{$fila}:C{$fila}")->applyFromArray($styleCell);
                $fila++;
            }

            $endDataRow = $fila - 1;

            // Autoajustar columnas
            foreach (range('A', 'D') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            // Crear gráfico de barras para esta pregunta
            $labels = [
                new DataSeriesValues('String', "'{$sheet->getTitle()}'!\$A\${$startDataRow}:\$A\${$endDataRow}", null, ($endDataRow - $startDataRow + 1)),
            ];
            $categories = $labels;
            $values = [
                new DataSeriesValues('Number', "'{$sheet->getTitle()}'!\$B\${$startDataRow}:\$B\${$endDataRow}", null, ($endDataRow - $startDataRow + 1)),
            ];

            $series = new DataSeries(
                DataSeries::TYPE_BARCHART,       // chart type
                DataSeries::GROUPING_CLUSTERED,  // grouping
                range(0, count($values) - 1),    // plotOrder
                $labels,                         // labels
                $categories,                     // categories
                $values                          // values
            );
            $series->setPlotDirection(DataSeries::DIRECTION_COL);

            $plotArea = new PlotArea(null, [$series]);
            $legend = new Legend(Legend::POSITION_RIGHT, null, false);
            $title = new Title('Distribución de Respuestas');
            $xAxisLabel = new Title('Opciones');
            $yAxisLabel = new Title('Frecuencia');

            $chart = new Chart(
                'chart' . $fila,
                $title,
                $legend,
                $plotArea,
                true,
                0,
                $xAxisLabel,
                $yAxisLabel
            );

            // Posición del gráfico en la hoja, por ejemplo un poco abajo de la tabla
            $posicionGraficoFila = $endDataRow + 2;
            $chart->setTopLeftPosition("A{$posicionGraficoFila}");
            $chart->setBottomRightPosition("H" . ($posicionGraficoFila + 15));

            $sheet->addChart($chart);

            // Separación antes de siguiente pregunta
            $fila = $posicionGraficoFila + 17;
        }
    }

    // Descargar archivo con gráficos
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="historico_estadistico_con_graficos.xlsx"');
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->setIncludeCharts(true);
    $writer->save('php://output');
    exit();
}

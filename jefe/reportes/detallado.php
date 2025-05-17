<?php
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

use Dompdf\Dompdf;
use Dompdf\Options;


function generarReporteDetallado($conexion, $carrera, $filtros) {
    if ($filtros['formato'] === 'pdf') {
        generarReporteDetalladoPDF($conexion, $carrera, $filtros);
    } else {
        generarReporteDetalladoExcel($conexion, $carrera, $filtros);
    }
}

function generarReporteDetalladoExcel($conexion, $carrera, $filtros) {
    $preguntasPorSeccion = obtenerPreguntasPorSeccion($conexion, $carrera);
    $egresados = obtenerEgresadosConRespuestas($conexion, $carrera, $filtros);

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Columnas fijas para cada bloque
    $columnasFijas = ['CURP', 'Nombre', 'Sexo', 'Carrera', 'Titulado', 'Año de Egreso'];

    // Fila inicial
    $fila = 1;

    // Estilos para encabezados
    $estiloEncabezado = [
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F81BD']],
        'borders' => [
            'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']],
        ],
        'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
    ];

    // Estilo para bordes celdas datos
    $estiloBordes = [
        'borders' => [
            'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']],
        ]
    ];

    foreach ($preguntasPorSeccion as $seccion => $preguntas) {
        // Insertar título de sección (fusionando columnas)
        $numColumnas = count($columnasFijas) + count($preguntas);
        $finCol = Coordinate::stringFromColumnIndex($numColumnas);

        $sheet->setCellValue("A{$fila}", $seccion);
        if ($numColumnas > 1) {
            $sheet->mergeCells("A{$fila}:{$finCol}{$fila}");
        }
        // Estilo título sección
        $sheet->getStyle("A{$fila}:{$finCol}{$fila}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 14],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D9E1F2']],
            'alignment' => ['horizontal' => 'left', 'vertical' => 'center'],
            'borders' => [
                'bottom' => ['borderStyle' => Border::BORDER_THICK, 'color' => ['rgb' => '4F81BD']],
            ],
        ]);
        $fila++;

        // Insertar encabezados fijos + preguntas
        $colIndex = 1;
        foreach ($columnasFijas as $header) {
            $colLetter = Coordinate::stringFromColumnIndex($colIndex);
            $sheet->setCellValue($colLetter . $fila, $header);
            $sheet->getStyle($colLetter . $fila)->applyFromArray($estiloEncabezado);
            $colIndex++;
        }
        foreach ($preguntas as $pregunta) {
            $colLetter = Coordinate::stringFromColumnIndex($colIndex);
            $sheet->setCellValue($colLetter . $fila, $pregunta['TEXTO']);
            $sheet->getStyle($colLetter . $fila)->applyFromArray($estiloEncabezado);
            $colIndex++;
        }

        $fila++;

        // Insertar datos egresados
        foreach ($egresados as $egresado) {
            $colIndex = 1;
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIndex++) . $fila, $egresado['CURP']);
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIndex++) . $fila, $egresado['NOMBRE']);
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIndex++) . $fila, $egresado['SEXO']);
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIndex++) . $fila, $egresado['CARRERA']);
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIndex++) . $fila, $egresado['TITULADO'] ? 'Sí' : 'No');
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIndex++) . $fila, date('Y', strtotime($egresado['FECHA_EGRESO'])));

            // Respuestas solo de esta sección
            foreach ($preguntas as $pregunta) {
                $respuesta = $egresado['respuestas'][$pregunta['ID_PREGUNTA']] ?? '';
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIndex++) . $fila, $respuesta);
            }

            // Aplicar bordes a la fila
            $finCol = Coordinate::stringFromColumnIndex($numColumnas);
            $sheet->getStyle("A{$fila}:{$finCol}{$fila}")->applyFromArray($estiloBordes);

            $fila++;
        }

        // Dejar fila en blanco para separar bloques
        $fila++;
    }

    // Ajustar ancho columnas (opcional)
    $totalCols = 0;
    foreach ($preguntasPorSeccion as $seccion => $preguntas) {
        $totalCols = max($totalCols, count($columnasFijas) + count($preguntas));
    }
    for ($i = 1; $i <= $totalCols; $i++) {
        $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($i))->setAutoSize(true);
    }

    // Exportar a Excel
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="reporte_detallado.xlsx"');
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit();
}

function generarReporteDetalladoPDF($conexion, $carrera, $filtros) {
    $preguntasPorSeccion = obtenerPreguntasPorSeccion($conexion, $carrera);
    $egresados = obtenerEgresadosConRespuestas($conexion, $carrera, $filtros);

    $html = '<html><head><style>
        body { font-family: Arial, sans-serif; font-size: 12px; margin: 20px; }
        h1 { text-align: center; margin-bottom: 30px; }
        .egresado { page-break-after: always; border-bottom: 2px solid #4F81BD; padding-bottom: 15px; margin-bottom: 30px; }
        .datos-basicos { margin-bottom: 20px; }
        .datos-basicos span { display: inline-block; width: 150px; font-weight: bold; }
        h2.seccion { background: #D9E1F2; padding: 8px; border-bottom: 3px solid #4F81BD; margin-top: 25px; }
        table { border-collapse: collapse; width: 100%; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 6px; vertical-align: top; }
        th { background: #4F81BD; color: white; }
    </style></head><body>';

    $html .= '<h1>Reporte Detallado por Egresado</h1>';

    foreach ($egresados as $egresado) {
        $html .= '<div class="egresado">';
        // Datos básicos
        $html .= '<div class="datos-basicos">';
        $html .= '<span>CURP:</span> ' . htmlspecialchars($egresado['CURP']) . '<br>';
        $html .= '<span>Nombre:</span> ' . htmlspecialchars($egresado['NOMBRE']) . '<br>';
        $html .= '<span>Sexo:</span> ' . htmlspecialchars($egresado['SEXO']) . '<br>';
        $html .= '<span>Carrera:</span> ' . htmlspecialchars($egresado['CARRERA']) . '<br>';
        $html .= '<span>Titulado:</span> ' . ($egresado['TITULADO'] ? 'Sí' : 'No') . '<br>';
        $html .= '<span>Año de Egreso:</span> ' . date('Y', strtotime($egresado['FECHA_EGRESO'])) . '<br>';
        $html .= '</div>';

        // Cuestionario por sección
        foreach ($preguntasPorSeccion as $seccion => $preguntas) {
            $html .= "<h2 class='seccion'>" . htmlspecialchars($seccion) . "</h2>";
            $html .= '<table><tbody>';
            foreach ($preguntas as $pregunta) {
                $respuesta = $egresado['respuestas'][$pregunta['ID_PREGUNTA']] ?? '';
                $html .= '<tr>';
                $html .= '<th style="width:40%;">' . htmlspecialchars($pregunta['TEXTO']) . '</th>';
                $html .= '<td>' . nl2br(htmlspecialchars($respuesta)) . '</td>';
                $html .= '</tr>';
            }
            $html .= '</tbody></table>';
        }
        $html .= '</div>';
    }

    $html .= '</body></html>';

    // Dompdf setup
    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isRemoteEnabled', false);

    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait'); // orientación vertical ahora
    $dompdf->render();

    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="reporte_detallado.pdf"');
    echo $dompdf->output();
    exit();
}

// FUNCIONES AUXILIARES

function obtenerTodasLasPreguntas($conexion, $carrera) {
    $sql = "SELECT P.ID_PREGUNTA, P.TEXTO
            FROM PREGUNTA P
            INNER JOIN SECCION S ON P.ID_SECCION = S.ID_SECCION
            WHERE S.PARA_CARRERA IS NULL OR S.PARA_CARRERA = ?
            ORDER BY S.ORDEN, P.ORDEN";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("s", $carrera);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function obtenerEgresadosConRespuestas($conexion, $carrera, $filtros) {
    $condiciones = "E.CARRERA = ?";
    $params = [$carrera];
    $tipos = "s";

    $periodoFiltro = !empty($filtros['periodo']) ? intval($filtros['periodo']) : null;
    $curpSeleccionado = $filtros['curp_seleccionado'] ?? '';

    if (!empty($curpSeleccionado)) {
        $condiciones .= " AND E.CURP = ?";
        $params[] = $curpSeleccionado;
        $tipos .= "s";
    } else {
        if (!empty($filtros['anio'])) {
            $condiciones .= " AND YEAR(E.FECHA_EGRESO) = ?";
            $params[] = intval($filtros['anio']);
            $tipos .= "i";
        }
        if (!empty($filtros['sexo'])) {
            $condiciones .= " AND E.SEXO = ?";
            $params[] = $filtros['sexo'];
            $tipos .= "s";
        }
        if ($filtros['titulado'] !== '') {
            $condiciones .= " AND E.TITULADO = ?";
            $params[] = intval($filtros['titulado']);
            $tipos .= "i";
        }
    }

    // Subconsulta para obtener el ID_CUESTIONARIO más reciente para cada egresado en el periodo seleccionado
    $subquery = "";
    $paramsSubquery = [];
    $tiposSubquery = "";

    if ($periodoFiltro) {
        $subquery = "
            LEFT JOIN (
                SELECT CURP, MAX(ID_CUESTIONARIO) AS ID_CUESTIONARIO
                FROM CUESTIONARIO_RESPUESTA
                WHERE ID_PERIODO = ?
                GROUP BY CURP
            ) CR ON E.CURP = CR.CURP
        ";
        $paramsSubquery[] = $periodoFiltro;
        $tiposSubquery .= "i";
    } else {
        // Si no hay filtro de periodo, unimos cuestionarios sin filtrar periodo, máximo ID_CUESTIONARIO por CURP
        $subquery = "
            LEFT JOIN (
                SELECT CURP, MAX(ID_CUESTIONARIO) AS ID_CUESTIONARIO
                FROM CUESTIONARIO_RESPUESTA
                GROUP BY CURP
            ) CR ON E.CURP = CR.CURP
        ";
    }

    $sql = "
        SELECT E.CURP, CONCAT(E.NOMBRE, ' ', E.APELLIDO_PATERNO, ' ', E.APELLIDO_MATERNO) AS NOMBRE,
               E.SEXO, E.CARRERA, E.TITULADO, E.FECHA_EGRESO,
               CR.ID_CUESTIONARIO
        FROM EGRESADO E
        $subquery
        WHERE $condiciones
    ";

    // Mezclamos parámetros y tipos para bind_param
    $paramsFinal = array_merge($paramsSubquery, $params);
    $tiposFinal = $tiposSubquery . $tipos;

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param($tiposFinal, ...$paramsFinal);
    $stmt->execute();
    $result = $stmt->get_result();

    $egresados = [];
    while ($row = $result->fetch_assoc()) {
        $respuestas = [];
        if (!empty($row['ID_CUESTIONARIO'])) {
            $respuestas = obtenerRespuestasPorCuestionario($conexion, $row['ID_CUESTIONARIO']);
        }
        $row['respuestas'] = $respuestas;
        $egresados[] = $row;
    }

    return $egresados;
}

function obtenerPreguntasPorSeccion($conexion, $carrera) {
    $sql = "SELECT S.NOMBRE AS SECCION, P.ID_PREGUNTA, P.TEXTO
            FROM PREGUNTA P
            INNER JOIN SECCION S ON P.ID_SECCION = S.ID_SECCION
            WHERE S.PARA_CARRERA IS NULL OR S.PARA_CARRERA = ?
            ORDER BY S.ORDEN, P.ORDEN";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("s", $carrera);
    $stmt->execute();
    $result = $stmt->get_result();

    $preguntasPorSeccion = [];
    while ($row = $result->fetch_assoc()) {
        $preguntasPorSeccion[$row['SECCION']][] = [
            'ID_PREGUNTA' => $row['ID_PREGUNTA'],
            'TEXTO' => $row['TEXTO']
        ];
    }
    return $preguntasPorSeccion;
}

function obtenerRespuestasPorCuestionario($conexion, $idCuestionario) {
    $sql = "
        SELECT R.ID_PREGUNTA, 
               COALESCE(O.TEXTO, R.RESPUESTA_TEXTO) AS RESPUESTA
        FROM RESPUESTA R
        LEFT JOIN OPCION_RESPUESTA O ON R.ID_OPCION = O.ID_OPCION
        WHERE R.ID_CUESTIONARIO = ?
    ";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $idCuestionario);
    $stmt->execute();
    $result = $stmt->get_result();

    $respuestas = [];
    while ($row = $result->fetch_assoc()) {
        $respuestas[$row['ID_PREGUNTA']] = $row['RESPUESTA'];
    }
    return $respuestas;
}

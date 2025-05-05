<?php 
session_start();
require('../db/conexion.php');
header('Content-Type: application/json');

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'egresado') {
    http_response_code(403);
    echo json_encode(['error' => 'Acceso no autorizado']);
    exit;
}

$curp = $_SESSION['curp'] ?? null;
$tipoEncuesta = $_SESSION['tipo_encuesta'] ?? 'GENERAL';

if (!$curp) {
    http_response_code(400);
    echo json_encode(['error' => 'CURP no encontrada en sesión']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!isset($data['respuestas']) || !is_array($data['respuestas'])) {
    echo json_encode(['success' => false, 'error' => 'Datos inválidos.']);
    exit;
}

$respuestas = $data['respuestas'];
$empresa = $data['empresa'] ?? null;

$id_empresa = null;

if ($empresa && is_array($empresa)) {
    $stmt_insert_empresa = $conexion->prepare("
        INSERT INTO EMPRESA (
            TIPO_ORGANISMO, GIRO, RAZON_SOCIAL, CALLE, NUMERO, COLONIA, CODIGO_POSTAL,
            CIUDAD, MUNICIPIO, ESTADO, TELEFONO, EMAIL, PAGINA_WEB,
            JEFE_INMEDIATO_NOMBRE, JEFE_INMEDIATO_PUESTO
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt_insert_empresa->bind_param(
        "sssssssssssssss",
        $empresa['tipo_organismo'],
        $empresa['giro'],
        $empresa['razon_social'],
        $empresa['calle'],
        $empresa['numero'],
        $empresa['colonia'],
        $empresa['codigo_postal'],
        $empresa['ciudad'],
        $empresa['municipio'],
        $empresa['estado'],
        $empresa['telefono'],
        $empresa['email'],
        $empresa['pagina_web'],
        $empresa['jefe_nombre'],
        $empresa['jefe_puesto']
    );

    if ($stmt_insert_empresa->execute()) {
        $id_empresa = $stmt_insert_empresa->insert_id;
    }

    $stmt_insert_empresa->close();
}

// Buscar el período activo
$stmt_periodo = $conexion->prepare("SELECT ID_PERIODO FROM PERIODO_ENCUESTA WHERE CURDATE() BETWEEN FECHA_INICIO AND FECHA_FIN AND ACTIVO = TRUE LIMIT 1");
$stmt_periodo->execute();
$stmt_periodo->bind_result($id_periodo);
$stmt_periodo->fetch();
$stmt_periodo->close();

if (!$id_periodo) {
    echo json_encode(['success' => false, 'error' => 'No hay un período de encuesta activo.']);
    exit;
}

// Verificar si ya existe un cuestionario en este período
$stmt_check = $conexion->prepare("SELECT ID_CUESTIONARIO, COMPLETO FROM CUESTIONARIO_RESPUESTA WHERE CURP = ? AND ID_PERIODO = ?");
$stmt_check->bind_param("si", $curp, $id_periodo);
$stmt_check->execute();
$stmt_check->bind_result($id_cuestionario_existente, $completo_existente);
$stmt_check->fetch();
$stmt_check->close();

if ($id_cuestionario_existente && $completo_existente) {
    echo json_encode(['success' => false, 'error' => 'Ya has completado la encuesta durante este período.']);
    exit;
}

if ($id_cuestionario_existente) {
    $id_cuestionario = $id_cuestionario_existente;

    // Borrar respuestas anteriores para permitir reintento
    $stmt_delete = $conexion->prepare("DELETE FROM RESPUESTA WHERE ID_CUESTIONARIO = ?");
    $stmt_delete->bind_param("i", $id_cuestionario);
    $stmt_delete->execute();
    $stmt_delete->close();

    // Marcar como incompleto para reintento
    $stmt_reset = $conexion->prepare("UPDATE CUESTIONARIO_RESPUESTA SET COMPLETO = FALSE, FECHA_APLICACION = NOW() WHERE ID_CUESTIONARIO = ?");
    $stmt_reset->bind_param("i", $id_cuestionario);
    $stmt_reset->execute();
    $stmt_reset->close();

} else {
    // Insertar nuevo cuestionario con ID_PERIODO
    $stmt = $conexion->prepare("INSERT INTO CUESTIONARIO_RESPUESTA (CURP, TIPO, FECHA_APLICACION, COMPLETO, ID_PERIODO, ID_EMPRESA) VALUES (?, ?, NOW(), FALSE, ?, ?)");
    if (!$stmt) {
        echo json_encode(['success' => false, 'error' => 'Error preparando inserción de cuestionario.']);
        exit;
    }
    $stmt->bind_param('ssii', $curp, $tipoEncuesta, $id_periodo, $id_empresa);
    if (!$stmt->execute()) {
        echo json_encode(['success' => false, 'error' => 'Error al guardar el cuestionario.']);
        exit;
    }
    $id_cuestionario = $stmt->insert_id;
    $stmt->close();
}

// Preparar sentencias
$stmt_opcion = $conexion->prepare("INSERT INTO RESPUESTA (ID_CUESTIONARIO, ID_PREGUNTA, ID_OPCION) VALUES (?, ?, ?)");
$stmt_texto = $conexion->prepare("INSERT INTO RESPUESTA (ID_CUESTIONARIO, ID_PREGUNTA, RESPUESTA_TEXTO) VALUES (?, ?, ?)");
$stmt_verificar_opcion = $conexion->prepare("SELECT 1 FROM OPCION_RESPUESTA WHERE ID_OPCION = ?");
$stmt_verificar_si_tiene_opciones = $conexion->prepare("SELECT 1 FROM OPCION_RESPUESTA WHERE ID_PREGUNTA = ? LIMIT 1");

if (!$stmt_opcion || !$stmt_texto || !$stmt_verificar_opcion || !$stmt_verificar_si_tiene_opciones) {
    echo json_encode(['success' => false, 'error' => 'Error preparando sentencias.']);
    exit;
}

// Guardar respuestas
$preguntas_respondidas = [];

foreach ($respuestas as $respuesta) {
    $id_pregunta = intval($respuesta['id_pregunta']);
    $valor = $respuesta['respuesta'];

    $stmt_verificar_si_tiene_opciones->bind_param("i", $id_pregunta);
    $stmt_verificar_si_tiene_opciones->execute();
    $stmt_verificar_si_tiene_opciones->store_result();
    $tiene_opciones = $stmt_verificar_si_tiene_opciones->num_rows > 0;

    if ($tiene_opciones) {
        if (is_array($valor)) {
            foreach ($valor as $id_opcion) {
                $id_opcion = intval($id_opcion);
                $stmt_verificar_opcion->bind_param("i", $id_opcion);
                $stmt_verificar_opcion->execute();
                $stmt_verificar_opcion->store_result();

                if ($stmt_verificar_opcion->num_rows > 0) {
                    $stmt_opcion->bind_param("iii", $id_cuestionario, $id_pregunta, $id_opcion);
                    $stmt_opcion->execute();
                    $preguntas_respondidas[$id_pregunta] = true;
                }
            }
        } else {
            $id_opcion = intval($valor);
            $stmt_verificar_opcion->bind_param("i", $id_opcion);
            $stmt_verificar_opcion->execute();
            $stmt_verificar_opcion->store_result();

            if ($stmt_verificar_opcion->num_rows > 0) {
                $stmt_opcion->bind_param("iii", $id_cuestionario, $id_pregunta, $id_opcion);
                $stmt_opcion->execute();
                $preguntas_respondidas[$id_pregunta] = true;
            }
        }
    } else {
        $texto = trim($valor);
        if ($texto !== '') {
            $stmt_texto->bind_param("iis", $id_cuestionario, $id_pregunta, $texto);
            $stmt_texto->execute();
            $preguntas_respondidas[$id_pregunta] = true;
        }
    }
}

// Total de preguntas obligatorias para ese tipo
$stmt_total_preguntas = $conexion->prepare("
    SELECT COUNT(*) 
    FROM SECCION s 
    JOIN PREGUNTA p ON s.ID_SECCION = p.ID_SECCION 
    WHERE (s.PARA_CARRERA IS NULL OR s.PARA_CARRERA = ?) AND p.OBLIGATORIA = TRUE
");
$stmt_total_preguntas->bind_param('s', $tipoEncuesta);
$stmt_total_preguntas->execute();
$stmt_total_preguntas->bind_result($total_preguntas);
$stmt_total_preguntas->fetch();
$stmt_total_preguntas->close();

// Marcar como completo si aplica
if (count($preguntas_respondidas) >= $total_preguntas) {
    $stmt_update = $conexion->prepare("UPDATE CUESTIONARIO_RESPUESTA SET COMPLETO = TRUE WHERE ID_CUESTIONARIO = ?");
    $stmt_update->bind_param("i", $id_cuestionario);
    $stmt_update->execute();
    $stmt_update->close();
}

echo json_encode(['success' => true, 'completo' => count($preguntas_respondidas) >= $total_preguntas]);
?>

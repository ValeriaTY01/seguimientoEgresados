<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once('../db/conexion.php');

header('Content-Type: application/json');

function limpiar_texto($str) {
    return htmlspecialchars(trim($str));
}

// Lista de campos editables
$campos_editables = [
    'NOMBRE', 'APELLIDO_PATERNO', 'APELLIDO_MATERNO',
    'FECHA_NACIMIENTO', 'SEXO', 'ESTADO_CIVIL', 'CALLE', 'COLONIA',
    'CODIGO_POSTAL', 'CIUDAD', 'MUNICIPIO', 'ESTADO', 'EMAIL',
    'TELEFONO', 'CARRERA', 'FECHA_EGRESO', 'TITULADO'
];

// ——— GET ———
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['curp'])) {
    $curp = $_GET['curp'];

    $sql = "SELECT * FROM EGRESADO WHERE CURP = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("s", $curp);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        http_response_code(404);
        echo json_encode(['error' => 'Egresado no encontrado']);
        exit;
    }

    $egresado = $result->fetch_assoc();
    echo json_encode($egresado);
    exit;
}

// ——— POST ———
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['curp'])) {
    $curp = $_POST['curp'];

    // Consultar datos actuales
    $sql_actual = "SELECT * FROM EGRESADO WHERE CURP = ?";
    $stmt_actual = $conexion->prepare($sql_actual);
    $stmt_actual->bind_param("s", $curp);
    $stmt_actual->execute();
    $result_actual = $stmt_actual->get_result();

    if ($result_actual->num_rows === 0) {
        http_response_code(404);
        echo json_encode(['error' => 'Registro no encontrado']);
        exit;
    }

    $datos_actuales = $result_actual->fetch_assoc();
    $cambios = [];
    $update_parts = [];
    $update_types = "";
    $update_values = [];

    foreach ($campos_editables as $campo) {
        if ($campo === 'TITULADO') {
            $nuevo_valor = isset($_POST[$campo]) ? 1 : 0;
        } else {
            $nuevo_valor = $_POST[$campo] ?? null;
            if ($nuevo_valor !== null) {
                $nuevo_valor = limpiar_texto($nuevo_valor);
            }
        }
        if (isset($datos_actuales[$campo]) && $datos_actuales[$campo] != $nuevo_valor) {
            $cambios[] = [
                'campo' => $campo,
                'valor_anterior' => $datos_actuales[$campo],
                'valor_nuevo' => $nuevo_valor
            ];
            $update_parts[] = "$campo = ?";
            $update_types .= ($campo === 'TITULADO') ? "i" : "s";
            $update_values[] = $nuevo_valor;
        }
    }

    if (count($cambios) === 0) {
        echo json_encode(['message' => 'No hubo cambios para guardar']);
        exit;
    }

    $update_sql = "UPDATE EGRESADO SET " . implode(", ", $update_parts) . " WHERE CURP = ?";
    $stmt_update = $conexion->prepare($update_sql);
    $update_types .= "s";
    $update_values[] = $curp;

    $stmt_update->bind_param($update_types, ...$update_values);

    if ($stmt_update->execute()) {
        $insert_sql = "INSERT INTO MODIFICACION_EGRESADO (RFC, CURP, CAMPO_MODIFICADO, VALOR_ANTERIOR, VALOR_NUEVO) VALUES (?, ?, ?, ?, ?)";
        $stmt_insert = $conexion->prepare($insert_sql);
        $rfc_usuario = $_SESSION['rfc'] ?? 'SYSTEM';

        foreach ($cambios as $modificacion) {
            $stmt_insert->bind_param(
                "sssss",
                $rfc_usuario,
                $curp,
                $modificacion['campo'],
                $modificacion['valor_anterior'],
                $modificacion['valor_nuevo']
            );
            $stmt_insert->execute();
        }

        echo json_encode(['message' => 'Datos actualizados correctamente']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Error al actualizar los datos']);
    }

    exit;
}

// Si no hay CURP o método no soportado
http_response_code(400);
echo json_encode(['error' => 'CURP no especificado o método no permitido']);
exit;

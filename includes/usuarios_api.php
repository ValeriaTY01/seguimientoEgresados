<?php
header('Content-Type: application/json');

require_once('../db/conexion.php');

$accion = isset($_GET['accion']) ? $_GET['accion'] : '';

// ACCIONES GET
if ($accion === 'listar') {
    $rol = isset($_GET['rol']) ? $conexion->real_escape_string($_GET['rol']) : '';
    $carrera = isset($_GET['carrera']) ? $conexion->real_escape_string($_GET['carrera']) : '';

    $filtros = [];
    if ($rol !== '') {
        $filtros[] = "ROL = '$rol'";
    }
    if ($carrera !== '') {
        $filtros[] = "CARRERA = '$carrera'";
    }

    $where = count($filtros) > 0 ? " WHERE " . implode(" AND ", $filtros) : "";

    $sql = "SELECT RFC, NOMBRE, APELLIDO_PATERNO, APELLIDO_MATERNO, EMAIL, ROL, CARRERA FROM USUARIO" . $where;

    $resultado = $conexion->query($sql);
    if (!$resultado) {
        echo json_encode(["ok" => false, "mensaje" => "Error en consulta: " . $conexion->error]);
        exit;
    }

    $usuarios = [];
    while ($fila = $resultado->fetch_assoc()) {
        $usuarios[] = [
            "rfc" => $fila["RFC"],
            "nombre" => $fila["NOMBRE"] . " " . $fila["APELLIDO_PATERNO"] . " " . $fila["APELLIDO_MATERNO"],
            "email" => $fila["EMAIL"],
            "rol" => $fila["ROL"],
            "carrera" => $fila["CARRERA"]
        ];
    }

    echo json_encode(["ok" => true, "data" => $usuarios]);
    exit;
}

if ($accion === 'carreras') {
    $sql = "SELECT DISTINCT CARRERA FROM USUARIO WHERE CARRERA IS NOT NULL AND CARRERA != ''";
    $resultado = $conexion->query($sql);

    $carreras = [];
    while ($fila = $resultado->fetch_assoc()) {
        $carreras[] = [
            "id" => $fila["CARRERA"],  // usar el nombre como id también
            "nombre" => $fila["CARRERA"]
        ];
    }

    echo json_encode(["ok" => true, "data" => $carreras]);
    exit;
}

if ($accion === 'obtener' && isset($_GET['rfc'])) {
    $rfc = $conexion->real_escape_string($_GET['rfc']);
    $sql = "SELECT RFC, NOMBRE, APELLIDO_PATERNO, APELLIDO_MATERNO, EMAIL, ROL, CARRERA FROM USUARIO WHERE RFC = '$rfc'";
    $resultado = $conexion->query($sql);

    if ($resultado && $resultado->num_rows > 0) {
        $fila = $resultado->fetch_assoc();
        echo json_encode([
            "ok" => true,
            "data" => [
                "rfc" => $fila["RFC"],
                "nombre" => $fila["NOMBRE"],
                "apellido_paterno" => $fila["APELLIDO_PATERNO"],
                "apellido_materno" => $fila["APELLIDO_MATERNO"],
                "email" => $fila["EMAIL"],
                "rol" => $fila["ROL"],
                "carrera" => $fila["CARRERA"]
            ]
        ]);
    } else {
        echo json_encode(["ok" => false, "mensaje" => "Usuario no encontrado"]);
    }
    exit;
}

// ACCIONES POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion_post = isset($_POST['accion']) ? $_POST['accion'] : '';

    if ($accion_post === 'eliminar') {
        $rfc = isset($_POST['rfc']) ? $conexion->real_escape_string($_POST['rfc']) : '';
        if ($rfc === '') {
            echo json_encode(["ok" => false, "mensaje" => "RFC no especificado"]);
            exit;
        }

        $sqlCheck = "SELECT ROL FROM USUARIO WHERE RFC = '$rfc'";
        $resultadoCheck = $conexion->query($sqlCheck);
        if ($resultadoCheck && $resultadoCheck->num_rows > 0) {
            $usuario = $resultadoCheck->fetch_assoc();
            if (strtolower($usuario['ROL']) === 'administrador') {
                echo json_encode(["ok" => false, "mensaje" => "No se puede eliminar al administrador"]);
                exit;
            }
        } else {
            echo json_encode(["ok" => false, "mensaje" => "Usuario no encontrado"]);
            exit;
        }

        $sqlEliminar = "DELETE FROM USUARIO WHERE RFC = '$rfc'";
        if ($conexion->query($sqlEliminar)) {
            echo json_encode(["ok" => true, "mensaje" => "Usuario eliminado correctamente"]);
        } else {
            echo json_encode(["ok" => false, "mensaje" => "Error al eliminar: " . $conexion->error]);
        }
        exit;
    }

    if ($accion_post === 'agregar') {
        // Campos esperados
        $rfc = isset($_POST['rfc']) ? $conexion->real_escape_string(trim($_POST['rfc'])) : '';
        $nombre = isset($_POST['nombre']) ? $conexion->real_escape_string(trim($_POST['nombre'])) : '';
        $apellido_paterno = isset($_POST['apellido_paterno']) ? $conexion->real_escape_string(trim($_POST['apellido_paterno'])) : '';
        $apellido_materno = isset($_POST['apellido_materno']) ? $conexion->real_escape_string(trim($_POST['apellido_materno'])) : '';
        $email = isset($_POST['email']) ? $conexion->real_escape_string(trim($_POST['email'])) : '';
        $rol = isset($_POST['rol']) ? $conexion->real_escape_string(trim($_POST['rol'])) : '';
        $carrera = isset($_POST['carrera']) ? $conexion->real_escape_string(trim($_POST['carrera'])) : '';

        // Validaciones básicas
        if ($rfc === '' || $nombre === '' || $apellido_paterno === '' || $email === '' || $rol === '') {
            echo json_encode(["ok" => false, "mensaje" => "Faltan campos obligatorios"]);
            exit;
        }

        // Verificar si ya existe el RFC
        $sqlExiste = "SELECT RFC FROM USUARIO WHERE RFC = '$rfc'";
        $resExiste = $conexion->query($sqlExiste);
        if ($resExiste && $resExiste->num_rows > 0) {
            echo json_encode(["ok" => false, "mensaje" => "El RFC ya existe"]);
            exit;
        }

        $sqlInsert = "INSERT INTO USUARIO (RFC, NOMBRE, APELLIDO_PATERNO, APELLIDO_MATERNO, EMAIL, ROL, CARRERA)
                      VALUES ('$rfc', '$nombre', '$apellido_paterno', '$apellido_materno', '$email', '$rol', '$carrera')";

        if ($conexion->query($sqlInsert)) {
            echo json_encode(["ok" => true, "mensaje" => "Usuario agregado correctamente"]);
        } else {
            echo json_encode(["ok" => false, "mensaje" => "Error al agregar usuario: " . $conexion->error]);
        }
        exit;
    }

    if ($accion_post === 'actualizar') {
        // Campos esperados
        $rfc = isset($_POST['rfc']) ? $conexion->real_escape_string(trim($_POST['rfc'])) : '';
        $nombre = isset($_POST['nombre']) ? $conexion->real_escape_string(trim($_POST['nombre'])) : '';
        $apellido_paterno = isset($_POST['apellido_paterno']) ? $conexion->real_escape_string(trim($_POST['apellido_paterno'])) : '';
        $apellido_materno = isset($_POST['apellido_materno']) ? $conexion->real_escape_string(trim($_POST['apellido_materno'])) : '';
        $email = isset($_POST['email']) ? $conexion->real_escape_string(trim($_POST['email'])) : '';
        $rol = isset($_POST['rol']) ? $conexion->real_escape_string(trim($_POST['rol'])) : '';
        $carrera = isset($_POST['carrera']) ? $conexion->real_escape_string(trim($_POST['carrera'])) : '';

        if ($rfc === '') {
            echo json_encode(["ok" => false, "mensaje" => "RFC no especificado"]);
            exit;
        }

        // Validar que el usuario existe
        $sqlCheck = "SELECT * FROM USUARIO WHERE RFC = '$rfc'";
        $resCheck = $conexion->query($sqlCheck);
        if (!$resCheck || $resCheck->num_rows === 0) {
            echo json_encode(["ok" => false, "mensaje" => "Usuario no encontrado"]);
            exit;
        }

        // Construir consulta de actualización dinámicamente para evitar sobreescribir con campos vacíos
        $camposActualizar = [];
        if ($nombre !== '') $camposActualizar[] = "NOMBRE = '$nombre'";
        if ($apellido_paterno !== '') $camposActualizar[] = "APELLIDO_PATERNO = '$apellido_paterno'";
        if ($apellido_materno !== '') $camposActualizar[] = "APELLIDO_MATERNO = '$apellido_materno'";
        if ($email !== '') $camposActualizar[] = "EMAIL = '$email'";
        if ($rol !== '') $camposActualizar[] = "ROL = '$rol'";
        if ($carrera !== '') $camposActualizar[] = "CARRERA = '$carrera'";

        if (count($camposActualizar) === 0) {
            echo json_encode(["ok" => false, "mensaje" => "No hay datos para actualizar"]);
            exit;
        }

        $sqlUpdate = "UPDATE USUARIO SET " . implode(", ", $camposActualizar) . " WHERE RFC = '$rfc'";

        if ($conexion->query($sqlUpdate)) {
            echo json_encode(["ok" => true, "mensaje" => "Usuario actualizado correctamente"]);
        } else {
            echo json_encode(["ok" => false, "mensaje" => "Error al actualizar: " . $conexion->error]);
        }
        exit;
    }
}

echo json_encode(["ok" => false, "mensaje" => "Acción no válida"]);
exit;
?>

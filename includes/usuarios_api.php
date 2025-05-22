<?php
header('Content-Type: application/json');
require_once('../db/conexion.php');

$accion = isset($_REQUEST['accion']) ? $_REQUEST['accion'] : '';

switch ($accion) {
    // =======================
    // ACCIONES GET
    // =======================

    case 'listar':
        $rol = isset($_GET['rol']) ? $conexion->real_escape_string($_GET['rol']) : '';
        $carrera = isset($_GET['carrera']) ? $conexion->real_escape_string($_GET['carrera']) : '';

        $filtros = [];
        if ($rol !== '') $filtros[] = "ROL = '$rol'";
        if ($carrera !== '') $filtros[] = "CARRERA = '$carrera'";

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
        break;

    case 'carreras':
        $sql = "SELECT DISTINCT CARRERA FROM USUARIO WHERE CARRERA IS NOT NULL AND CARRERA != ''";
        $resultado = $conexion->query($sql);

        $carreras = [];
        while ($fila = $resultado->fetch_assoc()) {
            $carreras[] = ["id" => $fila["CARRERA"], "nombre" => $fila["CARRERA"]];
        }

        echo json_encode(["ok" => true, "data" => $carreras]);
        break;

    case 'obtener':
        if (!isset($_GET['rfc'])) {
            echo json_encode(["ok" => false, "mensaje" => "RFC no proporcionado"]);
            break;
        }

        $rfc = $conexion->real_escape_string($_GET['rfc']);
        $sql = "SELECT * FROM USUARIO WHERE RFC = '$rfc'";
        $resultado = $conexion->query($sql);

        if ($resultado && $resultado->num_rows > 0) {
            $fila = $resultado->fetch_assoc();
            echo json_encode(["ok" => true, "data" => [
                "rfc" => $fila["RFC"],
                "nombre" => $fila["NOMBRE"],
                "apellido_paterno" => $fila["APELLIDO_PATERNO"],
                "apellido_materno" => $fila["APELLIDO_MATERNO"],
                "email" => $fila["EMAIL"],
                "rol" => $fila["ROL"],
                "carrera" => $fila["CARRERA"]
            ]]);
        } else {
            echo json_encode(["ok" => false, "mensaje" => "Usuario no encontrado"]);
        }
        break;

    // =======================
    // ACCIONES POST
    // =======================

    case 'eliminar':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') break;

        $rfc = isset($_POST['rfc']) ? $conexion->real_escape_string($_POST['rfc']) : '';
        if ($rfc === '') {
            echo json_encode(["ok" => false, "mensaje" => "RFC no especificado"]);
            break;
        }

        $sqlCheck = "SELECT ROL FROM USUARIO WHERE RFC = '$rfc'";
        $resultadoCheck = $conexion->query($sqlCheck);

        if ($resultadoCheck && $resultadoCheck->num_rows > 0) {
            $usuario = $resultadoCheck->fetch_assoc();
            if (strtolower($usuario['ROL']) === 'administrador') {
                echo json_encode(["ok" => false, "mensaje" => "No se puede eliminar al administrador"]);
                break;
            }
        } else {
            echo json_encode(["ok" => false, "mensaje" => "Usuario no encontrado"]);
            break;
        }

        $sqlEliminar = "DELETE FROM USUARIO WHERE RFC = '$rfc'";
        echo $conexion->query($sqlEliminar)
            ? json_encode(["ok" => true, "mensaje" => "Usuario eliminado correctamente"])
            : json_encode(["ok" => false, "mensaje" => "Error al eliminar: " . $conexion->error]);
        break;

    case 'agregar':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') break;

        $rfc = trim($_POST['rfc'] ?? '');
        $nombre = trim($_POST['nombre'] ?? '');
        $apellido_paterno = trim($_POST['apellido_paterno'] ?? '');
        $apellido_materno = trim($_POST['apellido_materno'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $rol = trim($_POST['rol'] ?? '');
        $carrera = trim($_POST['carrera'] ?? '');

        if ($rfc === '' || $nombre === '' || $apellido_paterno === '' || $email === '' || $rol === '') {
            echo json_encode(["ok" => false, "mensaje" => "Faltan campos obligatorios"]);
            break;
        }

        $sqlExiste = "SELECT RFC FROM USUARIO WHERE RFC = '$rfc'";
        $resExiste = $conexion->query($sqlExiste);
        if ($resExiste && $resExiste->num_rows > 0) {
            echo json_encode(["ok" => false, "mensaje" => "El RFC ya existe"]);
            break;
        }

        $sqlInsert = "INSERT INTO USUARIO (RFC, NOMBRE, APELLIDO_PATERNO, APELLIDO_MATERNO, EMAIL, ROL, CARRERA)
                      VALUES ('$rfc', '$nombre', '$apellido_paterno', '$apellido_materno', '$email', '$rol', '$carrera')";

        echo $conexion->query($sqlInsert)
            ? json_encode(["ok" => true, "mensaje" => "Usuario agregado correctamente"])
            : json_encode(["ok" => false, "mensaje" => "Error al agregar usuario: " . $conexion->error]);
        break;

    case 'actualizar':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') break;

        $rfc = trim($_POST['rfc'] ?? '');
        if ($rfc === '') {
            echo json_encode(["ok" => false, "mensaje" => "RFC no especificado"]);
            break;
        }

        $sqlCheck = "SELECT * FROM USUARIO WHERE RFC = '$rfc'";
        $resCheck = $conexion->query($sqlCheck);
        if (!$resCheck || $resCheck->num_rows === 0) {
            echo json_encode(["ok" => false, "mensaje" => "Usuario no encontrado"]);
            break;
        }

        $campos = [];
        foreach (['nombre', 'apellido_paterno', 'apellido_materno', 'email', 'rol', 'carrera'] as $campo) {
            if (!empty($_POST[$campo])) {
                $valor = $conexion->real_escape_string(trim($_POST[$campo]));
                $campos[] = strtoupper($campo) . " = '$valor'";
            }
        }

        if (count($campos) === 0) {
            echo json_encode(["ok" => false, "mensaje" => "No hay datos para actualizar"]);
            break;
        }

        $sqlUpdate = "UPDATE USUARIO SET " . implode(", ", $campos) . " WHERE RFC = '$rfc'";
        echo $conexion->query($sqlUpdate)
            ? json_encode(["ok" => true, "mensaje" => "Usuario actualizado correctamente"])
            : json_encode(["ok" => false, "mensaje" => "Error al actualizar: " . $conexion->error]);
        break;

    // =======================
    // DEFAULT
    // =======================
    default:
        echo json_encode(["ok" => false, "mensaje" => "Acción no válida"]);
        break;
}
?>

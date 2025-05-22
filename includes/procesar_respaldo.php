<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Configuración conexión
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "seguimientoEgresados";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$accion = $_POST['accion'] ?? '';

if ($accion === 'respaldar') {
    // ------------------- RESPALDO -------------------

    $nombreArchivo = 'respaldo_' . date("Ymd_His") . '.sql';
    $carpeta = __DIR__ . '/../respaldo_bd/';
    $rutaCompleta = $carpeta . $nombreArchivo;

    // Crear carpeta si no existe
    if (!file_exists($carpeta)) {
        mkdir($carpeta, 0775, true);
    }

    $respaldoSQL = "-- Respaldo generado el " . date("Y-m-d H:i:s") . "\n\n";

    // Obtener todas las tablas
    $tablas = [];
    $result = $conn->query("SHOW TABLES");
    while ($row = $result->fetch_array()) {
        $tablas[] = $row[0];
    }

    foreach ($tablas as $tabla) {
        // Instrucción DROP y CREATE TABLE
        $respaldoSQL .= "DROP TABLE IF EXISTS `$tabla`;\n";
        $createTable = $conn->query("SHOW CREATE TABLE `$tabla`")->fetch_assoc();
        $respaldoSQL .= $createTable['Create Table'] . ";\n\n";

        // Datos
        $datos = $conn->query("SELECT * FROM `$tabla`");
        while ($fila = $datos->fetch_assoc()) {
            $valores = array_map(function ($valor) use ($conn) {
                return is_null($valor) ? "NULL" : "'" . $conn->real_escape_string($valor) . "'";
            }, array_values($fila));
            $respaldoSQL .= "INSERT INTO `$tabla` VALUES(" . implode(",", $valores) . ");\n";
        }

        $respaldoSQL .= "\n\n";
    }

    // Guardar respaldo en servidor
    file_put_contents($rutaCompleta, $respaldoSQL);

    // Descargar respaldo al navegador
    header('Content-Description: File Transfer');
    header('Content-Type: application/sql');
    header("Content-Disposition: attachment; filename=\"$nombreArchivo\"");
    header('Content-Length: ' . filesize($rutaCompleta));
    readfile($rutaCompleta);
    exit;

} elseif ($accion === 'restaurar') {
    // ------------------- RESTAURACIÓN -------------------
    if (!isset($_FILES['archivo_sql']) || $_FILES['archivo_sql']['error'] !== UPLOAD_ERR_OK) {
        die("Error al subir el archivo.");
    }

    $contenido_sql = file_get_contents($_FILES['archivo_sql']['tmp_name']);
    $consultas = explode(";\n", $contenido_sql); // dividir por consultas

    $conn->begin_transaction();

    try {
        foreach ($consultas as $consulta) {
            $consulta = trim($consulta);
            if (!empty($consulta)) {
                $conn->query($consulta);
                if ($conn->error) throw new Exception($conn->error);
            }
        }
        $conn->commit();
        echo "<script>alert('Base de datos restaurada con éxito.'); window.location.href='../backup.php';</script>";
    } catch (Exception $e) {
        $conn->rollback();
        echo "<script>alert('Error al restaurar: " . $e->getMessage() . "'); window.location.href='../backup.php';</script>";
    }

    exit;
} else {
    die("Acción no válida.");
}
?>

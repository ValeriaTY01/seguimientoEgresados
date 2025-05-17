<?php
session_start();
require('../db/conexion.php');

header('Content-Type: application/json');

$q = $_GET['q'] ?? '';
$q = trim($q);

if (strlen($q) < 3) {
    echo json_encode([]);
    exit;
}

$sql = "SELECT CURP, NUM_CONTROL, NOMBRE, APELLIDO_PATERNO, APELLIDO_MATERNO
        FROM EGRESADO
        WHERE NOMBRE LIKE ? OR APELLIDO_PATERNO LIKE ? OR APELLIDO_MATERNO LIKE ? OR CURP LIKE ? OR NUM_CONTROL LIKE ?
        LIMIT 10";

$param = "%$q%";
$stmt = $conexion->prepare($sql);
$stmt->bind_param('sssss', $param, $param, $param, $param, $param);
$stmt->execute();
$result = $stmt->get_result();

$egresados = [];
while ($row = $result->fetch_assoc()) {
    $egresados[] = $row;
}

echo json_encode($egresados);

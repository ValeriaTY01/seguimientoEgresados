<?php
session_start();

if (!isset($_SESSION['rol']) || !in_array($_SESSION['rol'], ['administrador', 'jefe vinculación'])) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'error' => 'Acceso denegado']);
    exit;
}

require_once '../db/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'toggle') {
    header('Content-Type: application/json; charset=utf-8');
    $id = intval($_POST['id']);
    $stmt = $conexion->prepare("UPDATE PERIODO_ENCUESTA SET ACTIVO = NOT ACTIVO WHERE ID_PERIODO=?");
    $stmt->bind_param("i", $id);
    $success = $stmt->execute();
    $stmt->close();
    echo json_encode(['success' => $success]);
    exit;
}

// Si se llega aquí, no es petición válida
header('Content-Type: application/json; charset=utf-8');
echo json_encode(['success' => false, 'error' => 'Acción no permitida']);
exit;

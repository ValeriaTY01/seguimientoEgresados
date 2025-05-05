<?php
session_start();
header('Content-Type: application/json'); // ← Importante para fetch

include 'db/conexion.php';
include 'correo.php';  // Función enviarCorreoVerificacion()

// Validar CAPTCHA
if (!isset($_POST['captcha']) || $_POST['captcha'] !== ($_SESSION['captcha'] ?? '')) {
    echo json_encode(['success' => false, 'message' => 'El CAPTCHA es incorrecto.']);
    exit;
}

// Recoger datos
$nombre = $_POST['nombre'] ?? '';
$apellido_paterno = $_POST['apellido_paterno'] ?? '';
$apellido_materno = $_POST['apellido_materno'] ?? '';
$curp = $_POST['curp'] ?? '';
$num_control = $_POST['num_control'] ?? '';
$email = $_POST['email'] ?? '';
$contrasena = password_hash($_POST['contrasena'] ?? '', PASSWORD_DEFAULT);

// Generar código de verificación
$codigo_verificacion = bin2hex(random_bytes(16));

// Enviar correo antes de guardar en BD
if (!enviarCorreoVerificacion($email, $nombre, $codigo_verificacion)) {
    echo json_encode(['success' => false, 'message' => 'No se pudo enviar el correo de verificación. Intenta más tarde.']);
    exit;
}

// Insertar en la base de datos
$stmt = $conexion->prepare("INSERT INTO EGRESADO 
    (CURP, NUM_CONTROL, NOMBRE, APELLIDO_PATERNO, APELLIDO_MATERNO, EMAIL, CONTRASENA, CODIGO_VERIFICACION, VERIFICADO) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0)");
$stmt->bind_param("ssssssss", $curp, $num_control, $nombre, $apellido_paterno, $apellido_materno, $email, $contrasena, $codigo_verificacion);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Registro exitoso. Por favor, revisa tu correo para verificar tu cuenta.'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Error al guardar los datos. Es posible que ya exista un registro con esos datos.'
    ]);
}
?>

<?php
require('../db/conexion.php');

$mensaje = "";

if (isset($_GET['codigo'])) {
    $codigo = $_GET['codigo'];

    $stmt = $conexion->prepare("SELECT CURP FROM EGRESADO WHERE CODIGO_VERIFICACION = ?");
    $stmt->bind_param("s", $codigo);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 1) {
        $row = $resultado->fetch_assoc();
        $curp = $row['CURP'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nueva_contrasena = $_POST['nueva_contrasena'];
            $confirmar_contrasena = $_POST['confirmar_contrasena'];

            if ($nueva_contrasena === $confirmar_contrasena) {
                $hash = password_hash($nueva_contrasena, PASSWORD_DEFAULT);

                $stmt_update = $conexion->prepare("UPDATE EGRESADO SET CONTRASENA = ?, CODIGO_VERIFICACION = NULL WHERE CURP = ?");
                $stmt_update->bind_param("ss", $hash, $curp);
                $stmt_update->execute();

                $mensaje = "<p class='mensaje-exito'>✅ Tu contraseña ha sido restablecida con éxito.</p>";
                $formulario = false;
            } else {
                $mensaje = "<p class='mensaje-error'>❌ Las contraseñas no coinciden.</p>";
                $formulario = true;
            }
        } else {
            $formulario = true;
        }
    } else {
        $mensaje = "<p class='mensaje-error'>❌ Código inválido o ya usado.</p>";
        $formulario = false;
    }
} else {
    $mensaje = "<p class='mensaje-alerta'>⚠️ No se proporcionó ningún código.</p>";
    $formulario = false;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Restablecer Contraseña</title>
    <link rel="stylesheet" href="../css/restablecer.css"> <!-- Estilo nuevo -->
</head>
<body>
    <div class="modal-restablecer">
        <div class="modal-content-restablecer">
            <h2 class="titulo-restablecer">Restablecer Contraseña</h2>
            <?= $mensaje ?>

            <?php if (!empty($formulario)): ?>
            <form method="POST" class="form-restablecer">
                <input type="password" name="nueva_contrasena" placeholder="Nueva contraseña" required>
                <input type="password" name="confirmar_contrasena" placeholder="Confirmar contraseña" required>
                <button type="submit" class="btn-restablecer">Restablecer</button>
            </form>
            <?php endif; ?>

            <form action="../login.php" method="get" class="form-volver">
                <button type="submit" class="btn-volver">Volver al login</button>
            </form>
        </div>
    </div>
</body>
</html>

<?php
include 'db/conexion.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Verificación de Cuenta</title>
    <link rel="stylesheet" href="css/modal.css">
</head>
<body>
<div class="modal-verificacion" id="modal-verificacion">
    <div class="modal-content-verificacion">
        <?php
        if (isset($_GET['codigo'])) {
            $codigo = $_GET['codigo'];

            // Buscar si hay algún egresado con ese código
            $stmt = $conexion->prepare("SELECT CURP FROM EGRESADO WHERE CODIGO_VERIFICACION = ?");
            $stmt->bind_param("s", $codigo);
            $stmt->execute();
            $resultado = $stmt->get_result();

            if ($resultado->num_rows === 1) {
                $row = $resultado->fetch_assoc();
                $curp = $row['CURP'];

                // Actualizar como verificado
                $stmt_update = $conexion->prepare("UPDATE EGRESADO SET VERIFICADO = TRUE, CODIGO_VERIFICACION = NULL WHERE CURP = ?");
                $stmt_update->bind_param("s", $curp);
                $stmt_update->execute();

                echo '<h2>✅ ¡Cuenta verificada con éxito!</h2>';
                echo '<p>Ahora puedes acceder a tu cuenta.</p>';
            } else {
                echo '<h2>❌ Código inválido</h2>';
                echo '<p>El código de verificación es inválido o ya fue utilizado.</p>';
            }
        } else {
            echo '<h2>⚠️ Código no recibido</h2>';
            echo '<p>No se recibió código de verificación. Por favor, revisa el enlace en tu correo.</p>';
        }
        ?>
        <form action="login.php" method="get">
            <button type="submit">Aceptar</button>
        </form>
    </div>
</div>
</body>
</html>

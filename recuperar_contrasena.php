<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'db/conexion.php';
require 'vendor/autoload.php'; // Asegúrate de que esta ruta es correcta

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $entrada = trim($_POST['curp_o_correo']);

    // Buscar por CURP o EMAIL
    $stmt = $conexion->prepare("SELECT CURP, EMAIL FROM EGRESADO WHERE CURP = ? OR EMAIL = ?");
    $stmt->bind_param("ss", $entrada, $entrada);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 1) {
        $egresado = $resultado->fetch_assoc();
        $curp = $egresado['CURP'];
        $email = $egresado['EMAIL'];

        // Generar código único
        $codigo = bin2hex(random_bytes(16));

        // Guardar código
        $stmt_update = $conexion->prepare("UPDATE EGRESADO SET CODIGO_VERIFICACION = ? WHERE CURP = ?");
        $stmt_update->bind_param("ss", $codigo, $curp);
        $stmt_update->execute();

        // Crear enlace
        $link = "http://localhost:8012/seguimientoEgresados/includes/restablecer.php?codigo=$codigo";

        // Configurar PHPMailer
        $mail = new PHPMailer(true);

        try {
            // Configuración del servidor SMTP
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; // o tu servidor SMTP
            $mail->SMTPAuth = true;
            $mail->Username = 'valeriarco04@gmail.com'; // tu correo
            $mail->Password = 'faep pvlg whcn llkw'; // contraseña de aplicación
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            // Remitente y destinatario
            $mail->setFrom('seguimiento@itver.edu.mx', 'Sistema de Egresados ITV');
            $mail->addAddress($email);

            // Contenido
            $mail->isHTML(true);
            $mail->Subject = 'Recuperación de Contraseña - Seguimiento Egresados';
            $mail->Body    = "
                <p>Hola,</p>
                <p>Haz solicitado restablecer tu contraseña. Da clic en el siguiente enlace:</p>
                <p><a href='$link'>Restablecer Contraseña</a></p>
                <p>Si no solicitaste este cambio, puedes ignorar este mensaje.</p>
            ";

            $mail->send();
            header("Location: login.php?msg=success");
            exit;
        } catch (Exception $e) {
            header("Location: login.php?msg=error");
            exit;
        }
    } else {
        header("Location: login.php?msg=notfound");
        exit;
    }
}
?>

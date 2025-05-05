<?php
// correo.php: Enviar correo de verificacion con PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

function enviarCorreoVerificacion($correoDestino, $nombreEgresado, $codigoVerificacion) {
    $mail = new PHPMailer(true);

    try {
        // Configuración SMTP
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'valeriarco04@gmail.com';
        $mail->Password   = 'faep pvlg whcn llkw';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('valeriarco04@gmail.com', 'Seguimiento de Egresados');
        $mail->addAddress($correoDestino, $nombreEgresado);

        $mail->isHTML(true);
        $mail->Subject = 'Verifica tu cuenta - Seguimiento de Egresados';
        $mail->Body    = "<p>Hola <strong>$nombreEgresado</strong>,</p>
                          <p>Gracias por registrarte. Para activar tu cuenta, haz clic en el siguiente enlace:</p>
                          <p><a href='http://localhost:8012/seguimientoEgresados/verificar.php?codigo=$codigoVerificacion'>Verificar cuenta</a></p>
                          <p>Si no solicitaste este registro, puedes ignorar este mensaje.</p>";

        $mail->send();
        return true;
    } catch (Exception $e) {
        echo "Error al enviar correo: {$mail->ErrorInfo}"; // <-- Agrega esta línea para ver el error
        return false;
    }
}
?>

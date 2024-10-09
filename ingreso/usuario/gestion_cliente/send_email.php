<?php
require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
$config = include(__DIR__ . '/../../../config.php');

function sendConfirmationEmail($email, $token) {
    global $config; 
    $mail = new PHPMailer(true);

    try {
        // Configuración del servidor SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Usa el servidor SMTP de Gmail
        $mail->SMTPAuth = true;
        $mail->Username = $config['smtp_username']; // Tu dirección de correo de Gmail
        $mail->Password = $config['smtp_password']; // Tu contraseña de Gmail o contraseña de aplicación
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Remitente y destinatario
        $mail->setFrom($config['from_email'],'Mat Construcciones'); // Asegúrate de especificar el remitente
        $mail->addAddress($email);
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        $base_url = $protocol . '://' . $host . '/ingreso/usuario/gestion_cliente/';
 // Contenido del correo
 $mail->isHTML(true);
 $mail->Subject = 'Confirmación de registro';
 $confirmationLink = $base_url . "confirmar.php?token=$token";

        // Contenido del correo
        $mail->isHTML(true);
        $mail->Subject = 'Confirmación de registro';
        $mail->Body = "Hola,<br><br>Gracias por registrarte. Por favor, haz clic en el siguiente enlace para establecer tu contrasena:<br><br><a href='$confirmationLink'>Establecer contraseña</a>";

        $mail->send();
        echo "El correo de confirmación ha sido enviado.";
    } catch (Exception $e) {
        echo "Error al enviar el mensaje. Mailer Error: {$mail->ErrorInfo}";
    }
}
?>

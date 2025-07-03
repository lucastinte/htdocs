<?php
require 'ingreso/usuario/gestion_cliente/PHPMailer/Exception.php';
require 'ingreso/usuario/gestion_cliente/PHPMailer/PHPMailer.php';
require 'ingreso/usuario/gestion_cliente/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
$config = include('./config.php'); 
if (isset($_GET['email'])) {
    $email = urldecode($_GET['email']);
    
    $mail = new PHPMailer(true);

    try {
        // Configuración del servidor SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.hostinger.com'; 
        $mail->SMTPAuth = true;
        $mail->Username = $config['smtp_username']; // Cambia esto por tu correo
        $mail->Password = $config['smtp_password'];// Tu contraseña de Gmail o contraseña de aplicación
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Remitente y destinatario
        $mail->setFrom($config['from_email'], 'Mat Construcciones');
        $mail->addAddress($email);

        // Contenido del correo
        $mail->isHTML(true);
        $mail->Subject = 'Confirmación de postulación';
        $mail->Body    = "Hola,<br><br>Gracias por postularte. Hemos recibido tu postulación exitosamente. Nos pondremos en contacto contigo pronto.<br><br>Saludos,<br>Mat Construcciones.";

        $mail->send();
        
        echo "<script>\nwindow.location.href = '/talentos.php?success=1';\n</script>";
    } catch (Exception $e) {
        echo "Error al enviar el mensaje. Mailer Error: {$mail->ErrorInfo}";
    }
}
?>

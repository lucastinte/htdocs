<?php
// filepath: /Applications/XAMPP/xamppfiles/htdocs/ingreso/recuperar_contrasena.php
include('../db.php');
include("../header.php");
require 'usuario/gestion_cliente/PHPmailer/Exception.php';
require 'usuario/gestion_cliente/PHPMailer/PHPMailer.php';
require 'usuario/gestion_cliente/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
$config = include('../config.php');

$message = '';
$emailSent = false;
$errorMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = trim($_POST['dato']);
    $is_email = filter_var($input, FILTER_VALIDATE_EMAIL);
    $token = bin2hex(random_bytes(16));
    $found = false;
    $email = '';
    $usuario = '';
    $tabla = '';

    if ($is_email) {
        // Buscar en clientes por email
        $stmt = $conexion->prepare('SELECT id, email FROM clientes WHERE email = ?');
        $stmt->bind_param('s', $input);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $found = true;
            $email = $row['email'];
            $usuario = $row['email'];
            $tabla = 'clientes';
            // Guardar token
            $update = $conexion->prepare('UPDATE clientes SET token = ? WHERE id = ?');
            $update->bind_param('si', $token, $row['id']);
            $update->execute();
        }
        $stmt->close();
    } else {
        // Buscar en usuarios por usuario
        $stmt = $conexion->prepare('SELECT id_usuario, email, usuario FROM usuarios WHERE usuario = ?');
        $stmt->bind_param('s', $input);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $found = true;
            $email = $row['email'];
            $usuario = $row['usuario'];
            $tabla = 'usuarios';
            // Guardar token
            $update = $conexion->prepare('UPDATE usuarios SET token = ? WHERE id_usuario = ?');
            $update->bind_param('si', $token, $row['id_usuario']);
            $update->execute();
        }
        $stmt->close();
    }

    if ($found) {
        // Configurar PHPMailer
        $mail = new PHPMailer(true);
        try {
            // Configuración del servidor SMTP
            $mail->isSMTP();
            $mail->Host = 'smtp.hostinger.com';
            $mail->SMTPAuth = true;
            $mail->Username = $config['smtp_username'];
            $mail->Password = $config['smtp_password'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Configuración del correo
            $mail->setFrom($config['smtp_username'], 'Mat Construcciones');
            $mail->addAddress($email);
            $mail->CharSet = 'UTF-8';
            $mail->Subject = 'Recuperación de contraseña - Mat Construcciones';
            $mail->isHTML(true);

            // Determinar la URL correcta según el tipo de cuenta
            $base_url = $config['base_url'];
            $base_port = isset($config['base_port']) && $config['base_port'] != '80' ? ':' . $config['base_port'] : '';
            $link = $tabla === 'clientes' 
                ? "http://$base_url$base_port/ingreso/cliente/restablecer_contrasena.php?token=" . $token
                : "http://$base_url$base_port/ingreso/usuario/gestion_usuario/restablecer_contrasena.php?token=" . $token;

            $mail->Body = "Hola,<br><br>
            Recibimos una solicitud para restablecer tu contraseña. Si no hiciste esta solicitud, puedes ignorar este correo.<br><br>
            Por favor, haz clic en el siguiente enlace para establecer tu nueva contraseña:<br><br>
            <a href='$link'>Establecer nueva contraseña</a><br><br>
            Este enlace expirará por seguridad. Por favor, úsalo lo antes posible.<br><br>
            Saludos,<br>
            Mat Construcciones";

            $mail->send();
            $emailSent = true;
        } catch (Exception $e) {
            $errorMsg = "Error al enviar el correo: " . $mail->ErrorInfo;
        }
    } else {
        $errorMsg = "No se encontró ninguna cuenta con ese email o usuario.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña</title>
    <link rel="stylesheet" href="../index.css">
    <link rel="stylesheet" href="../modal-q.css">
    <script src="../modal-q.js"></script>
    <style>
        body {
            position: relative;
            background-image: url("../imagen/portada/p10.jpg");
            background-size: cover;
            background-repeat: no-repeat;
            height: 100vh;
            margin: 0;
            display: flex;
            flex-direction: column;
        }

        body::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.7);
            z-index: -1;
        }

        header {
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
        }

        .form-container {
            width: 100%;
            max-width: 400px;
            background-color: rgba(255, 255, 255, 0.9);
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin: auto;
            margin-top: 120px;
        }

        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
        }

        input[type="text"] {
            width: calc(100% - 20px);
            padding: 10px;
            margin: 5px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }

        button {
            background-color: blueviolet;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
        }

        button:hover {
            background-color: rgb(101, 33, 165);
        }

        button:active {
            background-color: rgb(129, 9, 241);
        }

        hr {
            background-color: #242323;
            height: 1px;
        }

        button + p + a {
            display: block;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <?php include("../header.php"); ?>

    <div class="form-container">
        <h2 style = "text-align:center;">RECUPERAR CONTRASEÑA</h2>
        <hr>
        <form method="post" autocomplete="off" id="recuperarForm">
            <div class="form-group">
                <label for="dato">Email (si eres cliente) o Usuario (si eres usuario interno):</label>
                <input type="text" id="dato" name="dato" placeholder="Email o Usuario" required>
            </div>
            <hr>
            <button type="submit">Enviar enlace</button>
        </form>
        <p></p>
        <a href="ingreso.php"><button>Volver</button></a>
    </div>

    <!-- Modal Q -->
    <div id="modal-q" class="modal-q" style="display: none;">
        <div class="modal-content">
            <span class="close" onclick="closeModalQ()">&times;</span>
            <h3 id="modal-q-title"></h3>
            <p id="modal-q-msg"></p>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        <?php if ($emailSent): ?>
            showModalQ('Se ha enviado un enlace de recuperación a tu correo electrónico.', false, 'recuperarForm', 'Correo Enviado', 'success');
            setTimeout(function() {
                window.location.href = 'ingreso.php';
            }, 3000);
        <?php endif; ?>
        
        <?php if ($errorMsg): ?>
            showModalQ(<?php echo json_encode($errorMsg); ?>, true, null, 'Error', 'error');
        <?php endif; ?>
    });
    </script>
</body>
</html>

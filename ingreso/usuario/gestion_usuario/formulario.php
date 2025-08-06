<?php
include('../../../db.php');
require '../gestion_cliente/PHPMailer/Exception.php';
require '../gestion_cliente/PHPMailer/PHPMailer.php';
require '../gestion_cliente/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// CORRECCIÓN FINAL: ruta correcta para incluir config.php (misma altura que modal-q.js)
$config = include($_SERVER['DOCUMENT_ROOT'] . '/config.php');

session_start();

// Verificar si se ha establecido la conexión correctamente
if (!$conexion) {
    die("Error de conexión: " . mysqli_connect_error());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Capturar los datos del formulario
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $dni = $_POST['dni'];
    $email = $_POST['email'];
    $fecha_nacimiento = $_POST['fecha_nacimiento'];
    $telefono = $_POST['telefono']; 
    $puesto = $_POST['puesto'];
    $permisos = $_POST['permisos'];
    $usuario = $_POST['usuario'];
    $localidad = $_POST['localidad'];
    $provincia = $_POST['provincia'];

    // Validaciones
    $errores = [];

    // 1. Verificar mayoría de edad
    $hoy = new DateTime();
    $fechaNacimiento = new DateTime($fecha_nacimiento);
    $edad = $hoy->diff($fechaNacimiento)->y;
    if ($edad < 18) {
        $errores[] = "Debes ser mayor de edad para registrarte.";
    }

    

    // 4. Verificar si el email, DNI o usuario ya existen
    $query_verificar = "SELECT 1 FROM usuarios WHERE email = ? OR dni = ? OR usuario = ?";
    $stmt_verificar = $conexion->prepare($query_verificar);
    $stmt_verificar->bind_param("sss", $email, $dni, $usuario);
    $stmt_verificar->execute();
    if ($stmt_verificar->get_result()->num_rows > 0) {
        $errores[] = "El email, DNI o usuario ya están registrados.";
    }
    $stmt_verificar->close();

    // Si hay errores, mostrarlos y detener el proceso
    if (!empty($errores)) {
        echo "<html><head>"
            . '<link rel="stylesheet" href="/modal-q.css">'
            . '<script src="/modal-q.js"></script>'
            . "<script>
            function closeModalQAndReload() {
                closeModalQ();
                window.location.href = 'formulario.php';
            }
        </script>"
            . "</head><body>"
            . '<div id="modal-q" style="display:none;position:fixed;z-index:9999;left:0;top:0;width:100vw;height:100vh;background:rgba(0,0,0,0.6);justify-content:center;align-items:center;">'
            . '<div class="modal-content" style="background:#fff;color:#181828;border-radius:24px;padding:40px 30px 30px 30px;text-align:center;min-width:320px;max-width:90vw;box-shadow:0 8px 32px rgba(0,0,0,0.25);transition:border 0.2s, color 0.2s;">'
            . '<h2 id="modal-q-title"></h2>'
            . '<p id="modal-q-msg"></p>'
            . '<button onclick="closeModalQAndReload()">OK</button>'
            . '</div></div>'
            . "<script>showModalQ('" . implode("<br>", $errores) . "', true, null, 'Errores de Validación');</script>"
            . "</body></html>";
        $conexion->close();
        exit();
    } 

    // Si NO hay errores, insertar el usuario en la base de datos
    // Generar un token único para el usuario
    $token = bin2hex(random_bytes(16)); 

    // Insertar el usuario en la base de datos (incluyendo el token)
    $query_insertar = "INSERT INTO usuarios (nombre, apellido, dni, email, fecha_nacimiento, telefono, puesto, permisos, usuario, token, localidad, provincia) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt_insertar = $conexion->prepare($query_insertar);
    $stmt_insertar->bind_param("ssssssssssss", $nombre, $apellido, $dni, $email, $fecha_nacimiento, $telefono, $puesto, $permisos, $usuario, $token, $localidad, $provincia);

    if ($stmt_insertar->execute()) {
        // Enviar correo electrónico de confirmación
        enviarCorreo($email, $nombre, $token);

        // Mostrar un mensaje de éxito al usuario
        echo "<html><head>"
            . '<link rel="stylesheet" href="/modal-q.css">'
            . '<script src="/modal-q.js"></script>'
            . "</head><body>"
            . '<div id="modal-q" style="display:none;position:fixed;z-index:9999;left:0;top:0;width:100vw;height:100vh;background:rgba(0,0,0,0.6);justify-content:center;align-items:center;">'
            . '<div class="modal-content" style="background:#fff;color:#181828;border-radius:24px;padding:40px 30px 30px 30px;text-align:center;min-width:320px;max-width:90vw;box-shadow:0 8px 32px rgba(0,0,0,0.25);transition:border 0.2s, color 0.2s;">'
            . '<h2 id="modal-q-title"></h2>'
            . '<p id="modal-q-msg"></p>'
            . '<button id="modal-q-ok-btn" onclick="closeModalQ(); window.location.href=\'gestionusuario.php\';">OK</button>'
            . '</div></div>'
            . "<script>showModalQ('Usuario registrado con éxito. Revisa tu correo electrónico para establecer tu contraseña.', false, null, 'Registro Exitoso');</script>"
            . "<script>
document.addEventListener('DOMContentLoaded', function() {
  var okBtn = document.getElementById('modal-q-ok-btn');
  if (okBtn) {
    okBtn.addEventListener('click', function() {
      window.location.href = 'gestionusuario.php';
    });
  }
});
</script>"
            . "</body></html>";
    } else {
        echo "<html><head>"
            . '<link rel="stylesheet" href="/modal-q.css">'
            . '<script src="/modal-q.js"></script>'
            . "</head><body>"
            . '<div id="modal-q" style="display:none;position:fixed;z-index:9999;left:0;top:0;width:100vw;height:100vh;background:rgba(0,0,0,0.6);justify-content:center;align-items:center;">'
            . '<div class="modal-content" style="background:#fff;color:#181828;border-radius:24px;padding:40px 30px 30px 30px;text-align:center;min-width:320px;max-width:90vw;box-shadow:0 8px 32px rgba(0,0,0,0.25);transition:border 0.2s, color 0.2s;">'
            . '<h2 id="modal-q-title"></h2>'
            . '<p id="modal-q-msg"></p>'
            . '<button onclick="closeModalQ()">OK</button>'
            . '</div></div>'
            . "<script>showModalQ('Error al registrar el usuario: " . $stmt_insertar->error . "', true, null, 'Error de Registro');</script>"
            . "</body></html>";
    }

    $stmt_insertar->close();
    $conexion->close();
    exit();
}


function enviarCorreo($email, $nombre, $token) {
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
        $mail->setFrom($config['from_email'], $config['from_name']);
        $mail->addAddress($email);

        // Construir URL base dinámica
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        $base_url = $protocol . '://' . $host . '/ingreso/usuario/gestion_usuario/';

        // Contenido del correo
        $mail->isHTML(true);
        $mail->Subject = 'Establece tu contraseña';
        $confirmationLink = $base_url . "restablecer_contrasena.php?token=$token";
        $mail->Body    = "Hola,<br><br>Gracias por registrarte.<br><br><b>Nombre:</b> $nombre<br><b>Email:</b> $email<br><br>Por favor, haz clic en el siguiente enlace para establecer tu contraseña:<br><br><a href='$confirmationLink'>Establecer nueva contraseña</a><br><br>Saludos,<br>Mat Construcciones.";

        $mail->send();
    } catch (Exception $e) {
        echo "Error al enviar el mensaje. Mailer Error: {$mail->ErrorInfo}";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Usuarios</title>
    <link rel="stylesheet" href="formulario.css">
    <link rel="stylesheet" href="/modal-q.css">
    <script src="/modal-q.js"></script>
</head>
<body>
    <div class="container">
        <h2>REGISTRO DE USUARIOS</h2>
        <form action="formulario.php" method="post" class="login-form">
            <div class="form-group">
                <label for="nombre">Nombre:</label>
                <input type="text" id="nombre" name="nombre" required>
            </div>
            <div class="form-group">
                <label for="apellido">Apellido:</label>
                <input type="text" id="apellido" name="apellido" required>
            </div>
            <div class="form-group">
                <label for="dni">DNI:</label>
                <input type="text" id="dni" name="dni" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="fecha_nacimiento">Fecha de Nacimiento:</label>
                <input type="date" id="fecha_nacimiento" name="fecha_nacimiento">
            </div>
            <div class="form-group">
                <label for="telefono">Teléfono:</label>
                <input type="text" id="telefono" name="telefono">
            </div>
            <div class="form-group">
                <label for="puesto">Puesto:</label>
                <input type="text" id="puesto" name="puesto">
            </div>
            <div class="form-group">
                <label for="permisos">Permisos:</label>
                <select id="permisos" name="permisos">
                    <option value="ninguno">Ninguno</option>
                    <option value="crear">Crear Usuarios</option>
                    <option value="modificar">Modificar Usuarios</option>
                </select>
            </div>
            <div class="form-group">
                <label for="usuario">Usuario:</label>
                <input type="text" id="usuario" name="usuario" required>
            </div>
            <div class="form-group">
                <label for="localidad">Localidad:</label>
                <input type="text" id="localidad" name="localidad" required>
            </div>
            <div class="form-group">
                <label for="provincia">Provincia:</label>
                <input type="text" id="provincia" name="provincia" required>
            </div>
            <!-- Se elimina el campo de contraseña -->
            <button type="submit">REGISTRAR</button>
        </form>
        <p></p>
        <a href="/ingreso/usuario/gestion_usuario/gestionusuario.php"><button>VOLVER</button></a>
    </div>

    <!-- Modal Q para mensajes -->
    <div id="modal-q" style="display:none;position:fixed;z-index:9999;left:0;top:0;width:100vw;height:100vh;background:rgba(0,0,0,0.6);justify-content:center;align-items:center;">
      <div class="modal-content" style="background:#fff;color:#181828;border-radius:24px;padding:40px 30px 30px 30px;text-align:center;min-width:320px;max-width:90vw;box-shadow:0 8px 32px rgba(0,0,0,0.25);transition:border 0.2s, color 0.2s;">
        <h2 id="modal-q-title"></h2>
        <p id="modal-q-msg"></p>
        <button onclick="closeModalQ()">OK</button>
      </div>
    </div>
</body>
</html>

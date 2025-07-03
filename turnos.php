<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include('db.php');
include "header.php";
require 'ingreso/usuario/gestion_cliente/PHPMailer/Exception.php';
require 'ingreso/usuario/gestion_cliente/PHPMailer/PHPMailer.php';
require 'ingreso/usuario/gestion_cliente/PHPMailer/SMTP.php';
require('fpdf186/fpdf.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
$config = include('./config.php'); 
// Validar y sanitizar datos del formulario
function sanitize_input($data) {
    return htmlspecialchars(trim($data));
}

// Función para crear el PDF con la información del turno
function generarPDF($nombre, $apellido, $email, $telefono, $fecha_hora, $comentario) {
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 10, 'Confirmacion de Turno - Mat Construcciones', 0, 1, 'C');

    $pdf->SetFont('Arial', '', 12);
    $pdf->Ln(10);
    $pdf->Cell(40, 10, "Nombre: $nombre $apellido");
    $pdf->Ln();
    $pdf->Cell(40, 10, "Email: $email");
    $pdf->Ln();
    $pdf->Cell(40, 10, "Teléfono: $telefono");
    $pdf->Ln();
    $pdf->Cell(40, 10, "Fecha y Hora: $fecha_hora");
    $pdf->Ln();
    $pdf->Cell(40, 10, "Comentario: $comentario");

    // Guardar el PDF en una ruta temporal segura
    $rutaPDF = __DIR__ . "/tmp/confirmacion_turno_" . time() . ".pdf";
    $pdf->Output('F', $rutaPDF);

    return $rutaPDF;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = sanitize_input($_POST['nombre']);
    $apellido = sanitize_input($_POST['apellido']);
    $email = sanitize_input($_POST['email']);
    $telefono = sanitize_input($_POST['telefono']);
    $fecha_hora = sanitize_input($_POST['fecha_hora']);
    $comentario = sanitize_input($_POST['comentario']);
    $presupuesto = isset($_POST['presupuesto']) ? 1 : 0;
    $cliente_existente = isset($_POST['cliente_existente']) ? 1 : 0;

    // Insertar el turno
    $insert_query = "INSERT INTO turnos (nombre, apellido, email, telefono, fecha, hora, comentario, presupuesto, cliente_existente) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $fecha = date('Y-m-d', strtotime($fecha_hora));
    $hora = date('H:i:s', strtotime($fecha_hora));
    $stmt = mysqli_prepare($conexion, $insert_query);
    mysqli_stmt_bind_param($stmt, "sssssssii", $nombre, $apellido, $email, $telefono, $fecha, $hora, $comentario, $presupuesto, $cliente_existente);
    mysqli_stmt_execute($stmt);

    if (mysqli_stmt_affected_rows($stmt) > 0) {
        // Actualizar disponibilidad de la hora
        $update_query = "UPDATE horarios_disponibles SET disponible = FALSE WHERE fecha_hora = ?";
        $stmt_update = mysqli_prepare($conexion, $update_query);
        mysqli_stmt_bind_param($stmt_update, 's', $fecha_hora);
        mysqli_stmt_execute($stmt_update);
        mysqli_stmt_close($stmt_update);

        $message_turno = "Turno agendado exitosamente.";

        // Generar el PDF con la información del turno
        $rutaPDF = generarPDF($nombre, $apellido, $email, $telefono, $fecha_hora, $comentario);

        // Enviar el correo con PHPMailer y adjuntar el PDF
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.hostinger.com';
            $mail->SMTPAuth = true;
            $mail->Username = $config['smtp_username']; // Cambia esto por tu correo
            $mail->Password = $config['smtp_password']; // Cambia esto por tu contraseña de Gmail
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom($config['from_email'], 'Mat Construcciones');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Confirmacion de Turno - Mat Construcciones';
            $mail->Body = "Hola $nombre $apellido,<br><br>Tu turno ha sido confirmado para el día $fecha a las $hora.<br>Adjuntamos un PDF con los detalles de tu turno.<br><br>Saludos,<br>Mat Construcciones.";

            // Adjuntar el PDF
            $mail->addAttachment($rutaPDF);

            $mail->send();

            // Eliminar el archivo PDF después de enviar el correo
            unlink($rutaPDF);

            // Mensaje de confirmación
            header('Location: turnos.php?success=1');
            exit();
        } catch (Exception $e) {
            echo "Error al enviar el mensaje. Mailer Error: {$mail->ErrorInfo}";
        }
    } else {
        $message_turno = "Error al agendar el turno.";
    }

    mysqli_stmt_close($stmt);
}

// Obtener fechas y horas disponibles a partir de la fecha y hora actuales
$query_disponibles = "SELECT fecha_hora FROM horarios_disponibles WHERE disponible = TRUE AND fecha_hora >= NOW() ORDER BY fecha_hora ASC";
$result_disponibles = mysqli_query($conexion, $query_disponibles);
$success = isset($_GET['success']) && $_GET['success'] == '1';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mat Construcciones - Turnos</title>
    <link rel="stylesheet" href="index.css">
    <style>
        .message {
            text-align: center;
            padding: 10px;
            margin: 10px auto;
            width: 80%;
            max-width: 600px;
            border-radius: 5px;
            font-size: 16px;
        }
        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            text-align: center;
            padding: 10px;
            margin: 10px auto;
            width: 80%;
            max-width: 600px;
            border-radius: 5px;
            font-size: 16px;
        }
        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        body {
            position: relative;
            background-image: url("./imagen/nosotros/turnos.png");
            background-size: cover;
            background-repeat: no-repeat;
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
     .checkbox-group {
    display: flex;
    flex-direction: column;
    gap: 1.5em;
    margin-bottom: 2em;
    align-items: flex-start;
}

.checkbox-item {
    display: flex;
    align-items: center;
    gap: 0.75em;
    font-size: 1.1em;
    background-color: rgba(255, 255, 255, 0.6);
    padding: 8px 12px;
    border-radius: 8px;
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1);
    width: 100%;
}

.checkbox-item label {
    flex: 1;
    margin: 0;
    cursor: pointer;
}

.checkbox-item input[type="checkbox"] {
    width: 20px;
    height: 20px;
    accent-color: blueviolet;
}

    </style>
</head>
<body>
    <main>
        <h1 class="color-acento">Agenda un turno con Nosotros</h1>
        <section id="turnos">
            <div class="container">
                <?php if ($success) { ?>
                    <p class="message success">¡El turno ha sido agendado y el correo de confirmación ha sido enviado con éxito!</p>
                <?php } ?>
                <?php if (isset($message_turno)) { ?>
                    <p class="message <?php echo strpos($message_turno, 'exitosamente') !== false ? 'success' : 'error'; ?>"><?php echo htmlspecialchars($message_turno); ?></p>
                <?php } ?>

                <form action="turnos.php" method="post">
                    <label for="nombre">Nombre:</label>
                    <input type="text" id="nombre" name="nombre" required>

                    <label for="apellido">Apellido:</label>
                    <input type="text" id="apellido" name="apellido" required>

                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>

                    <label for="telefono">Teléfono:</label>
                    <input type="tel" id="telefono" name="telefono" required>

                    <label for="fecha_hora">Fecha y Hora:</label>
                    <select id="fecha_hora" name="fecha_hora" required>
                        <option value="">Selecciona una fecha y hora</option>
                        <?php while ($row_disponibles = mysqli_fetch_assoc($result_disponibles)) { ?>
                            <option value="<?php echo htmlspecialchars($row_disponibles['fecha_hora']); ?>">
                                <?php echo htmlspecialchars(date('Y-m-d H:i', strtotime($row_disponibles['fecha_hora']))); ?>
                            </option>
                        <?php } ?>
                    </select>

                    <label for="comentario">Motivo de consulta:</label>
                    <textarea id="comentario" name="comentario" placeholder="Ejemplo: presupuestar casa, consultar costos, etc."></textarea>

                    <div class="checkbox-group">
                        <div class="checkbox-item">
                            <label for="presupuesto">¿Ya realizó un presupuesto?</label>
                            <input type="checkbox" id="presupuesto" name="presupuesto">
                        </div>
                        <div class="checkbox-item">
                            <label for="cliente_existente">¿Es un cliente existente?</label>
                            <input type="checkbox" id="cliente_existente" name="cliente_existente">
                        </div>
                    </div>

                    <button type="submit">Agendar Turno</button>
                </form>
            </div>
        </section>
    </main>
    <footer>
    <div class="container">
        <p>&copy;Mat Construcciones</p>
    </div>
    </footer>
</body>
</html>

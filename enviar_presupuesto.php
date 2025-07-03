<?php
include('db.php');
include "header.php";
require 'ingreso/usuario/gestion_cliente/PHPmailer/Exception.php';
require 'ingreso/usuario/gestion_cliente/PHPMailer/PHPMailer.php';
require 'ingreso/usuario/gestion_cliente/PHPMailer/SMTP.php';
require('fpdf186/fpdf.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
$config = include('./config.php'); 
$query = "SELECT fecha_hora FROM horarios_disponibles WHERE disponible = TRUE AND fecha_hora > NOW()";$result = mysqli_query($conexion, $query);
$turnosDisponibles = [];
while ($row = mysqli_fetch_assoc($result)) {
    $turnosDisponibles[] = $row['fecha_hora'];
}
// Verifica si se ha enviado el formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $ocupacion = $_POST['ocupacion'];
    $habitantes = $_POST['habitantes'];
    $seguridad = $_POST['seguridad'];
    $trabajo_en_casa = $_POST['trabajo_en_casa'];
    $salud = $_POST['salud'];
    $telefono = $_POST['telefono'];
    $email = $_POST['email'];
    $direccion = $_POST['direccion'];
    $fobias = $_POST['fobias'];
    $intereses = $_POST['intereses'];
    $rutinas = $_POST['rutinas'];
    $pasatiempos = $_POST['pasatiempos'];
    $visitas = $_POST['visitas'];
    $detalles_visitas = $_POST['detalles_visitas'];
    $vehiculos = $_POST['vehiculos'];
    $mascotas = $_POST['mascotas'];
    $aprendizaje = $_POST['aprendizaje'];
    $negocio = $_POST['negocio'];
    $muebles = $_POST['muebles'];
    $detalles_casa = $_POST['detalles_casa'];
    $turnoSeleccionado = $_POST['turno'];
   // ... (resto del código para guardar la información en la base de datos)

   // Actualizar la disponibilidad del turno en la base de datos
   $update_query = "UPDATE horarios_disponibles SET disponible = FALSE WHERE fecha_hora = ?";
   $stmt = mysqli_prepare($conexion, $update_query);
   mysqli_stmt_bind_param($stmt, "s", $turnoSeleccionado);
   mysqli_stmt_execute($stmt);

    // Consulta para insertar en la base de datos
    $insert_query = "INSERT INTO presupuestos (nombre, ocupacion, habitantes, seguridad, trabajo_en_casa, salud, telefono, email, direccion, fobias, intereses, rutinas, pasatiempos, visitas, detalles_visitas, vehiculos, mascotas, aprendizaje, negocio, muebles, detalles_casa, turno) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"; 
    $stmt = mysqli_prepare($conexion, $insert_query);

    // Vincular los parámetros (incluyendo todos los campos)
    mysqli_stmt_bind_param($stmt, "ssssssssssssssssssssss", $nombre, $ocupacion, $habitantes, $seguridad, $trabajo_en_casa, $salud, $telefono, $email, $direccion, $_POST['fobias'], $_POST['intereses'], $_POST['rutinas'], $_POST['pasatiempos'], $_POST['visitas'], $_POST['detalles_visitas'], $_POST['vehiculos'], $_POST['mascotas'], $_POST['aprendizaje'], $_POST['negocio'], $_POST['muebles'], $_POST['detalles_casa'], $turnoSeleccionado);

    mysqli_stmt_execute($stmt);

    if (mysqli_stmt_affected_rows($stmt) > 0) {
        $message = "Presupuesto enviado exitosamente.";

        // Crear el PDF
        $pdf = new FPDF();
        $pdf->AddPage();
        
        // Agregar logo (asegúrate de tener un archivo logo.png en la misma carpeta)
        $pdf->Image('logo.png',10,10,30);
        $pdf->Ln(20); // Salto de línea
        
        // Título del documento
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(0, 10, 'Cuestionario', 0, 1, 'C');
        $pdf->Ln(10); // Salto de línea
        
        // Datos del presupuesto
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 10, 'Apellido y Nombre: ' . $nombre, 0, 1);
        $pdf->Cell(0, 10, 'Ocupacion: ' . $ocupacion, 0, 1);
        $pdf->Cell(0, 10, 'Cantidad de Habitantes: ' . $habitantes, 0, 1);
        $pdf->Cell(0, 10, 'Seguridad: ' . $seguridad, 0, 1);
        $pdf->Cell(0, 10, 'Trabajo en Casa: ' . $trabajo_en_casa, 0, 1);
        $pdf->Cell(0, 10, 'Salud: ' . $salud, 0, 1);
        $pdf->Cell(0, 10, 'Telefono: ' . $telefono, 0, 1);
        $pdf->Cell(0, 10, 'Email: ' . $email, 0, 1);
        $pdf->Cell(0, 10, 'Direccion: ' . $direccion, 0, 1);
 // Agregar los nuevos campos al PDF
 $pdf->Cell(0, 10, 'Fobias: ' . $fobias, 0, 1);
 $pdf->Cell(0, 10, 'Intereses: ' . $intereses, 0, 1);
 $pdf->Cell(0, 10, 'Rutinas: ' . $rutinas, 0, 1);
 $pdf->Cell(0, 10, 'Pasatiempos: ' . $pasatiempos, 0, 1);
 $pdf->Cell(0, 10, 'Visitas: ' . $visitas, 0, 1);
 $pdf->Cell(0, 10, 'Detalles de Visitas: ' . $detalles_visitas, 0, 1);
 $pdf->Cell(0, 10, 'Vehiculos: ' . $vehiculos, 0, 1);
 $pdf->Cell(0, 10, 'Mascotas: ' . $mascotas, 0, 1);
 $pdf->Cell(0, 10, 'Aprendizaje: ' . $aprendizaje, 0, 1);
 $pdf->Cell(0, 10, 'Negocio: ' . $negocio, 0, 1);
 $pdf->Cell(0, 10, 'Muebles: ' . $muebles, 0, 1);
 $pdf->Cell(0, 10, 'Detalles de la Casa: ' . $detalles_casa, 0, 1);
 $pdf->Cell(0, 10, 'Turno: ' . $turnoSeleccionado, 0, 1);
        // Guardar el PDF de manera temporal
        $filePath = 'Presupuesto_' . time() . '.pdf';
        $pdf->Output('F', $filePath); // Guardar el archivo en el servidor de manera temporal

        // Enviar email de confirmación con el PDF adjunto
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.hostinger.com';
            $mail->SMTPAuth = true;
            $mail->Username = $config['smtp_username']; // Cambia esto por tu correo
            $mail->Password = $config['smtp_password'];// Tu contraseña de Gmail o contraseña de aplicación
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom($config['from_email'], 'Mat Construcciones');
            $mail->addAddress($email);
            $mail->addAttachment($filePath); // Adjuntar el archivo PDF
            $mail->isHTML(true);
            $mail->Subject = 'Confirmación de presupuesto';
            $mail->Body    = "Hola,<br><br>Gracias por enviar tu presupuesto. Hemos recibido tu información exitosamente.<br><br>Saludos,<br>Mat Construcciones.";

            $mail->send();

            // Eliminar el archivo temporal
            if (file_exists($filePath)) {
                unlink($filePath);
            }

            // Redirigir a la misma página con success=1 para mostrar mensaje de éxito
            header('Location: enviar_presupuesto.php?success=1');
            exit();
        } catch (Exception $e) {
            echo "Error al enviar el mensaje. Mailer Error: {$mail->ErrorInfo}";
        }

    } else {
        $message = "Error al enviar el presupuesto.";
    }

    mysqli_stmt_close($stmt);
    mysqli_close($conexion);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enviar Presupuesto</title>
    <link rel="stylesheet" href="index.css"> <!-- Cambia el nombre del archivo de CSS si es necesario -->
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
            background-image: url("./imagen/nosotros/imagen3.jpg"); 
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
            background-color: rgba(255, 255, 255, 0.7); /* Ajusta la opacidad según lo necesites */
            z-index: -1; /* Asegura que esté detrás del contenido */
        }

        .info-box {
            background: rgba(120,120,120,0.18);
            color: #222;
            border-radius: 12px;
            padding: 18px 22px;
            margin: 0 auto 28px auto;
            max-width: 700px;
            font-size: 1.13em;
            box-shadow: 0 2px 12px rgba(80,80,80,0.07);
            text-align: center;
            border: 1px solid rgba(120,120,120,0.13);
        }
    </style>
    
</head>
<body>
    <main>
        <section id="presupuesto-form">
            <h1 class="color-acento">Conocerte es el primer paso</h1>
            <div class="info-box">
              Completá este cuestionario para ayudarte a construir tu espacio ideal.<br>
              <b>Recordá que también debés agendar un turno para la cita.</b>
            </div>
            <form action="enviar_presupuesto.php" method="post">
                <label for="nombre">Apellido y Nombre:</label>
                <input type="text" id="nombre" name="nombre" required>

                <label for="ocupacion">¿Cuál es su ocupación?</label>
                <textarea id="ocupacion" name="ocupacion" rows="2" required></textarea>

                <label for="habitantes">¿Cuántas personas ocupan la casa?</label>
                <textarea id="habitantes" name="habitantes" rows="2" required></textarea>

                <label for="seguridad">¿Cuál es el tiempo habitual de permanencia en su casa? (Seguridad)</label>
                <textarea id="seguridad" name="seguridad" rows="2" required></textarea>

                <label for="trabajo_en_casa">¿En qué espacio prefiere trabajar cuando está en su casa y cuánto tiempo lo hace en promedio al día? ¿Cómo sería su espacio de trabajo ideal?</label>
                <textarea id="trabajo_en_casa" name="trabajo_en_casa" rows="4" required></textarea>

                <label for="salud">¿Algún miembro o allegado tiene alguna discapacidad?</label>
                <textarea id="salud" name="salud" rows="2" required></textarea>
                
                <!-- Nuevos campos -->
                <label for="telefono">Teléfono:</label>
                <input type="text" id="telefono" name="telefono" required>

                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>

                <label for="direccion">Dirección:</label>
                <input type="text" id="direccion" name="direccion" required>

                <label for="fobias">¿Tiene alguna fobia?</label>
                <input type="text" id="fobias" name="fobias">

                <label for="intereses">En base a su experiencia o instancia en otras casas. ¿Qué cosas le molestan? ¿Hay cosas que le sean de mucho interés en su casa? (me molesta: Espacios muy chicos, pasillos largos, techos bajos etc. Me interesa: ventanas grandes, espacio para mis plantas, etc)</label>
                <textarea id="intereses" name="intereses" rows="4"></textarea>


                <label for="rutinas">¿Qué actividad realiza desde que se levanta a la mañana hasta que se acuesta por la noche? (ej. Desayunar. Trabajar en casa. Hacer ejercicios en casa. Salir siempre por las tardes. etc)</label>
                <textarea id="rutinas" name="rutinas" rows="4"></textarea>

                <label for="pasatiempos">¿Tiene pasatiempos? ¿Cuáles?</label>
                <input type="text" id="pasatiempos" name="pasatiempos">


                <label for="visitas">¿Recibe con frecuencia visitas? (diarias/eventuales)</label>
                <select id="visitas" name="visitas">
                    <option value="Diarias">Diarias</option>
                    <option value="Eventuales">Eventuales</option>
                    <option value="No">No</option>
                </select>

                <label for="detalles_visitas">Detalles (Terminaciones y pedidos especiales) Ej. Espacio para que duerman mis 5 perros.</label>
                <textarea id="detalles_visitas" name="detalles_visitas" rows="4"></textarea>


                <label for="vehiculos">¿Cuántos vehículos posee? ¿Planea adquirir otro? ¿Cuáles?</label>
                <textarea id="vehiculos" name="vehiculos" rows="2"></textarea>


                <label for="mascotas">¿Tiene o planea tener mascotas? ¿De qué tipo?</label>
                <input type="text" id="mascotas" name="mascotas">


                <label for="aprendizaje">¿Qué le gustaría aprender? (ej, me gustaría aprender a reparar mi auto)</label>
                <input type="text" id="aprendizaje" name="aprendizaje">


                <label for="negocio">¿Le gustaría anexar algún negocio a su vivienda? Ej. Haré dptos. para alquilar. Pondré una despensa. etc</label>
                <textarea id="negocio" name="negocio" rows="2"></textarea>


                <label for="muebles">¿Tiene algún mueble o algo que tenga pensado implementar en la casa? (ej, tengo un mueble de roble original que heredé de mi abuela de medida 1m x 0.60m que quiero que entre en mi living.)</label>
                <textarea id="muebles" name="muebles" rows="4"></textarea>


                <label for="detalles_casa">¿Qué clase de detalles le gustan? Ejemplo: Jacuzzi, Luces dicroicas, molduras en las puertas. etc</label>
                <input type="text" id="detalles_casa" name="detalles_casa">
                <label for="turno">Seleccione un turno para la cita:</label>
                <select id="turno" name="turno">
                    <?php if (!empty($turnosDisponibles)) : ?>
                        <?php foreach ($turnosDisponibles as $turno) : ?>
                            <option value="<?php echo $turno; ?>"><?php echo $turno; ?></option>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <option value="">No hay turnos disponibles</option>
                    <?php endif; ?>
                </select>
                
                <button type="submit">Enviar</button>
            </form>
        </section>
    </main>

    <!-- Modal Q reutilizable -->
    <div id="modal-q" style="display:none">
      <div class="modal-content">
        <h2 id="modal-q-title"></h2>
        <p id="modal-q-msg"></p>
        <button onclick="closeModalQ()">OK</button>
      </div>
    </div>
    <link rel="stylesheet" href="modal-q.css">
    <script src="modal-q.js"></script>
    <script>
    <?php if (isset($_GET['success']) && $_GET['success'] == '1') { ?>
      showModalQ('¡El presupuesto ha sido enviado y el correo de confirmación ha sido enviado exitosamente!', false, null, '¡Presupuesto Enviado!');
    <?php } elseif (isset($message)) { ?>
      showModalQ('<?php echo htmlspecialchars($message); ?>', <?php echo (strpos($message, 'Error') !== false ? 'true' : 'false'); ?>, null, <?php echo (strpos($message, 'Error') !== false ? "'Error al Enviar Presupuesto'" : "'¡Presupuesto Enviado!'"); ?>);
    <?php } ?>
    </script>

    <footer>
        <div class="container">
            <p>&copy; Mat Construcciones</p>
        </div>
    </footer>
</body>
</html>

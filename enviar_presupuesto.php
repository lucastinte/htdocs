<?php
include('db.php');
include "header.php";
require 'ingreso/usuario/gestion_cliente/PHPMailer/Exception.php';
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
        
        .tabla-referencia {
            background: rgba(255, 255, 255, 0.95);
            padding: 30px;
            margin: 20px auto;
            max-width: 900px;
            border-radius: 8px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
        }

        .titulo-carpeta {
            text-align: center;
            margin-bottom: 30px;
            padding: 20px;
            background: linear-gradient(135deg, #8a2be2, #9370db);
            border-radius: 8px;
            color: white;
            box-shadow: 0 4px 15px rgba(138, 43, 226, 0.2);
        }

        .titulo-carpeta h2 {
            margin: 0;
            font-size: 2em;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
        }

        .titulo-carpeta p {
            margin: 0;
            font-size: 1.2em;
            opacity: 0.9;
        }

        .tabla-referencia table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background: white;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.05);
            border-radius: 8px;
            overflow: hidden;
        }

        .tabla-referencia th {
            background: linear-gradient(135deg, #9370db, #8a2be2);
            color: white;
            font-weight: bold;
            padding: 15px;
            text-align: left;
            border: none;
            text-transform: uppercase;
            font-size: 0.9em;
            letter-spacing: 0.5px;
        }

        .tabla-referencia td {
            padding: 15px;
            border: none;
            border-bottom: 1px solid rgba(138, 43, 226, 0.1);
        }

        .tabla-referencia tbody tr:nth-child(even) {
            background-color: rgba(147, 112, 219, 0.05);
        }

        .tabla-referencia tbody tr:hover {
            background-color: rgba(138, 43, 226, 0.1);
            transition: background-color 0.3s ease;
        }

        .tabla-referencia tfoot {
            font-weight: bold;
            background: rgba(138, 43, 226, 0.1);
        }

        .tabla-referencia tfoot td {
            border-top: 2px solid rgba(138, 43, 226, 0.2);
            color: #8a2be2;
            font-size: 1.1em;
        }

        .observaciones {
            margin-top: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 6px;
        }

        .observaciones h3 {
            color: #333;
            margin-bottom: 15px;
        }

        .observaciones p {
            margin-bottom: 10px;
            line-height: 1.5;
            color: #666;
        }

        .boton-inicio {
            text-align: center;
            padding: 40px 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 20px;
        }

        .btn-comenzar {
            background: linear-gradient(135deg, #8a2be2, #9370db);
            border: none;
            border-radius: 15px;
            padding: 20px 40px;
            color: white;
            cursor: pointer;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            box-shadow: 0 4px 15px rgba(138, 43, 226, 0.3);
            width: auto;
            min-width: 300px;
        }

        .btn-comenzar:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 20px rgba(138, 43, 226, 0.4);
        }

        .btn-comenzar h1 {
            font-size: 2.5em;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .leyenda {
            background: rgba(138, 43, 226, 0.1);
            padding: 20px;
            border-radius: 10px;
            max-width: 600px;
            margin: 0 auto;
        }

        .leyenda p {
            margin: 10px 0;
            font-size: 1.2em;
            color: #666;
        }

        .leyenda .slogan {
            font-size: 1.4em;
            color: #8a2be2;
            font-weight: bold;
            margin-top: 15px;
        }

        #formulario-container {
            opacity: 0;
            max-height: 0;
            overflow: hidden;
            transition: opacity 0.5s ease, max-height 0.5s ease;
            scroll-margin-top: 100px;
        }

        #formulario-container.visible {
            opacity: 1;
            max-height: 5000px; /* Valor alto para asegurar que todo el contenido sea visible */
        }

        .info-box {
            margin-top: 120px !important;
            margin-bottom: 40px !important;
        }

        .btn-volver-container {
            text-align: center;
            padding: 40px 20px;
            margin: 20px auto;
            max-width: 600px;
            border-top: 1px solid rgba(138, 43, 226, 0.2);
        }

        .btn-volver {
            background: linear-gradient(135deg, #8a2be2, #9370db);
            border: none;
            border-radius: 30px;
            padding: 15px 40px;
            color: white;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 1.2em;
            display: inline-flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 4px 15px rgba(138, 43, 226, 0.2);
        }

        .btn-volver:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(138, 43, 226, 0.4);
            background: linear-gradient(135deg, #9370db, #8a2be2);
        }

        .btn-volver .flecha {
            font-size: 1.4em;
            line-height: 1;
            transition: transform 0.3s ease;
        }

        .btn-volver:hover .flecha {
            transform: translateX(-5px);
        }

        .tabla-referencia {
            transition: opacity 0.5s ease, transform 0.5s ease;
        }

        .tabla-referencia.oculto {
            opacity: 0;
            transform: translateY(20px);
            pointer-events: none;
            position: absolute;
        }

        #presupuesto-form {
            transition: transform 0.5s ease;
        }

        #presupuesto-form.arriba {
            transform: translateY(-100vh);
        }
    </style>
    
</head>
<body>
    <main>
        <section id="presupuesto-referencia" class="tabla-referencia">
            <div class="titulo-carpeta">
                <h2>CARPETA TÉCNICA PLANO DE CONSTRUCCIÓN</h2>
                <p>Estructura, Eléctrico, Sanitario y Gas</p>
            </div>

            <table border="1" cellpadding="5" cellspacing="0">
                <thead>
                    <tr>
                        <th>ITEM</th>
                        <th>DESCRIPCIÓN</th>
                        <th>Unid.</th>
                        <th>Cant.</th>
                        <th>Unitario</th>
                        <th>Total M.O.</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td>1</td><td>DISEÑO DE PLANOS</td><td>Global</td><td>1</td><td>$1.000.000</td><td>$1.000.000</td></tr>
                    <tr><td>2</td><td>PLOTEO DE PLANOS</td><td>Global</td><td>1</td><td>$60.000</td><td>$60.000</td></tr>
                    <tr><td>3</td><td>FICHA PARCELAREA</td><td>Un</td><td>1</td><td>$5.000</td><td>$5.000</td></tr>
                    <tr><td>4</td><td>CERTIFICADO ÚNICO</td><td>Un</td><td>1</td><td>$15.000</td><td>$15.000</td></tr>
                    <tr><td>5</td><td>SELLADO COL. TÉCNICOS</td><td>Un</td><td>1</td><td>$150.000</td><td>$150.000</td></tr>
                    <tr><td>7</td><td>DERECHO DE CONSTRUCCIÓN</td><td>m²</td><td>1</td><td>$300.000</td><td>$300.000</td></tr>
                    <tr><td>8</td><td>PAGO DE SELLADO, NOTA DE INGRESO</td><td>Un</td><td>1</td><td>$5.000</td><td>$5.000</td></tr>
                    <tr><td>9</td><td>PAGO DE SELLADO, PLANOS MUN.</td><td>Un</td><td>1</td><td>$10.000</td><td>$10.000</td></tr>
                    <tr><td>10</td><td>PLOTEO CARTEL DE OBRA</td><td>Un</td><td>1</td><td>$40.000</td><td>$40.000</td></tr>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="5"><strong>Total M.O.</strong></td>
                        <td><strong>$1.585.000</strong></td>
                    </tr>
                </tfoot>
            </table>

            <div class="observaciones">
                <h3>OBSERVACIONES</h3>
                <p>Para empezar el trabajo se pide abonar el 50% del ITEM DISEÑO DE PLANOS, luego el 30% una vez presentado todos los planos para la firma del comitente, y la cancelación del mismo una vez se entrega la carpeta aprobada por la municipalidad.</p>
                <p>Los precios de item 2 a 10 son variables, se rendirán con boleta, los precios son estimativos.</p>
                <p>El presupuesto no incluye la <strong>DIRECCIÓN TÉCNICA</strong>, pero sí visitas periódicas mensuales.</p>
            </div>
        </section>

        <section id="presupuesto-form">
            <div class="boton-inicio">
                <button id="mostrarFormulario" class="btn-comenzar">
                    <h1>¡COMIENZA YA!</h1>
                </button>
                <div class="leyenda">
                    <p>Llena un pre-formulario y agenda un turno</p>
                    <p class="slogan">Tu casa, tu hogar te espera. ¡No lo dudes!</p>
                </div>
            </div>
            <div id="formulario-container" style="display: none;">
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
            <div class="btn-volver-container" style="display: none;">
                <button id="btnVolver" class="btn-volver">
                    <span class="flecha">←</span> Volver a la tabla de presupuesto
                </button>
            </div>
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
    document.addEventListener('DOMContentLoaded', function() {
        const boton = document.getElementById('mostrarFormulario');
        const formulario = document.getElementById('formulario-container');
        const btnVolver = document.getElementById('btnVolver');
        const tablaReferencia = document.querySelector('.tabla-referencia');
        const presupuestoForm = document.getElementById('presupuesto-form');
        const btnVolverContainer = document.querySelector('.btn-volver-container');
        
        function scrollToForm() {
            formulario.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
        
        boton.addEventListener('click', function() {
            // Ocultar tabla con animación
            tablaReferencia.classList.add('oculto');
            
            // Ocultar botón de comenzar y mostrar formulario
            boton.parentElement.style.display = 'none';
            btnVolverContainer.style.display = 'block';
            formulario.style.display = 'block';
            
            setTimeout(() => {
                formulario.classList.add('visible');
                scrollToForm(); // Scroll al formulario después de que sea visible
            }, 50);
        });

        btnVolver.addEventListener('click', function() {
            // Mostrar tabla nuevamente
            tablaReferencia.classList.remove('oculto');
            
            // Ocultar formulario y mostrar botón de comenzar
            formulario.classList.remove('visible');
            btnVolverContainer.style.display = 'none';
            
            setTimeout(() => {
                formulario.style.display = 'none';
                boton.parentElement.style.display = 'flex';
            }, 500);
        });
    });

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

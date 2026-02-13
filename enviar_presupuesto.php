<?php
session_start();
require 'ingreso/usuario/gestion_cliente/PHPMailer/Exception.php';
require 'ingreso/usuario/gestion_cliente/PHPMailer/PHPMailer.php';
require 'ingreso/usuario/gestion_cliente/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

include('db.php');

// Fetch all config for the logic
$sql_mo_config = "SELECT * FROM cotizacion_config WHERE clave = 'm2_base'";
$res_mo_config = mysqli_query($conexion, $sql_mo_config);
$row_mo_config = mysqli_fetch_assoc($res_mo_config);
$project_prices = [
    'unifamiliar' => $row_mo_config['valor_unifamiliar'],
    'colectiva'   => $row_mo_config['valor_colectiva'],
    'quincho'     => $row_mo_config['valor_quincho']
];
$global_mo_percent = $row_mo_config['porcentaje_mo'] ?? 3;

include "header.php";
$query = "SELECT fecha_hora FROM horarios_disponibles WHERE disponible = TRUE AND fecha_hora > NOW()";$result = mysqli_query($conexion, $query);
$turnosDisponibles = [];
while ($row = mysqli_fetch_assoc($result)) {
    $turnosDisponibles[] = $row['fecha_hora'];
}
// Verifica si se ha enviado el formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'] ?? '';
    $ocupacion = $_POST['ocupacion'] ?? '';
    $habitantes = $_POST['habitantes'] ?? '';
    $seguridad = $_POST['seguridad'] ?? '';
    $trabajo_en_casa = $_POST['trabajo_en_casa'] ?? '';
    $salud = $_POST['salud'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $email = $_POST['email'] ?? '';
    $direccion = $_POST['direccion'] ?? '';
    $fobias = $_POST['fobias'] ?? '';
    $intereses = $_POST['intereses'] ?? '';
    $rutinas = $_POST['rutinas'] ?? '';
    $pasatiempos = $_POST['pasatiempos'] ?? '';
    $visitas = $_POST['visitas'] ?? '';
    $detalles_visitas = $_POST['detalles_visitas'] ?? '';
    $vehiculos = $_POST['vehiculos'] ?? '';
    $mascotas = $_POST['mascotas'] ?? '';
    $aprendizaje = $_POST['aprendizaje'] ?? '';
    $negocio = $_POST['negocio'] ?? '';
    $muebles = $_POST['muebles'] ?? '';
    $detalles_casa = $_POST['detalles_casa'] ?? '';
    $turnoSeleccionado = $_POST['turno'] ?? '';
   // ... (resto del código para guardar la información en la base de datos)

   // Actualizar la disponibilidad del turno en la base de datos
   $update_query = "UPDATE horarios_disponibles SET disponible = FALSE WHERE fecha_hora = ?";
   $stmt = mysqli_prepare($conexion, $update_query);
   mysqli_stmt_bind_param($stmt, "s", $turnoSeleccionado);
   mysqli_stmt_execute($stmt);

// Consulta para insertar en la base de datos (TODOS los campos)
    $insert_query = "INSERT INTO presupuestos (
        nombre, ocupacion, habitantes, seguridad, trabajo_en_casa, salud, telefono, email, direccion, 
        fobias, intereses, rutinas, pasatiempos, visitas, detalles_visitas, vehiculos, mascotas, 
        aprendizaje, negocio, muebles, detalles_casa, turno, m2_cantidad, tipo_proyecto
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"; 
    
    $stmt = mysqli_prepare($conexion, $insert_query);

    $m2_cant = floatval($_POST['m2_cantidad'] ?? 0);
    $tipo_proj = $_POST['tipo_proyecto'] ?? 'unifamiliar';

    // Vincular parámetros (24 en total)
    mysqli_stmt_bind_param($stmt, "ssssssssssssssssssssssds", 
        $nombre, $ocupacion, $habitantes, $seguridad, $trabajo_en_casa, $salud, $telefono, $email, $direccion, 
        $fobias, $intereses, $rutinas, $pasatiempos, $visitas, $detalles_visitas, $vehiculos, $mascotas, 
        $aprendizaje, $negocio, $muebles, $detalles_casa, $turnoSeleccionado, $m2_cant, $tipo_proj
    );

    mysqli_stmt_execute($stmt);

    if (mysqli_stmt_affected_rows($stmt) > 0) {
        $config = require('./config.php');
        $presupuestoId = mysqli_insert_id($conexion);
        
        // Formatear la fecha del turno
        $fecha_turno = new DateTime($turnoSeleccionado);
        $fecha_formateada = $fecha_turno->format('d/m/Y H:i');

        // Generar PDF con datos básicos y cotización
        $pdfPath = __DIR__ . "/tmp/Presupuesto_" . $presupuestoId . ".pdf";
        require_once('./fpdf186/fpdf.php');
        if (!is_dir(__DIR__ . '/tmp')) {
            mkdir(__DIR__ . '/tmp', 0775, true);
        }
        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetMargins(10, 10, 10);
        $pdf->Image('logo.png', 10, 10, 30);
        $pdf->Ln(20);
        $pdf->SetFont('Arial', 'B', 20);
        $pdf->Cell(0, 10, utf8_decode('Presupuesto de Referencia: ' . $presupuestoId), 0, 1, 'C');
        $pdf->Ln(10);
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(0, 10, utf8_decode('Datos del Cliente'), 0, 1, 'L');
        $pdf->SetFont('Arial', '', 9);
        $fields = [
            'Nombre' => $nombre,
            'Telefono' => $telefono,
            'Email' => $email,
            'Direccion' => $direccion,
            'Metros Cuadrados' => $m2_cant,
            'Tipo de Proyecto' => $tipo_proj,
            'Fecha Turno' => $fecha_formateada
        ];
        foreach ($fields as $label => $value) {
            $pdf->Cell(50, 8, utf8_decode($label), 1, 0, 'L');
            $pdf->Cell(100, 8, utf8_decode($value), 1, 1, 'L');
        }
        $pdf->Ln(10);
        $pdf->Output('F', $pdfPath);

        // Fetch items from DB for Email
        $res_items_db = mysqli_query($conexion, "SELECT * FROM cotizacion_items ORDER BY orden ASC");
        $items_data = mysqli_fetch_all($res_items_db, MYSQLI_ASSOC);

        $grandTotal = 0;
        $html_table_rows = "";
        
        // Row for Project Type First
        $proj_base_price = $project_prices[$tipo_proj];
        $proj_mo = $global_mo_percent; // Use global for project row
        $proj_unit_final = $proj_base_price * (1 + ($proj_mo / 100));
        $proj_total = $proj_unit_final * $m2_cant;
        $grandTotal += $proj_total;
        
        $html_table_rows .= "<tr>
            <td>•</td>
            <td>PROYECTO: " . strtoupper($tipo_proj) . " <span style='font-size:10px; color:#8a2be2;'>(Total M.O.)</span></td>
            <td style='text-align:center'>m²</td>
            <td style='text-align:center'>$m2_cant</td>
            <td style='text-align:right'>$" . number_format($proj_unit_final, 0, ',', '.') . "</td>
            <td style='text-align:right'>$" . number_format($proj_total, 0, ',', '.') . "</td>
        </tr>";

        foreach($items_data as $item) {
            $desc = $item['descripcion'];
            $unid = $item['unidad'];
            $base_price = $item['precio_unitario'];
            $cant = $item['cantidad'];
            $idx = $item['item_num'];
            $mo_percent = $item['porcentaje_mo'];

            if (strtolower($unid) === 'm2' || strtolower($unid) === 'm²') {
                $cant = $m2_cant;
            }

            $mo_factor = 1 + ($mo_percent / 100);
            $unit_final = $base_price * $mo_factor;
            $total = $unit_final * $cant;
            $grandTotal += $total;

            $mo_label = ($mo_percent > 0) ? " <span style='font-size:10px; color:#8a2be2;'>(Total M.O.)</span>" : "";

            $html_table_rows .= "<tr>
                <td>$idx</td>
                <td>$desc$mo_label</td>
                <td style='text-align:center'>$unid</td>
                <td style='text-align:center'>$cant</td>
                <td style='text-align:right'>$" . number_format($unit_final, 0, ',', '.') . "</td>
                <td style='text-align:right'>$" . number_format($total, 0, ',', '.') . "</td>
            </tr>";
        }
        
        $obs1 = "Para empezar el trabajo se pide abonar el 50% del ITEM DISEÑO DE PLANOS, luego el 30% una vez presentado todos los planos para la firma del comitente, y la cancelación del mismo una vez se entrega la carpeta aprobada por la municipalidad.";
        $obs2 = "Los precios son variables, se rendirán con boleta, los precios son estimativos.";
        $obs3 = "El presupuesto no incluye la DIRECCIÓN TÉCNICA, pero sí visitas periódicas mensuales.";
        
        $mail = new PHPMailer(true);

        try {
            // Configuración del servidor SMTP (usando config.php)
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = $config['smtp_username'];
            $mail->Password = $config['smtp_password'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            $mail->CharSet = 'UTF-8';

            // Remitente y destinatario
            $mail->setFrom($config['from_email'], 'Mat Construcciones');
            $mail->addAddress($email, $nombre);
            
            if (file_exists($pdfPath)) {
                $mail->addAttachment($pdfPath, 'Presupuesto_MatConstrucciones.pdf');
            }

            $mail->isHTML(true);
            $mail->Subject = 'Tu Cotización - Mat Construcciones';
            $mail->Body = "
            <html>
            <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; }
                .container { max-width: 700px; margin: 0 auto; padding: 20px; }
                .header { background-color: #8a2be2; color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { padding: 20px; background-color: #f9f9f9; border: 1px solid #eee; border-radius: 0 0 10px 10px; }
                .footer { text-align: center; padding: 20px; color: #666; }
                table { width: 100%; border-collapse: collapse; margin: 16px 0; }
                th, td { border: 1px solid #ececec; padding: 8px; font-size: 13px; text-align: left; }
                th { background: #8a2be2; color: #fff; text-transform: uppercase; font-size: 12px; }
            </style>
            </head>
            <body>
            <div class='container'>
                <div class='header'>
                    <h2>Presupuesto de Referencia</h2>
                </div>
                <div class='content'>
                    <p>Hola <strong>$nombre</strong>,</p>
                    <p>Adjuntamos la cotización de referencia para tu proyecto <strong>" . ucfirst($tipo_proj) . "</strong> de aproximadamente <strong>$m2_cant m²</strong>.</p>
                    <p><strong>Cita Programada:</strong> $fecha_formateada</p>

                    <table>
                        <thead>
                            <tr>
                                <th>ITEM</th><th>DESCRIPCIÓN</th><th>UNID.</th><th>CANT.</th><th>UNITARIO</th><th>TOTAL M.O.</th>
                            </tr>
                        </thead>
                        <tbody>
                            $html_table_rows
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan='5' style='text-align:right'><strong>Total M.O.</strong></td>
                                <td style='text-align:right'><strong>$" . number_format($grandTotal, 0, ',', '.') . "</strong></td>
                            </tr>
                        </tfoot>
                    </table>

                    <p style='font-size: 12px; color: #555;'>$obs1</p>
                    <p style='font-size: 12px; font-weight: bold;'>$obs2</p>
                    <p style='font-size: 12px; color: #555;'>$obs3</p>
                    <p style='font-size: 12px; color: #8a2be2;'>* Los precios indicados como (Total M.O.) incluyen Mano de Obra.</p>
                </div>
                <div class='footer'>
                    <p>Atentamente,<br>Mat Construcciones</p>
                </div>
            </div>
            </body>
            </html>";

            $mail->send();
            if (file_exists($pdfPath)) {
                @unlink($pdfPath);
            }

            header('Location: enviar_presupuesto.php?success=1');
            exit();
        } catch (Exception $e) {
            $_SESSION['error_message'] = "Error al enviar el correo: " . $mail->ErrorInfo;
            header('Location: enviar_presupuesto.php?error=1');
            exit();
        }
        
        // Redirigir con mensaje de éxito
        header('Location: enviar_presupuesto.php?success=1');
        exit();
    } else {
        $_SESSION['error_message'] = "Error al guardar el presupuesto";
        header('Location: enviar_presupuesto.php?error=1');
        exit();
    }

    mysqli_stmt_close($stmt);
    mysqli_close($conexion);
}

// El porcentaje de M.O. ya fue obtenido al principio del archivo
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enviar Presupuesto - Mat Construcciones</title>
    <link rel="stylesheet" href="index.css">
    <style>
        body {
            position: relative;
            background-image: url("./imagen/nosotros/imagen3.jpg"); 
            background-size: cover; 
            background-repeat: no-repeat;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
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

        main {
            padding: 20px;
            max-width: 1000px;
            margin: 0 auto;
        }

        .info-box {
            background: rgba(255, 255, 255, 0.95);
            color: #222;
            border-radius: 12px;
            padding: 25px;
            margin: 20px auto;
            max-width: 800px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            text-align: center;
            border: 1px solid rgba(0,0,0,0.05);
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
        }

        .tabla-referencia td {
            padding: 15px;
            border: none;
            border-bottom: 1px solid rgba(138, 43, 226, 0.1);
        }

        .tabla-referencia tbody tr:nth-child(even) {
            background-color: rgba(147, 112, 219, 0.05);
        }

        .tabla-referencia tfoot td {
            border-top: 2px solid rgba(138, 43, 226, 0.2);
            color: #8a2be2;
            font-size: 1.1em;
            font-weight: bold;
            padding: 20px;
        }

        .btn-comenzar {
            background: linear-gradient(135deg, #8a2be2, #9370db);
            border: none;
            border-radius: 12px;
            padding: 25px 30px;
            color: white;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 20px rgba(138, 43, 226, 0.4);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            min-height: 100px;
        }

        .btn-comenzar:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 25px rgba(138, 43, 226, 0.5);
        }

        input, select {
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 1em;
            box-sizing: border-box;
        }

        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #444;
        }

        .mo-badge {
            background: rgba(138, 43, 226, 0.1);
            color: #8a2be2;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 0.8em;
            margin-left: 10px;
            font-weight: bold;
        }

        #formulario-container {
            opacity: 0;
            max-height: 0;
            overflow: hidden;
            transition: opacity 0.5s ease, max-height 0.5s ease;
        }

        #formulario-container.visible {
            opacity: 1;
            max-height: 1000px;
        }

        .observaciones {
            background: #fdfbff;
            border-left: 4px solid #8a2be2;
            padding: 20px;
            margin-top: 30px;
            border-radius: 4px;
            text-align: left;
        }

        .oculto { display: none !important; }
    </style>
</head>
<body>
    <main>
        <!-- SECCIÓN 1: CALCULADORA INICIAL -->
        <section id="intro-step" class="info-box" style="margin-top: 80px;">
            <div class="titulo-carpeta" style="margin-bottom: 30px;">
                <h2>COTIZADOR ONLINE</h2>
                <p>Calculá tu presupuesto estimado ingresando los metros cuadrados</p>
            </div>
            
            <div style="display: flex; gap: 20px; max-width: 600px; margin: 0 auto 30px auto; flex-wrap: wrap;">
                <div class="form-group" style="flex: 1; min-width: 250px;">
                    <label for="intro_m2">Metros Cuadrados (m²):</label>
                    <input type="number" id="intro_m2" placeholder="Ej: 110" min="1" value="<?php echo isset($_REQUEST['m2']) ? $_REQUEST['m2'] : ''; ?>" style="width: 100%;">
                </div>
                <div class="form-group" style="flex: 1; min-width: 250px;">
                    <label for="intro_tipo">Tipo de Proyecto:</label>
                    <select id="intro_tipo" style="width: 100%;">
                        <option value="unifamiliar">Vivienda Unifamiliar</option>
                        <option value="colectiva">Vivienda Colectiva</option>
                        <option value="quincho">Quincho</option>
                    </select>
                </div>
            </div>

            <button id="btn-calcular" class="btn-comenzar" style="width: 100%; max-width: 400px; margin: 0 auto; display: flex;">
                <span style="font-size: 1.8em; font-weight: 800; text-transform: uppercase; letter-spacing: 2px; line-height: 1.1;">
                    CALCULAR<br>PRESUPUESTO
                </span>
            </button>
        </section>

        <!-- SECCIÓN 2: TABLA DE REFERENCIA -->
        <section id="presupuesto-referencia" class="tabla-referencia" style="display: none;">
            <div class="titulo-carpeta">
                <h2>PRESUPUESTO ESTIMADO</h2>
                <p>Estructura, Eléctrico, Sanitario y Gas</p>
            </div>

            <?php
            $res_items_view = mysqli_query($conexion, "SELECT * FROM cotizacion_items ORDER BY orden ASC");
            $items_view = mysqli_fetch_all($res_items_view, MYSQLI_ASSOC);
            ?>

            <table id="tabla_cotizacion">
                <thead>
                    <tr>
                        <th>ITEM</th>
                        <th>DESCRIPCIÓN</th>
                        <th>UNID.</th>
                        <th style="text-align:center">CANT.</th>
                        <th>UNITARIO</th>
                        <th>SUBTOTAL</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Row for Dynamic Project Calculation -->
                    <tr id="row-proyecto" data-is-project="1" data-mo-percent="<?php echo $global_mo_percent; ?>">
                        <td>•</td>
                        <td id="proyecto-desc">PROYECTO: VIVIENDA UNIFAMILIAR <span class="mo-badge">Total M.O.</span></td>
                        <td>m²</td>
                        <td class="cant-cell" style="text-align:center">0</td>
                        <td class="unit-cell">$0</td>
                        <td class="total-cell">$0</td>
                    </tr>

                    <?php foreach ($items_view as $item): ?>
                    <tr data-base-price="<?php echo $item['precio_unitario']; ?>" 
                        data-base-cant="<?php echo $item['cantidad']; ?>"
                        data-is-m2="<?php echo (strtolower($item['unidad']) === 'm2' || strtolower($item['unidad']) === 'm²') ? '1' : '0'; ?>"
                        data-mo-percent="<?php echo $item['porcentaje_mo']; ?>">
                        <td><?php echo $item['item_num']; ?></td>
                        <td>
                            <?php echo $item['descripcion']; ?>
                            <?php if($item['porcentaje_mo'] > 0): ?>
                                <span class="mo-badge">Total M.O.</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $item['unidad']; ?></td>
                        <td class="cant-cell" style="text-align:center"><?php echo $item['cantidad']; ?></td>
                        <td class="unit-cell">$<?php echo number_format($item['precio'], 0, ',', '.'); ?></td>
                        <td class="total-cell">$0</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="5" style="text-align: right;"><strong>TOTAL ESTIMADO M.O.</strong>:</td>
                        <td id="total_mo_display" style="text-align: left; color: #8a2be2; font-size: 1.25em;">$0</td>
                    </tr>
                </tfoot>
            </table>

            <div class="observaciones">
                <h3 style="margin-top: 0; color: #8a2be2;">OBSERVACIONES</h3>
                <p>• Los precios indicados como "Total M.O." incluyen el concepto de Mano de Obra.</p>
                <p>• Precios estimativos sujetos a cambios. Ítems del 2 al 10 se rinden con boleta.</p>
                <p>• El presupuesto NO incluye Dirección Técnica.</p>
            </div>

            <div style="text-align: center; margin-top: 30px;">
                <button id="btnVolver" style="padding: 12px 25px; cursor: pointer; border: 2px solid #8a2be2; color: #8a2be2; background: white; border-radius: 30px; font-weight: bold;">
                    ← VOLVER A EDITAR METROS
                </button>
            </div>
        </section>

        <!-- SECCIÓN 3: FORMULARIO DE CITA -->
        <section id="presupuesto-form">
            <div id="formulario-container" class="tabla-referencia" style="display: none; margin-top: 30px;">
                <h2 style="text-align: center; color: #8a2be2; margin-bottom: 30px;">AGENDAR CITA Y ENVIAR</h2>
                <form action="enviar_presupuesto.php" method="post">
                    <input type="hidden" name="m2_cantidad" id="hidden_m2">
                    <input type="hidden" name="tipo_proyecto" id="hidden_tipo">
                    
                    <div class="form-group">
                        <label for="nombre">Apellido y Nombre:</label>
                        <input type="text" id="nombre" name="nombre" required style="width: 100%;">
                    </div>

                    <div class="form-group">
                        <label for="telefono">Teléfono:</label>
                        <input type="text" id="telefono" name="telefono" required style="width: 100%;">
                    </div>

                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" required style="width: 100%;">
                    </div>

                    <div class="form-group">
                        <label for="direccion">Dirección:</label>
                        <input type="text" id="direccion" name="direccion" required style="width: 100%;">
                    </div>

                    <div class="form-group">
                        <label for="turno">Seleccione un turno para la cita:</label>
                        <select id="turno" name="turno" required style="width: 100%;">
                            <?php if (!empty($turnosDisponibles)) : ?>
                                <?php foreach ($turnosDisponibles as $turno) : ?>
                                    <option value="<?php echo $turno; ?>"><?php echo date('d/m/Y H:i', strtotime($turno)); ?> hs</option>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <option value="">No hay turnos disponibles</option>
                            <?php endif; ?>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn-comenzar" style="width: 100%; margin-top: 10px;">
                        <h3 style="margin:0; text-transform: uppercase;">Enviar</h3>
                    </button>
                </form>
            </div>
        </section>
    </main>

    <link rel="stylesheet" href="modal-q.css">
    <script src="modal-q.js"></script>
    <script>
    const projectPrices = <?php echo json_encode($project_prices); ?>;

    function updateTable() {
        const m2 = parseFloat(document.getElementById('intro_m2').value) || 0;
        const type = document.getElementById('intro_tipo').value;
        let sumTotal = 0;

        // Update Project Row
        const rowProj = document.getElementById('row-proyecto');
        const projBasePrice = projectPrices[type] || 0;
        const projMOPercent = parseFloat(rowProj.getAttribute('data-mo-percent')) || 0;
        const projUnitFinal = projBasePrice * (1 + (projMOPercent / 100));
        const projSubtotal = projUnitFinal * m2;
        
        document.getElementById('proyecto-desc').innerHTML = 'PROYECTO: ' + type.toUpperCase() + ' <span class="mo-badge">Total M.O.</span>';
        rowProj.querySelector('.cant-cell').innerText = m2;
        rowProj.querySelector('.unit-cell').innerText = '$' + Math.round(projUnitFinal).toLocaleString('es-AR');
        rowProj.querySelector('.total-cell').innerText = '$' + Math.round(projSubtotal).toLocaleString('es-AR');
        sumTotal += projSubtotal;

        document.querySelectorAll('#tabla_cotizacion tbody tr:not(#row-proyecto)').forEach(row => {
            const isM2 = row.getAttribute('data-is-m2') === '1';
            const moPercent = parseFloat(row.getAttribute('data-mo-percent')) || 0;
            let price = parseFloat(row.getAttribute('data-base-price')) || 0;
            let baseCant = parseFloat(row.getAttribute('data-base-cant')) || 1;
            let cant = isM2 ? m2 : baseCant;

            let unitFinal = price * (1 + (moPercent / 100));
            let subtotal = unitFinal * cant;
            
            sumTotal += subtotal;

            row.querySelector('.cant-cell').innerText = cant;
            row.querySelector('.unit-cell').innerText = '$' + Math.round(unitFinal).toLocaleString('es-AR');
            row.querySelector('.total-cell').innerText = '$' + Math.round(subtotal).toLocaleString('es-AR');
        });

        document.getElementById('total_mo_display').innerHTML = '<strong>$' + Math.round(sumTotal).toLocaleString('es-AR') + '</strong>';
        
        if(document.getElementById('hidden_m2')) document.getElementById('hidden_m2').value = m2;
        if(document.getElementById('hidden_tipo')) document.getElementById('hidden_tipo').value = document.getElementById('intro_tipo').value;
    }

    document.getElementById('intro_m2').addEventListener('input', updateTable);
    document.getElementById('intro_tipo').addEventListener('change', updateTable);
    
    document.getElementById('btn-calcular').addEventListener('click', function() {
        const m2 = parseFloat(document.getElementById('intro_m2').value) || 0;
        if(m2 <= 0) {
            alert("Por favor ingrese una cantidad válida de metros cuadrados.");
            return;
        }
        
        updateTable();
        
        document.getElementById('intro-step').style.display = 'none';
        document.getElementById('presupuesto-referencia').style.display = 'block';
        
        const formContainer = document.getElementById('formulario-container');
        formContainer.style.display = 'block';
        setTimeout(() => formContainer.classList.add('visible'), 100);

        window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    document.getElementById('btnVolver').addEventListener('click', function() {
        document.getElementById('intro-step').style.display = 'block';
        document.getElementById('presupuesto-referencia').style.display = 'none';
        document.getElementById('formulario-container').classList.remove('visible');
        document.getElementById('formulario-container').style.display = 'none';
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    <?php if (isset($_GET['success']) && $_GET['success'] == '1') { ?>
        showModalQ('¡El presupuesto ha sido enviado exitosamente!', false, function() {
            setTimeout(function() {
                window.location.href = 'index.php';
            }, 2000);
        }, '¡Éxito!');
    <?php } elseif (isset($_GET['error']) && $_GET['error'] == '1') { ?>
        showModalQ('<?php echo isset($_SESSION['error_message']) ? $_SESSION['error_message'] : "Ha ocurrido un error al procesar su solicitud"; ?>', true, null, 'Error');
        <?php unset($_SESSION['error_message']); ?>
    <?php } elseif (isset($message)) { ?>
      showModalQ('<?php echo htmlspecialchars($message); ?>', <?php echo (strpos($message, 'Error') !== false ? 'true' : 'false'); ?>, null, <?php echo (strpos($message, 'Error') !== false ? "'Error al Enviar Presupuesto'" : "'¡Presupuesto Enviado!'"); ?>);
    <?php } ?>
    </script>

    <footer style="text-align: center; padding: 50px 0; color: #777;">
        <p>&copy; Mat Construcciones</p>
    </footer>
</body>
</html>

<?php
session_start();
require 'ingreso/usuario/gestion_cliente/PHPMailer/Exception.php';
require 'ingreso/usuario/gestion_cliente/PHPMailer/PHPMailer.php';
require 'ingreso/usuario/gestion_cliente/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

include('db.php');
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
    // Tipos: s (string), i (int), d (double), b (blob)
    // m2_cantidad es double 'd', habitantes puede ser int pero 's' es seguro.
    // Asumiremos 's' para todo salvo m2.
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

        // Fetch dynamic items for email (as calculated in the form)
        $sql_items_db = "SELECT * FROM cotizacion_items ORDER BY orden ASC";
        $res_items_db = mysqli_query($conexion, $sql_items_db);
        
        $sql_conf_db = "SELECT * FROM cotizacion_config WHERE clave = 'm2_base'";
        $res_conf_db = mysqli_query($conexion, $sql_conf_db);
        $conf_db = mysqli_fetch_assoc($res_conf_db);
        
        // Calcular precio base del m2 inflado con MO base
        $mo_base_percent = $conf_db['porcentaje_mo'] ?? 0;
        $raw_price = $conf_db['valor_' . $tipo_proj] ?? $conf_db['valor_unifamiliar'];
        $price_m2_type = $raw_price * (1 + $mo_base_percent/100);

        $grandTotal = 0;
        $html_table_rows = "";
        
        // Agregar filas informativas de precios base (Items 1-3)
        // No suman al total, solo informativo
        $prices_base = [
            '1' => ['desc' => 'Precio Base: Vivienda Unifamiliar', 'price' => $conf_db['valor_unifamiliar'] * (1 + $mo_base_percent/100)],
            '2' => ['desc' => 'Precio Base: Vivienda Colectiva', 'price' => $conf_db['valor_colectiva'] * (1 + $mo_base_percent/100)],
            '3' => ['desc' => 'Precio Base: Quincho', 'price' => $conf_db['valor_quincho'] * (1 + $mo_base_percent/100)]
        ];
        
        foreach($prices_base as $idx => $pb) {
             $html_table_rows .= "<tr>
                <td>$idx</td>
                <td>{$pb['desc']}</td>
                <td style='text-align:center'>m²</td>
                <td style='text-align:center'>1</td>
                <td style='text-align:right'>$" . number_format($pb['price'], 0, ',', '.') . "</td>
                <td style='text-align:right'>$" . number_format($pb['price'], 0, ',', '.') . "</td>
            </tr>";
        }
        
        // Items dinámicos (4 en adelante)
        $i = 4;
        while($item = mysqli_fetch_assoc($res_items_db)) {
            $desc = $item['descripcion'];
            $unid = $item['unidad'];
            $price = floatval($item['precio_unitario']);
            $cant = floatval($item['cantidad']);
            $mo_item_percent = floatval($item['porcentaje_mo'] ?? 0);

            if (strtolower($unid) === 'm2' || strtolower($unid) === 'm²') {
                $cant = $m2_cant;
                $price = $price_m2_type;
            }

            // Calculo: (Precio * Cantidad) * (1 + %Item)
            $subtotal = $price * $cant;
            $total = $subtotal * (1 + $mo_item_percent/100);
            
            $grandTotal += $total;

            $html_table_rows .= "<tr>
                <td>$i</td>
                <td>$desc</td>
                <td style='text-align:center'>$unid</td>
                <td style='text-align:center'>$cant</td>
                <td style='text-align:right'>$" . number_format($price, 0, ',', '.') . "</td>
                <td style='text-align:right'>$" . number_format($total, 0, ',', '.') . "</td>
            </tr>";
            $i++;
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
            border: 1px solid #e6e6e6;
        }

        .observaciones h3 {
            color: #333;
            margin-bottom: 12px;
            letter-spacing: 0.3px;
        }

        .observaciones p {
            margin-bottom: 8px;
            line-height: 1.45;
            color: #555;
            font-size: 0.95em;
        }

        .observaciones .nota-variable {
            color: #333;
            font-weight: 700;
            font-size: 0.97em;
        }

        .observaciones .nota-legal {
            color: #666;
            font-size: 0.9em;
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

            <?php
            // Obtener configuración de m2
            $sql_conf = "SELECT * FROM cotizacion_config WHERE clave = 'm2_base'";
            $res_conf = mysqli_query($conexion, $sql_conf);
            $conf_m2 = mysqli_fetch_assoc($res_conf);

            // Obtener ítems
            $sql_items = "SELECT * FROM cotizacion_items ORDER BY orden ASC";
            $res_items = mysqli_query($conexion, $sql_items);
            $items = [];
            $total_mo = 0;
            while($row = mysqli_fetch_assoc($res_items)) {
                $items[] = $row;
            }
            ?>

            <?php
            // Capturar valores iniciales si vienen de una pantalla anterior
            $m2_inicial = isset($_REQUEST['m2']) ? floatval($_REQUEST['m2']) : 1;
            // El tipo de vivienda puede venir como texto o value
            $tipo_inicial = isset($_REQUEST['tipo']) ? $_REQUEST['tipo'] : 'unifamiliar'; 
            ?>

            <!-- SECCIÓN OCULTA ELIMINADA: Los inputs ahora están en el formulario de abajo y son visibles/editables -->

            <table border="1" cellpadding="5" cellspacing="0" id="tabla_cotizacion">
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
                    <?php
                    // Recalcular precios con MO base para mostrar en tabla
                    $mo_base = $conf_m2['porcentaje_mo'] ?? 0;
                    $precio_unif_show = $conf_m2['valor_unifamiliar'] * (1 + $mo_base/100);
                    $precio_cole_show = $conf_m2['valor_colectiva'] * (1 + $mo_base/100);
                    $precio_quin_show = $conf_m2['valor_quincho'] * (1 + $mo_base/100);
                    ?>
                    <!-- Filas informativas de precios base por m² (Items 1-3) -->
                    <tr style="background-color: #f9f9f9;">
                        <td>1</td>
                        <td>Precio Base: Vivienda Unifamiliar</td>
                        <td>m²</td>
                        <td>1</td>
                        <td>$<?php echo number_format($precio_unif_show, 0, ',', '.'); ?></td>
                        <td>$<?php echo number_format($precio_unif_show, 0, ',', '.'); ?></td>
                    </tr>
                    <tr style="background-color: #f9f9f9;">
                        <td>2</td>
                        <td>Precio Base: Vivienda Colectiva</td>
                        <td>m²</td>
                        <td>1</td>
                        <td>$<?php echo number_format($precio_cole_show, 0, ',', '.'); ?></td>
                        <td>$<?php echo number_format($precio_cole_show, 0, ',', '.'); ?></td>
                    </tr>
                    <tr style="background-color: #f9f9f9;">
                        <td>3</td>
                        <td>Precio Base: Quincho</td>
                        <td>m²</td>
                        <td>1</td>
                        <td>$<?php echo number_format($precio_quin_show, 0, ',', '.'); ?></td>
                        <td>$<?php echo number_format($precio_quin_show, 0, ',', '.'); ?></td>
                    </tr>

                    <?php 
                    $i = 4; // Comenzamos enumeración desde 4
                    foreach ($items as $item): 
                    ?>
                    <tr data-item-num="<?php echo $i; ?>" 
                        data-base-price="<?php echo $item['precio_unitario']; ?>" 
                        data-unit="<?php echo $item['unidad']; ?>"
                        data-base-cant="<?php echo $item['cantidad']; ?>"
                        data-mo-percent="<?php echo $item['porcentaje_mo'] ?? 0; ?>">
                        <td><?php echo $i; ?></td>
                        <td><?php echo $item['descripcion']; ?></td>
                        <td><?php echo $item['unidad']; ?></td>
                        <td class="cant-cell"><?php echo $item['cantidad']; ?></td>
                        <td class="unit-cell">$<?php echo number_format($item['precio_unitario'], 0, ',', '.'); ?></td>
                        <td class="total-cell">$<?php echo number_format(($item['precio_unitario'] * $item['cantidad']) * (1 + ($item['porcentaje_mo'] ?? 0)/100), 0, ',', '.'); ?></td>
                    </tr>
                    <?php 
                    $i++;
                    endforeach; 
                    ?>
                </tbody>
                <tfoot>
                    <tr style="background-color: #f3e5f5;">
                        <td colspan="5" style="text-align: right; font-size: 1.1em;"><strong>TOTAL M.O.</strong>:</td>
                        <td id="total_mo_display" style="font-size: 1.2em; color: #8a2be2;"><strong>$0</strong></td>
                    </tr>
                </tfoot>
            </table>

            <div class="observaciones">
                <h3>OBSERVACIONES</h3>
                <p class="nota-legal">Para empezar el trabajo se pide abonar el 50% del ITEM DISEÑO DE PLANOS, luego el 30% una vez presentado todos los planos para la firma del comitente, y la cancelación del mismo una vez se entrega la carpeta aprobada por la municipalidad.</p>
                <p class="nota-variable">Los precios de item 2 a 10 son variables, se rendirán con boleta, los precios son estimativos.</p>
                <p class="nota-legal">El presupuesto no incluye la <strong>DIRECCIÓN TÉCNICA</strong>, pero sí visitas periódicas mensuales.</p>
            </div>

            <script>
            function updateTable() {
                const m2Input = document.getElementById('m2_input');
                const typeSelect = document.getElementById('tipo_vivienda');
                
                const m2 = parseFloat(m2Input.value) || 0;
                const typeText = typeSelect.options[typeSelect.selectedIndex].text;
                const typeValue = typeSelect.value;
                const m2Price = parseFloat(typeSelect.options[typeSelect.selectedIndex].getAttribute('data-price'));
                
                let sumTotal = 0;

                // Solo sumamos las filas dinámicas (Items 4 en adelante)
                document.querySelectorAll('#tabla_cotizacion tbody tr[data-base-price]').forEach(row => {
                    const unit = row.getAttribute('data-unit').toLowerCase();
                    let price = parseFloat(row.getAttribute('data-base-price'));
                    let cant = parseFloat(row.getAttribute('data-base-cant'));
                    let moPercent = parseFloat(row.getAttribute('data-mo-percent')) || 0;

                    // Si la unidad es m2 o el item depende de m2
                    if (unit === 'm2' || unit === 'm²') {
                        cant = m2;
                        price = m2Price; 
                    }

                    // Calculo: (Precio * Cantidad) + Porcentaje MO
                    // Interpretación: El porcentaje es sobre el subtotal del ítem
                    let subtotal = price * cant;
                    let total = subtotal * (1 + (moPercent / 100));
                    
                    sumTotal += total;

                    row.querySelector('.cant-cell').innerText = cant;
                    row.querySelector('.unit-cell').innerText = '$' + price.toLocaleString('es-AR');
                    row.querySelector('.total-cell').innerText = '$' + total.toLocaleString('es-AR', {minimumFractionDigits: 0, maximumFractionDigits: 0});
                });

                document.getElementById('total_mo_display').innerHTML = '<strong>$' + sumTotal.toLocaleString('es-AR', {minimumFractionDigits: 0, maximumFractionDigits: 0}) + '</strong>';
                
                // --- SINCRONIZACIÓN CON EL FORMULARIO ---
                // Campos ocultos para el POST
                if(document.getElementById('hidden_m2')) document.getElementById('hidden_m2').value = m2;
                if(document.getElementById('hidden_tipo')) document.getElementById('hidden_tipo').value = typeValue;
                
                // Campos visibles de solo lectura para confirmación del usuario
                /* if(document.getElementById('display_m2')) document.getElementById('display_m2').value = m2;
                if(document.getElementById('display_tipo')) document.getElementById('display_tipo').value = typeText; */
            }

            document.getElementById('m2_input').addEventListener('input', updateTable);
            document.getElementById('tipo_vivienda').addEventListener('change', updateTable);
            window.addEventListener('load', updateTable);
            </script>
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
                <input type="hidden" name="m2_cantidad" id="hidden_m2">
                <input type="hidden" name="tipo_proyecto" id="hidden_tipo">
                <div class="form-row-summary" style="display: flex; gap: 20px; margin-bottom: 20px; background: #f0f0f0; padding: 15px; border-radius: 8px;">
                    <div style="flex: 1;">
                        <label for="m2_input" style="font-weight: bold; color: #555;">Metros Cuadrados (m²):</label>
                        <input type="number" id="m2_input" name="m2_cantidad" value="<?php echo $m2_inicial; ?>" min="1" style="border: 1px solid #ced4da; font-weight: bold; width: 100%; padding: 8px; border-radius: 4px;">
                    </div>
                    <div style="flex: 1;">
                        <label for="tipo_vivienda" style="font-weight: bold; color: #555;">Tipo de Vivienda Cotizada:</label>
                        <select id="tipo_vivienda" name="tipo_proyecto" style="border: 1px solid #ced4da; font-weight: bold; width: 100%; padding: 8px; border-radius: 4px;">
                            <?php
                            $mo_base = $conf_m2['porcentaje_mo'] ?? 0;
                            $precio_unif = $conf_m2['valor_unifamiliar'] * (1 + $mo_base/100);
                            $precio_cole = $conf_m2['valor_colectiva'] * (1 + $mo_base/100);
                            $precio_quin = $conf_m2['valor_quincho'] * (1 + $mo_base/100);

                            $tipos = [
                                'unifamiliar' => ['label' => 'Unifamiliar', 'price' => $precio_unif],
                                'colectiva' => ['label' => 'Vivienda Colectiva', 'price' => $precio_cole],
                                'quincho' => ['label' => 'Quincho', 'price' => $precio_quin]
                            ];
                            foreach ($tipos as $val => $data) {
                                $selected = ($val == $tipo_inicial) ? 'selected' : '';
                                echo '<option value="' . $val . '" data-price="' . $data['price'] . '" ' . $selected . '>' . $data['label'] . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <label for="nombre">Apellido y Nombre:</label>
                <input type="text" id="nombre" name="nombre" required>

                <label for="telefono">Teléfono:</label>
                <input type="text" id="telefono" name="telefono" required>

                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>

                <label for="direccion">Dirección:</label>
                <input type="text" id="direccion" name="direccion" required>

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
        showModalQ('¡El presupuesto ha sido enviado exitosamente!', false, function() {
            // Después de mostrar el modal, esperar 2 segundos y redirigir
            setTimeout(function() {
                window.location.href = 'index.php';
            }, 2000);
        }, '¡Éxito!');
    <?php } elseif (isset($_GET['error']) && $_GET['error'] == '1') { ?>
        showModalQ('<?php echo isset($_SESSION['error_message']) ? $_SESSION['error_message'] : "Ha ocurrido un error al procesar su solicitud"; ?>', true, null, 'Error');
        <?php unset($_SESSION['error_message']); // Limpiar el mensaje de error ?>
        // Después de cerrar el modal, volver a mostrar la tabla
        const formulario = document.getElementById('formulario-container');
        const btnVolverContainer = document.querySelector('.btn-volver-container');
        const tablaReferencia = document.querySelector('.tabla-referencia');
        const boton = document.getElementById('mostrarFormulario');
        
        // Mostrar tabla nuevamente
        tablaReferencia.classList.remove('oculto');
        
        // Ocultar formulario y mostrar botón de comenzar
        formulario.classList.remove('visible');
        btnVolverContainer.style.display = 'none';
        
        setTimeout(() => {
            formulario.style.display = 'none';
            boton.parentElement.style.display = 'flex';
            // Scroll al inicio de la página
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }, 500);
      }, '¡Presupuesto Enviado!');
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

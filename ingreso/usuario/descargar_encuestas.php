<?php
require('../../fpdf186/fpdf.php'); 
include('../../db.php');

// Obtener el ID del presupuesto (asumiendo que lo pasas por GET)
$id_presupuesto = $_GET['id'];

// Consultas a la base de datos
$query_presupuesto = "SELECT nombre, direccion FROM presupuestos WHERE id = $id_presupuesto";
$query_primera = "SELECT * FROM primera_encuesta_new WHERE id_presupuesto = $id_presupuesto"; // Datos personales
$query_segunda = "SELECT * FROM segunda_encuesta_new WHERE id_presupuesto = $id_presupuesto"; // Ambientes
$query_tercera = "SELECT * FROM tercera_encuesta WHERE id_presupuesto = $id_presupuesto"; // Técnicos

$result_presupuesto = mysqli_query($conexion, $query_presupuesto);
$result_primera = mysqli_query($conexion, $query_primera);
$result_segunda = mysqli_query($conexion, $query_segunda);
$result_tercera = mysqli_query($conexion, $query_tercera);

$data_presupuesto = mysqli_fetch_assoc($result_presupuesto);
$data_primera = mysqli_fetch_assoc($result_primera);
$data_segunda = mysqli_fetch_assoc($result_segunda);
$data_tercera = mysqli_fetch_assoc($result_tercera);

// Crear un nuevo documento PDF
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetAutoPageBreak(true, 10); // Habilitar salto de página automático
header('Content-Type: text/html; charset=utf8'); // Asegurar la codificación UTF-8

// Encabezado
$pdf->Image('../../logo.png', 10, 10, 30); 
$pdf->SetFont('Arial', 'B', 18);
$pdf->Cell(0, 15, '', 0, 1, 'C');
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, utf8_decode('Teléfono: +543884800555'), 0, 1, 'C');
$pdf->Ln(10);

// Datos del presupuesto (con utf8_decode)
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, utf8_decode('Encuestas de Necesidades y Construcción'), 0, 1, 'C');
$pdf->Ln(5);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, utf8_decode('Nombre: ') . utf8_decode($data_presupuesto['nombre']), 0, 1);
$pdf->Cell(0, 10, utf8_decode('Dirección: ') . utf8_decode($data_presupuesto['direccion']), 0, 1);
$pdf->Ln(10);

// ========== PRIMERA ENCUESTA - DATOS PERSONALES ==========
if ($data_primera) {
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 10, utf8_decode('Primera Encuesta - Entrevista Personalizada'), 0, 1, 'L');
    $pdf->Ln(5);
    
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(95, 10, utf8_decode('Característica'), 1, 0, 'C'); 
    $pdf->Cell(95, 10, utf8_decode('Detalle'), 1, 1, 'C');
    $pdf->SetFont('Arial', '', 10);
    
    $campos_primera = [
        'ocupacion' => 'Ocupación',
        'habitantes' => 'Habitantes',
        'seguridad' => 'Permanencia en casa (Seguridad)',
        'trabajo_en_casa' => 'Espacio de trabajo ideal',
        'salud' => 'Discapacidades en el grupo familiar',
        'fobias' => 'Fobias',
        'intereses' => 'Intereses y molestias',
        'rutinas' => 'Rutinas diarias',
        'pasatiempos' => 'Pasatiempos',
        'visitas' => 'Recibe visitas',
        'detalles_visitas' => 'Detalles especiales',
        'vehiculos' => 'Vehículos',
        'mascotas' => 'Mascotas',
        'aprendizaje' => '¿Qué le gustaría aprender?',
        'negocio' => 'Negocio anexo a vivienda',
        'muebles' => 'Muebles especiales',
        'detalles_casa' => 'Detalles de gusto personal'
    ];
    
    foreach ($campos_primera as $campo => $label) {
        $valor = $data_primera[$campo] ?? 'No especificado';
        $pdf->Cell(95, 8, utf8_decode($label), 1, 0, 'L');
        $pdf->Cell(95, 8, utf8_decode($valor), 1, 1, 'L');
    }
    
    $pdf->AddPage();
}

// ========== SEGUNDA ENCUESTA - AMBIENTES ==========
if ($data_segunda) {
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 10, utf8_decode('Segunda Encuesta - Ambientes de la Vivienda'), 0, 1, 'L');
    $pdf->Ln(5);

    // Tabla de la segunda encuesta
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(10, 10, 'Item', 1, 0, 'C'); 
    $pdf->Cell(80, 10, utf8_decode('Pregunta'), 1, 0, 'C'); 
    $pdf->Cell(40, 10, 'Resp.', 1, 0, 'C'); 
    $pdf->Cell(60, 10, utf8_decode('Observaciones'), 1, 1, 'C');
    $pdf->SetFont('Arial', '', 12);

    $item = 1;
    foreach ($data_segunda as $key => $value) {
        if ($key != 'id' && $key != 'id_presupuesto') {
            $pregunta = utf8_decode(ucwords(str_replace('_', ' ', $key)));
            $resp = utf8_decode($value);
            $obs = '';
            // Medir el ancho del texto de la respuesta
            $minResp = 20; // ancho mínimo
            $maxResp = 80; // ancho máximo
            $anchoResp = $pdf->GetStringWidth($resp) + 8; // margen extra
            if ($anchoResp < $minResp) $anchoResp = $minResp;
            if ($anchoResp > $maxResp) $anchoResp = $maxResp;
            $anchoObs = 190 - 10 - 80 - $anchoResp; // total tabla 190mm
            if ($anchoObs < 20) $anchoObs = 20; // mínimo para observaciones

            $pdf->Cell(10, 10, $item++, 1, 0, 'C');
            $pdf->Cell(80, 10, $pregunta, 1, 0, 'L');
            $pdf->Cell($anchoResp, 10, $resp, 1, 0, 'L');
            $pdf->Cell($anchoObs, 10, $obs, 1, 1, 'L');
        }
    }
    
    $pdf->AddPage();
}

// ========== TERCERA ENCUESTA - DETALLES TÉCNICOS ==========
if ($data_tercera) {
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 10, utf8_decode('Tercera Encuesta - Detalles de Construcción'), 0, 1, 'L');
    $pdf->Ln(5);

    // Tabla de la tercera encuesta
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(80, 10, utf8_decode('Característica'), 1, 0, 'C'); 
    $pdf->Cell(110, 10, utf8_decode('Detalle'), 1, 1, 'C');
    $pdf->SetFont('Arial', '', 12);

    $pdf->Cell(80, 10, utf8_decode('Tipo de Cimiento'), 1, 0, 'L');
    $pdf->Cell(110, 10, utf8_decode($data_tercera['tipo_cimiento']), 1, 1, 'C');

    $pdf->Cell(80, 10, utf8_decode('Tipo de Mampostería'), 1, 0, 'L');
    $pdf->Cell(110, 10, utf8_decode($data_tercera['tipo_mamposteria']), 1, 1, 'C');

    $pdf->Cell(80, 10, utf8_decode('Espesor de Mampostería'), 1, 0, 'L');
    $pdf->Cell(110, 10, $data_tercera['espesor_mamposteria'] . ' cm', 1, 1, 'C');

    $pdf->Cell(80, 10, utf8_decode('Tipo de Estructura'), 1, 0, 'L');
    $pdf->Cell(110, 10, utf8_decode($data_tercera['tipo_estructura']), 1, 1, 'C');

    $pdf->Cell(80, 10, utf8_decode('Tipo de Techo'), 1, 0, 'L');
    $pdf->Cell(110, 10, utf8_decode($data_tercera['tipo_techo']), 1, 1, 'C');

    $pdf->Cell(80, 10, utf8_decode('Tipo de Contrapiso'), 1, 0, 'L');
    $pdf->Cell(110, 10, utf8_decode($data_tercera['tipo_contrapiso']), 1, 1, 'C');

    $pdf->Cell(80, 10, utf8_decode('Espesor de Contrapiso'), 1, 0, 'L');
    $pdf->Cell(110, 10, $data_tercera['espesor_contrapiso'] . ' cm', 1, 1, 'C');

    $pdf->Cell(80, 10, utf8_decode('Observaciones del Contrapiso'), 1, 0, 'L');
    // Centrar las observaciones del contrapiso
    $pdf->MultiCell(110, 10, utf8_decode($data_tercera['observaciones_contrapiso']), 1, 'C');
}

// Salida del PDF
$pdf->Output('encuestas_completas.pdf', 'D'); 

?>

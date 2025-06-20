<?php
require('../../fpdf186/fpdf.php'); 
include('../../db.php');

// Obtener el ID del presupuesto (asumiendo que lo pasas por GET)
$id_presupuesto = $_GET['id'];

// Consultas a la base de datos
$query_presupuesto = "SELECT nombre, direccion FROM presupuestos WHERE id = $id_presupuesto";
$query_primera_encuesta = "SELECT * FROM primera_encuesta WHERE id_presupuesto = $id_presupuesto";
$query_segunda_encuesta = "SELECT * FROM segunda_encuesta WHERE id_presupuesto = $id_presupuesto";

$result_presupuesto = mysqli_query($conexion, $query_presupuesto);
$result_primera_encuesta = mysqli_query($conexion, $query_primera_encuesta);
$result_segunda_encuesta = mysqli_query($conexion, $query_segunda_encuesta);

$data_presupuesto = mysqli_fetch_assoc($result_presupuesto);
$data_primera_encuesta = mysqli_fetch_assoc($result_primera_encuesta);
$data_segunda_encuesta = mysqli_fetch_assoc($result_segunda_encuesta);

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

// Primera Encuesta - Necesidades
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, utf8_decode('Primera Encuesta - Necesidades'), 0, 1, 'L');
$pdf->Ln(5);

// Tabla de la primera encuesta
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(10, 10, 'Item', 1, 0, 'C'); 
$pdf->Cell(80, 10, utf8_decode('Pregunta'), 1, 0, 'C'); 
$pdf->Cell(40, 10, 'Resp.', 1, 0, 'C'); 
$pdf->Cell(60, 10, utf8_decode('Observaciones'), 1, 1, 'C');
$pdf->SetFont('Arial', '', 12);

$item = 1;
foreach ($data_primera_encuesta as $key => $value) {
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

// Segunda Encuesta - Construcción
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, utf8_decode('Segunda Encuesta - Detalles de Construcción'), 0, 1, 'L');
$pdf->Ln(5);

// Tabla de la segunda encuesta
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(80, 10, utf8_decode('Característica'), 1, 0, 'C'); 
$pdf->Cell(110, 10, utf8_decode('Detalle'), 1, 1, 'C');
$pdf->SetFont('Arial', '', 12);

$pdf->Cell(80, 10, utf8_decode('Tipo de Cimiento'), 1, 0, 'L');
$pdf->Cell(110, 10, utf8_decode($data_segunda_encuesta['tipo_cimiento']), 1, 1, 'C');

$pdf->Cell(80, 10, utf8_decode('Tipo de Mampostería'), 1, 0, 'L');
$pdf->Cell(110, 10, utf8_decode($data_segunda_encuesta['tipo_mamposteria']), 1, 1, 'C');

$pdf->Cell(80, 10, utf8_decode('Espesor de Mampostería'), 1, 0, 'L');
$pdf->Cell(110, 10, $data_segunda_encuesta['espesor_mamposteria'] . ' cm', 1, 1, 'C');

$pdf->Cell(80, 10, utf8_decode('Tipo de Estructura'), 1, 0, 'L');
$pdf->Cell(110, 10, utf8_decode($data_segunda_encuesta['tipo_estructura']), 1, 1, 'C');

$pdf->Cell(80, 10, utf8_decode('Tipo de Techo'), 1, 0, 'L');
$pdf->Cell(110, 10, utf8_decode($data_segunda_encuesta['tipo_techo']), 1, 1, 'C');

$pdf->Cell(80, 10, utf8_decode('Tipo de Contrapiso'), 1, 0, 'L');
$pdf->Cell(110, 10, utf8_decode($data_segunda_encuesta['tipo_contrapiso']), 1, 1, 'C');

$pdf->Cell(80, 10, utf8_decode('Espesor de Contrapiso'), 1, 0, 'L');
$pdf->Cell(110, 10, $data_segunda_encuesta['espesor_contrapiso'] . ' cm', 1, 1, 'C');

$pdf->Cell(80, 10, utf8_decode('Observaciones del Contrapiso'), 1, 0, 'L');

// Centrar las observaciones del contrapiso
$pdf->MultiCell(110, 10, utf8_decode($data_segunda_encuesta['observaciones_contrapiso']), 1, 'C');

// Salida del PDF
$pdf->Output('encuestas_completas.pdf', 'D'); 

?>

<?php
require('fpdf186/fpdf.php');
include('./db.php');

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Consulta para obtener los detalles del presupuesto y la entrevista personal
    $query_presupuesto = "SELECT p.*, e.ocupacion, e.habitantes, e.seguridad, e.trabajo_en_casa, e.salud, e.fobias, e.intereses, e.rutinas, e.pasatiempos, e.visitas, e.detalles_visitas, e.vehiculos, e.mascotas, e.aprendizaje, e.negocio, e.muebles, e.detalles_casa 
                          FROM presupuestos p 
                          LEFT JOIN primera_encuesta_new e ON p.id = e.id_presupuesto 
                          WHERE p.id = ?";
    $stmt = mysqli_prepare($conexion, $query_presupuesto);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $presupuesto = mysqli_fetch_assoc($result);

    mysqli_stmt_close($stmt);
    mysqli_close($conexion);

    // Crear el PDF
    if ($presupuesto) {
        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetMargins(10, 10, 10);

        // Agregar logo
        $pdf->Image('logo.png', 10, 10, 30);
        $pdf->Ln(20);

        // Título del documento con ID del cuestionario
        $pdf->SetFont('Arial', 'B', 20);
        $pdf->Cell(0, 10, utf8_decode('Cuestionario de Entrevista: ' . $presupuesto['id']), 0, 1, 'C'); 
        $pdf->Ln(10);

        $pdf->SetFont('Arial', '', 10);
        
        // Definir anchos de columna
        $widths = [60, 130];
        
        // Solo datos básicos del formulario inicial
        $fields = [
            'Nombre' => $presupuesto['nombre'] ?? '',
            'Teléfono' => $presupuesto['telefono'] ?? '',
            'Email' => $presupuesto['email'] ?? '',
            'Dirección' => $presupuesto['direccion'] ?? '',
            'Metros Cuadrados (m²)' => $presupuesto['m2_cantidad'] ?? '',
            'Tipo de Vivienda' => ucfirst($presupuesto['tipo_proyecto'] ?? ''),
            'Turno Programado' => $presupuesto['turno'] ?? '',
        ];

        // Mostrar los datos en la tabla
        foreach ($fields as $label => $value) {
            $pdf->Cell($widths[0], 8, utf8_decode($label), 1, 0, 'L');
            $pdf->Cell($widths[1], 8, utf8_decode($value), 1, 1, 'L');
        }

        // Salida del PDF
        $pdf->Output('D', 'Presupuesto_' . $presupuesto['id'] . '.pdf');
    } else {
        echo "Presupuesto no encontrado.";
    }
} else {
    echo "ID no especificado.";
}
?>

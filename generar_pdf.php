<?php
require('fpdf186/fpdf.php');
include('./db.php');

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Consulta para obtener los detalles del presupuesto
    $query_presupuesto = "SELECT * FROM presupuestos WHERE id = ?";
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
        $pdf->SetMargins(10, 10, 10); // Márgenes más pequeños

        // Agregar logo
        $pdf->Image('logo.png', 10, 10, 40);
        $pdf->Ln(20); // Espacio debajo del logo

        // Título del documento con ID del cuestionario
        $pdf->SetFont('Arial', 'B', 20);
        $pdf->Cell(0, 10, 'Cuestionario: ' . $presupuesto['id'], 0, 1, 'C'); 
        $pdf->Ln(10); // Espacio debajo del título


          // Configuración de la tabla
          $pdf->SetFont('Arial', 'B', 10); // Tamaño de fuente más pequeño
          $header = ['Campo', 'Respuesta'];
          $widths = [40, 140]; // Ajustar ancho de columnas
  
        $pdf->SetFont('Arial', '', 8); // Tamaño de fuente aún más pequeño
        $fields = [
            'Nombre' => $presupuesto['nombre'],
            'Ocupacion' => $presupuesto['ocupacion'],
            'Habitantes' => $presupuesto['habitantes'],
            'Seguridad' => $presupuesto['seguridad'],
            'Trabajo en Casa' => $presupuesto['trabajo_en_casa'],
            'Salud' => $presupuesto['salud'],
            'Telefono' => $presupuesto['telefono'],
            'Email' => $presupuesto['email'],
            'Direccion' => $presupuesto['direccion'],
            'Fobias' => $presupuesto['fobias'],
            'Intereses' => $presupuesto['intereses'],
            'Rutinas' => $presupuesto['rutinas'],
            'Pasatiempos' => $presupuesto['pasatiempos'],
            'Visitas' => $presupuesto['visitas'],
            'Detalles de Visitas' => $presupuesto['detalles_visitas'],
            'Vehiculos' => $presupuesto['vehiculos'],
            'Mascotas' => $presupuesto['mascotas'],
            'Aprendizaje' => $presupuesto['aprendizaje'],
            'Negocio' => $presupuesto['negocio'],
            'Muebles' => $presupuesto['muebles'],
            'Detalles de la Casa' => $presupuesto['detalles_casa'],
            // 'Turno' => $presupuesto['turno'], // Quitar "Turno" de aquí
        ];

        // Mostrar los datos en la tabla
        foreach ($fields as $label => $value) {
            $pdf->Cell($widths[0], 8, utf8_decode($label), 1, 0, 'L'); // Alineación a la izquierda
            $pdf->MultiCell($widths[1], 8, utf8_decode($value), 1, 'L'); // MultiCell para el valor
        }

        // Agregar "Turno" al final
        $pdf->Ln(5); // Espacio antes de "Turno"
        $pdf->SetFont('Arial', 'B', 10); 
        $pdf->Cell($widths[0], 10, utf8_decode('Turno'), 1, 0, 'L');
        $pdf->SetFont('Arial', '', 10); 
        $pdf->Cell($widths[1], 10, utf8_decode($presupuesto['turno']), 1, 1, 'L');

        // Salida del PDF
        $pdf->Output('D', 'Presupuesto_' . $presupuesto['id'] . '.pdf');
    } else {
        echo "Presupuesto no encontrado.";
    }
} else {
    echo "ID no especificado.";
}
?>

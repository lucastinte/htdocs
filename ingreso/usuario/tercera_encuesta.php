<?php
include('../../db.php');
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: ingreso.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_presupuesto = $_POST['id_presupuesto'];
    $tipo_cimiento = $_POST['tipo_cimiento'];
    $tipo_mamposteria = $_POST['tipo_mamposteria'];
    $espesor_mamposteria = $_POST['espesor_mamposteria'];
    $tipo_estructura = $_POST['tipo_estructura'];
    $tipo_techo = $_POST['tipo_techo'];
    $tipo_contrapiso = $_POST['tipo_contrapiso'];
    $espesor_contrapiso = $_POST['espesor_contrapiso'];
    $observaciones_contrapiso = $_POST['observaciones_contrapiso'];

    $insert_query = "INSERT INTO tercera_encuesta (id_presupuesto, tipo_cimiento, tipo_mamposteria, espesor_mamposteria, tipo_estructura, tipo_techo, tipo_contrapiso, espesor_contrapiso, observaciones_contrapiso) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conexion, $insert_query);
    mysqli_stmt_bind_param($stmt, "isssssdss", $id_presupuesto, $tipo_cimiento, $tipo_mamposteria, $espesor_mamposteria, $tipo_estructura, $tipo_techo, $tipo_contrapiso, $espesor_contrapiso, $observaciones_contrapiso);

    if (mysqli_stmt_execute($stmt)) {
        // Marcar entrevista como completada
        $update_query = "UPDATE presupuestos SET entrevista_completada = TRUE WHERE id = ?";
        $stmt_update = mysqli_prepare($conexion, $update_query);
        mysqli_stmt_bind_param($stmt_update, "i", $id_presupuesto);
        mysqli_stmt_execute($stmt_update);

        header("Location: presupuestos.php?success=1");
        exit();
    } else {
        echo "<p>Error al guardar los datos: " . mysqli_error($conexion) . "</p>";
    }
    mysqli_stmt_close($stmt);
    mysqli_close($conexion);
}

if (isset($_GET['id'])) {
    $id_presupuesto = $_GET['id'];
} else {
    echo "ID de presupuesto no especificado.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entrevista - Paso 3</title>
    <link rel="stylesheet" href="premium_forms.css"> 
</head>
<body>
<header>
    <div class="container">
        <nav><a href="segunda_encuesta.php?id=<?php echo $id_presupuesto; ?>"><button>Volver</button></a></nav>
    </div>
</header>
<section id="presupuestos"> 
    <h2>Datos Técnicos (Paso 3)</h2>
    <form action="tercera_encuesta.php" method="post">
        <input type="hidden" name="id_presupuesto" value="<?php echo $id_presupuesto; ?>">
        <div class="form-grid">
            <div class="form-group">
                <label>Tipo de cimiento:</label>
                <select name="tipo_cimiento">
                    <option value="tradicional">Tradicional</option>
                    <option value="radier">Radier</option>
                    <option value="plateas">Plateas</option>
                </select>
            </div>
            <div class="form-group">
                <label>Tipo de mampostería:</label>
                <select name="tipo_mamposteria">
                    <option value="ladrillo_comun">Ladrillo común</option>
                    <option value="ladrillo_hueco">Ladrillo hueco</option>
                    <option value="bloque_cemento">Bloque de cemento</option>
                </select>
            </div>
            <div class="form-group">
                <label>Espesor mampostería (cm):</label>
                <input type="number" name="espesor_mamposteria" step="0.1">
            </div>
            <div class="form-group">
                <label>Tipo de estructura:</label>
                <select name="tipo_estructura">
                    <option value="hormigon_armado">Hormigón armado</option>
                    <option value="metalica">Metálica</option>
                    <option value="madera">Madera</option>
                </select>
            </div>
            <div class="form-group">
                <label>Tipo de techo:</label>
                <select name="tipo_techo">
                    <option value="losa">Losa</option>
                    <option value="chapa">Chapa</option>
                    <option value="tejas">Tejas</option>
                </select>
            </div>
            <div class="form-group">
                <label>Tipo de contrapiso:</label>
                <select name="tipo_contrapiso">
                    <option value="hormigon_armado">Hormigón armado</option>
                    <option value="contrapiso_liviano">Contrapiso liviano</option>
                </select>
            </div>
            <div class="form-group">
                <label>Espesor contrapiso (cm):</label>
                <input type="number" name="espesor_contrapiso" step="0.1">
            </div>
        </div>
        <div class="form-group full-width">
            <label>Observaciones técnicas:</label>
            <textarea name="observaciones_contrapiso" rows="3"></textarea>
        </div>
        <button type="submit" class="btn-primary">Finalizar Encuesta</button>
    </form>
</section>
</body>
</html>

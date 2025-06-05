<?php
include('../../db.php');
session_start();

// Verifica que el usuario esté autenticado
if (!isset($_SESSION['usuario'])) {
    header("Location: ingreso.php");
    exit();
}

// Verifica si se ha enviado el formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_presupuesto = $_POST['id_presupuesto'];
    $tipo_cimiento = $_POST['tipo_cimiento'];
    $tipo_mamposteria = $_POST['tipo_mamposteria'];
    $tipo_estructura = $_POST['tipo_estructura'];
    $tipo_techo = $_POST['tipo_techo'];
    $tipo_contrapiso = $_POST['tipo_contrapiso'];
    $observaciones_contrapiso = $_POST['observaciones_contrapiso'];
    $espesor_mamposteria = $_POST['espesor_mamposteria'];
    $espesor_contrapiso = $_POST['espesor_contrapiso'];
    
    $insert_query = "INSERT INTO segunda_encuesta (id_presupuesto, tipo_cimiento, tipo_mamposteria, espesor_mamposteria, tipo_estructura, tipo_techo, tipo_contrapiso, espesor_contrapiso, observaciones_contrapiso) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conexion, $insert_query);
    mysqli_stmt_bind_param($stmt, "isssssdss", $id_presupuesto, $tipo_cimiento, $tipo_mamposteria, $espesor_mamposteria, $tipo_estructura, $tipo_techo, $tipo_contrapiso, $espesor_contrapiso, $observaciones_contrapiso);
    
    if (mysqli_stmt_execute($stmt)) {
        // Redirigir a presupuestos.php sin poner "localhost"
        header("Location: http://" . $_SERVER['HTTP_HOST'] . "/ingreso/usuario/presupuestos.php");
        exit();
    } else {
        // Mostrar un mensaje de error
        echo "<p>Error al guardar los datos: " . mysqli_error($conexion) . "</p>";
    }
    

    mysqli_stmt_close($stmt);
    mysqli_close($conexion);
}

// Obtener el ID del presupuesto de la URL
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
    <title>Segunda Encuesta</title>
    <link rel="stylesheet" href="usuarioform.css"> 
</head>
<body>
<header>
    <div class="container">
         <div class="user-badge">
          <?php if (isset($_SESSION['usuario'])): ?>
           <p class="logo"> <span class="user-icon">&#128100;</span> <?php echo htmlspecialchars($_SESSION['usuario']); ?></p>
          <?php endif; ?>
        </div>
        <nav>
            <a href="presupuestos.php"> <button>Volver a Gestión de Cuestionarios</button></a> 
        </nav>
    </div>
</header>
<section id="presupuestos"> 

<h2>Segunda Encuesta</h2>
<p>ID de presupuesto: <?php echo $id_presupuesto; ?></p>

<form action="segunda_encuesta.php" method="post">
    <input type="hidden" name="id_presupuesto" value="<?php echo $id_presupuesto; ?>">

    <h3>Necesidades</h3>

    <!-- Tipo de cimiento -->
    <div class="form-group">
        <label for="tipo_cimiento">Tipo de cimiento:</label>
        <select id="tipo_cimiento" name="tipo_cimiento">
            <option value="tradicional">Tradicional</option>
            <option value="radier">Radier</option>
            <option value="plateas">Plateas</option>
        </select>
    </div>

    <!-- Tipo de mampostería -->
    <div class="form-group">
        <label for="tipo_mamposteria">Tipo de mampostería:</label>
        <select id="tipo_mamposteria" name="tipo_mamposteria">
            <option value="ladrillo_comun">Ladrillo común</option>
            <option value="ladrillo_hueco">Ladrillo hueco</option>
            <option value="bloque_cemento">Bloque de cemento</option>
        </select>
    </div>

    <!-- Espesor de mampostería -->
    <div class="form-group">
        <label for="espesor_mamposteria">Espesor de mampostería (cm):</label>
        <input type="number" id="espesor_mamposteria" name="espesor_mamposteria" step="0.1">
    </div>

    <!-- Tipo de estructura -->
    <div class="form-group">
        <label for="tipo_estructura">Tipo de estructura:</label>
        <select id="tipo_estructura" name="tipo_estructura">
            <option value="hormigon_armado">Hormigón armado</option>
            <option value="metalica">Metálica</option>
            <option value="madera">Madera</option>
        </select>
    </div>

    <!-- Tipo de techo -->
    <div class="form-group">
        <label for="tipo_techo">Tipo de techo:</label>
        <select id="tipo_techo" name="tipo_techo">
            <option value="losa">Losa</option>
            <option value="chapa">Chapa</option>
            <option value="tejas">Tejas</option>
        </select>
    </div>

    <!-- Tipo de contrapiso -->
    <div class="form-group">
        <label for="tipo_contrapiso">Tipo de contrapiso:</label>
        <select id="tipo_contrapiso" name="tipo_contrapiso">
            <option value="hormigon_armado">Hormigón armado</option>
            <option value="contrapiso_liviano">Contrapiso liviano</option>
        </select>
    </div>

    <!-- Observaciones sobre el contrapiso -->
    <div class="form-group">
        <label for="observaciones_contrapiso">Observaciones sobre el contrapiso:</label>
        <textarea id="observaciones_contrapiso" name="observaciones_contrapiso"></textarea>
    </div>

    <!-- Espesor del contrapiso -->
    <div class="form-group">
        <label for="espesor_contrapiso">Espesor del contrapiso (cm):</label>
        <input type="number" id="espesor_contrapiso" name="espesor_contrapiso" step="0.1">
    </div>

    <button type="submit">Guardar</button>
</form>


</body>
</html>
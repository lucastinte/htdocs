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
    $ocupacion = $_POST['ocupacion'];
    $habitantes = $_POST['habitantes'];
    $seguridad = $_POST['seguridad'];
    $trabajo_en_casa = $_POST['trabajo_en_casa'];
    $salud = $_POST['salud'];
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

    // Consulta para insertar en la nueva tabla primera_encuesta_new
    $insert_query = "INSERT INTO primera_encuesta_new (id_presupuesto, ocupacion, habitantes, seguridad, trabajo_en_casa, salud, fobias, intereses, rutinas, pasatiempos, visitas, detalles_visitas, vehiculos, mascotas, aprendizaje, negocio, muebles, detalles_casa) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conexion, $insert_query);

    mysqli_stmt_bind_param($stmt, "isssssssssssssssss", $id_presupuesto, $ocupacion, $habitantes, $seguridad, $trabajo_en_casa, $salud, $fobias, $intereses, $rutinas, $pasatiempos, $visitas, $detalles_visitas, $vehiculos, $mascotas, $aprendizaje, $negocio, $muebles, $detalles_casa);

    if (mysqli_stmt_execute($stmt)) {
        // Redirigir a la segunda encuesta (ambientes)
        header("Location: segunda_encuesta.php?id=$id_presupuesto");
        exit();
    } else {
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
    <title>Entrevista - Paso 1</title>
    <link rel="stylesheet" href="premium_forms.css"> 
</head>
<body>
<header>
    <div class="container">
        <nav><a href="presupuestos.php"><button>Volver</button></a></nav>
    </div>
</header>
<section id="presupuestos"> 
    <h2>Entrevista Personalizada (Paso 1)</h2>
    <form action="continuar_entrevista.php" method="post">
        <input type="hidden" name="id_presupuesto" value="<?php echo $id_presupuesto; ?>">
        
        <div class="form-grid">
            <div class="form-group">
                <label>¿Cuál es su ocupación?</label>
                <textarea name="ocupacion" rows="2" required></textarea>
            </div>
            <div class="form-group">
                <label>¿Cuántas personas ocupan la casa?</label>
                <textarea name="habitantes" rows="2" required></textarea>
            </div>
            <div class="form-group">
                <label>Permanencia en casa (Seguridad):</label>
                <textarea name="seguridad" rows="2" required></textarea>
            </div>
            <div class="form-group">
                <label>Espacio de trabajo ideal:</label>
                <textarea name="trabajo_en_casa" rows="3" required></textarea>
            </div>
            <div class="form-group">
                <label>Discapacidades en el grupo familiar:</label>
                <textarea name="salud" rows="2" required></textarea>
            </div>
            <div class="form-group">
                <label>¿Tiene alguna fobia?</label>
                <input type="text" name="fobias">
            </div>
            <div class="form-group full-width">
                <label>Intereses y molestias (ventanas, techos, pasillos, etc):</label>
                <textarea name="intereses" rows="3"></textarea>
            </div>
            <div class="form-group full-width">
                <label>Rutinas diarias (Desde que amanece hasta que anochece):</label>
                <textarea name="rutinas" rows="3"></textarea>
            </div>
            <div class="form-group">
                <label>Pasatiempos:</label>
                <input type="text" name="pasatiempos">
            </div>
            <div class="form-group">
                <label>¿Recibe visitas?</label>
                <select name="visitas">
                    <option value="Diarias">Diarias</option>
                    <option value="Eventuales">Eventuales</option>
                    <option value="No">No</option>
                </select>
            </div>
            <div class="form-group full-width">
                <label>Detalles especiales (Ej: espacio para mascotas):</label>
                <textarea name="detalles_visitas" rows="2"></textarea>
            </div>
            <div class="form-group">
                <label>Vehículos (actuales y futuros):</label>
                <input type="text" name="vehiculos">
            </div>
            <div class="form-group">
                <label>Mascotas:</label>
                <input type="text" name="mascotas">
            </div>
            <div class="form-group">
                <label>¿Qué le gustaría aprender?</label>
                <input type="text" name="aprendizaje">
            </div>
            <div class="form-group">
                <label>Negocio anexo a vivienda:</label>
                <input type="text" name="negocio">
            </div>
            <div class="form-group full-width">
                <label>Muebles especiales a contemplar:</label>
                <textarea name="muebles" rows="2"></textarea>
            </div>
            <div class="form-group full-width">
                <label>Detalles de gusto personal (Jacuzzi, luces, etc):</label>
                <input type="text" name="detalles_casa">
            </div>
        </div>
        <button type="submit" class="btn-primary">Siguiente: Ambientes</button>
    </form>
</section>
</body>
</html>

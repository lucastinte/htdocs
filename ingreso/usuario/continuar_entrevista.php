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
    $tipo_cocina = $_POST['tipo_cocina'];
    $tipo_bano = $_POST['tipo_bano'];
    $tipo_dormitorio_principal = $_POST['tipo_dormitorio_principal'];
    $tipo_dormitorio_secundario = $_POST['tipo_dormitorio_secundario'];
    $tipo_comedor = $_POST['tipo_comedor'];
    $tipo_estar = $_POST['tipo_estar'];
    $tipo_patio_servicio = $_POST['tipo_patio_servicio'];
    $tipo_plantas = $_POST['tipo_plantas'];
    $tipo_escalera = $_POST['tipo_escalera'];
    $cantidad_habitantes = $_POST['cantidad_habitantes'];
    $capacidad_quincho = $_POST['capacidad_quincho'];
    $otros_ambientes = $_POST['otros_ambientes'];
    $tipo_cochera = $_POST['tipo_cochera'];

    // Consulta para insertar en la base de datos
    $insert_query = "INSERT INTO primera_encuesta (id_presupuesto, tipo_cocina, tipo_bano, tipo_dormitorio_principal, tipo_dormitorio_secundario, tipo_comedor, tipo_estar, tipo_patio_servicio, tipo_plantas, tipo_escalera, cantidad_habitantes, capacidad_quincho, otros_ambientes, tipo_cochera) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conexion, $insert_query);

    // Asegurarse de que el número de variables coincida con la cantidad de marcadores de posición
    mysqli_stmt_bind_param($stmt, "issssssssssiis", $id_presupuesto, $tipo_cocina, $tipo_bano, $tipo_dormitorio_principal, $tipo_dormitorio_secundario, $tipo_comedor, $tipo_estar, $tipo_patio_servicio, $tipo_plantas, $tipo_escalera, $cantidad_habitantes, $capacidad_quincho, $otros_ambientes, $tipo_cochera);


    if (mysqli_stmt_execute($stmt)) {
        // Actualizar el campo entrevista_completada en la tabla presupuestos
        $update_query = "UPDATE presupuestos SET entrevista_completada = TRUE WHERE id = ?";
        $stmt_update = mysqli_prepare($conexion, $update_query);
        mysqli_stmt_bind_param($stmt_update, "i", $id_presupuesto);
        mysqli_stmt_execute($stmt_update);
        mysqli_stmt_close($stmt_update);
    
        // Redirigir a la segunda encuesta
        header("Location: segunda_encuesta.php?id=$id_presupuesto");
        exit(); // Asegurarse de que el script se detenga aquí
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

<h2>Continuación de la Entrevista</h2>
<p>ID de presupuesto: <?php echo $id_presupuesto; ?></p>

<form action="continuar_entrevista.php" method="post">
    <input type="hidden" name="id_presupuesto" value="<?php echo $id_presupuesto; ?>">

    <h3>Ambientes</h3>
    <div class="form-group"> 
        <label for="tipo_cocina">Tipo de cocina:</label>
        <select id="tipo_cocina" name="tipo_cocina">
            <option value="simple">Simple</option>
            <option value="con_isla">Con isla</option>
            <option value="con_comedor">Con comedor</option>
            <option value="con_desayunador">Con desayunador</option>
        </select>
    </div>

    <div class="form-group"> 
        <label for="tipo_bano">Tipo de baño:</label>
        <select id="tipo_bano" name="tipo_bano">
            <option value="simple">Simple</option>
            <option value="con_antebano">Con antebaño</option>
        </select>
    </div>

    <div class="form-group"> 
        <label for="tipo_dormitorio_principal">Tipo de dormitorio principal:</label>
        <select id="tipo_dormitorio_principal" name="tipo_dormitorio_principal">
            <option value="simple">Simple</option>
            <option value="con_bano">Con baño</option>
            <option value="con_vestidor">Con vestidor</option>
            <option value="con_bano_y_vestidor">Con baño y vestidor</option>
        </select>
    </div>

    <div class="form-group"> 
        <label for="tipo_dormitorio_secundario">Tipo de dormitorio secundario:</label>
        <select id="tipo_dormitorio_secundario" name="tipo_dormitorio_secundario">
            <option value="simple">Simple</option>
            <option value="con_bano">Con baño</option>
            <option value="con_placard">Con placard</option>
        </select>
    </div>

    <div class="form-group"> 
        <label for="tipo_comedor">Tipo de comedor:</label>
        <select id="tipo_comedor" name="tipo_comedor">
            <option value="simple">Simple</option>
            <option value="integrado">Integrado</option>
        </select>
    </div>

    <div class="form-group"> 
        <label for="tipo_estar">Tipo de estar:</label>
        <select id="tipo_estar" name="tipo_estar">
            <option value="simple">Simple</option>
            <option value="integrado">Integrado</option>
            <option value="con_hogar">Con hogar</option>
        </select>
    </div>

    <div class="form-group"> 
        <label for="tipo_patio_servicio">Tipo de patio de servicio:</label>
        <select id="tipo_patio_servicio" name="tipo_patio_servicio">
            <option value="simple">Simple</option>
            <option value="con_lavadero">Con lavadero</option>
            <option value="con_deposito">Con depósito</option>
        </select>
    </div>

    <div class="form-group"> 
        <label for="tipo_plantas">Tipo de plantas:</label>
        <select id="tipo_plantas" name="tipo_plantas">
            <option value="ninguna">Ninguna</option>
            <option value="interior">Interior</option>
            <option value="exterior">Exterior</optionz>
            </select>
    </div>

    <div class="form-group"> 
        <label for="tipo_escalera">Tipo de escalera:</label>
        <select id="tipo_escalera" name="tipo_escalera">
            <option value="ninguna">Ninguna</option>
            <option value="interior">Interior</option>
            <option value="exterior">Exterior</option>
        </select>
    </div>

    <div class="form-group"> 
        <label for="cantidad_habitantes">Cantidad de habitantes:</label>
        <input type="number" id="cantidad_habitantes" name="cantidad_habitantes">
    </div>

    <div class="form-group"> 
        <label for="capacidad_quincho">Capacidad del quincho:</label>
        <input type="number" id="capacidad_quincho" name="capacidad_quincho">
    </div>

    <div class="form-group"> 
        <label for="otros_ambientes">Otros ambientes:</label>
        <textarea id="otros_ambientes" name="otros_ambientes"></textarea>
    </div>

    <div class="form-group"> 
        <label for="tipo_cochera">Tipo de cochera:</label>
        <select id="tipo_cochera" name="tipo_cochera">
            <option value="ninguna">Ninguna</option>
            <option value="simple">Simple</option>
            <option value="doble">Doble</option>
            <option value="galeria">Galería</option>
        </select>
    </div>

    <button type="submit">Ir a la Segunda Encuesta</button>
</form>

</body>
</html>
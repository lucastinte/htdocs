<?php
include('../../db.php');
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: ingreso.php");
    exit();
}

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

    $insert_query = "INSERT INTO segunda_encuesta_new (id_presupuesto, tipo_cocina, tipo_bano, tipo_dormitorio_principal, tipo_dormitorio_secundario, tipo_comedor, tipo_estar, tipo_patio_servicio, tipo_plantas, tipo_escalera, cantidad_habitantes, capacidad_quincho, otros_ambientes, tipo_cochera) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conexion, $insert_query);

    mysqli_stmt_bind_param($stmt, "issssssssssiss", $id_presupuesto, $tipo_cocina, $tipo_bano, $tipo_dormitorio_principal, $tipo_dormitorio_secundario, $tipo_comedor, $tipo_estar, $tipo_patio_servicio, $tipo_plantas, $tipo_escalera, $cantidad_habitantes, $capacidad_quincho, $otros_ambientes, $tipo_cochera);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: tercera_encuesta.php?id=$id_presupuesto");
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
    <title>Entrevista - Paso 2</title>
    <link rel="stylesheet" href="premium_forms.css"> 
</head>
<body>
<header>
    <div class="container">
        <nav><a href="continuar_entrevista.php?id=<?php echo $id_presupuesto; ?>"><button>Volver</button></a></nav>
    </div>
</header>
<section id="presupuestos"> 
    <h2>Ambientes de la Vivienda (Paso 2)</h2>
    <form action="segunda_encuesta.php" method="post">
        <input type="hidden" name="id_presupuesto" value="<?php echo $id_presupuesto; ?>">
        <div class="form-grid">
            <div class="form-group"> 
                <label>Tipo de cocina:</label>
                <select name="tipo_cocina">
                    <option value="simple">Simple</option>
                    <option value="con_isla">Con isla</option>
                    <option value="con_comedor">Con comedor</option>
                    <option value="con_desayunador">Con desayunador</option>
                </select>
            </div>
            <div class="form-group"> 
                <label>Tipo de baño:</label>
                <select name="tipo_bano">
                    <option value="simple">Simple</option>
                    <option value="con_antebano">Con antebaño</option>
                </select>
            </div>
            <div class="form-group"> 
                <label>Dormitorio principal:</label>
                <select name="tipo_dormitorio_principal">
                    <option value="simple">Simple</option>
                    <option value="con_bano">Con baño</option>
                    <option value="con_vestidor">Con vestidor</option>
                    <option value="con_bano_y_vestidor">Con baño y vestidor</option>
                </select>
            </div>
            <div class="form-group"> 
                <label>Dormitorio secundario:</label>
                <select name="tipo_dormitorio_secundario">
                    <option value="simple">Simple</option>
                    <option value="con_bano">Con baño</option>
                    <option value="con_placard">Con placard</option>
                </select>
            </div>
            <div class="form-group"> 
                <label>Comedor:</label>
                <select name="tipo_comedor">
                    <option value="simple">Simple</option>
                    <option value="integrado">Integrado</option>
                </select>
            </div>
            <div class="form-group"> 
                <label>Estar:</label>
                <select name="tipo_estar">
                    <option value="simple">Simple</option>
                    <option value="integrado">Integrado</option>
                    <option value="con_hogar">Con hogar</option>
                </select>
            </div>
            <div class="form-group"> 
                <label>Patio de servicio:</label>
                <select name="tipo_patio_servicio">
                    <option value="simple">Simple</option>
                    <option value="con_lavadero">Con lavadero</option>
                    <option value="con_deposito">Con depósito</option>
                </select>
            </div>
            <div class="form-group"> 
                <label>Plantas:</label>
                <select name="tipo_plantas">
                    <option value="ninguna">Ninguna</option>
                    <option value="interior">Interior</option>
                    <option value="exterior">Exterior</option>
                </select>
            </div>
            <div class="form-group"> 
                <label>Escalera:</label>
                <select name="tipo_escalera">
                    <option value="ninguna">Ninguna</option>
                    <option value="interior">Interior</option>
                    <option value="exterior">Exterior</option>
                </select>
            </div>
            <div class="form-group"> 
                <label>Cochera:</label>
                <select name="tipo_cochera">
                    <option value="ninguna">Ninguna</option>
                    <option value="simple">Simple</option>
                    <option value="doble">Doble</option>
                    <option value="galeria">Galería</option>
                </select>
            </div>
            <div class="form-group"> 
                <label>Habitantes:</label>
                <input type="number" name="cantidad_habitantes" min="0">
            </div>
            <div class="form-group"> 
                <label>Capacidad Quincho:</label>
                <input type="number" name="capacidad_quincho" min="0">
            </div>
        </div>
        <div class="form-group full-width"> 
            <label>Otros ambientes:</label>
            <textarea name="otros_ambientes" rows="3"></textarea>
        </div>
        <button type="submit" class="btn-primary">Siguiente: Datos Técnicos</button>
    </form>
</section>
</body>
</html>

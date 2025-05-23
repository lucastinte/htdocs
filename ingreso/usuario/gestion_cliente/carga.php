<?php
include('../../../db.php');
session_start();

// Redirigir si el usuario no está autenticado
if (!isset($_SESSION['usuario'])) {
    header("Location: ../ingreso/ingreso.php");
    exit();
}

// Manejo de la carga de proyectos
if (isset($_POST['cargar_proyecto'])) {
    $id_cliente = intval($_POST['id_cliente']);
    $nombre_proyecto = $_POST['nombre_proyecto'];
    $descripcion = $_POST['descripcion'];
    $fecha_inicio = $_POST['fecha_inicio'];
    $fecha_fin = $_POST['fecha_fin'];
    $estado = $_POST['estado'];

    // Insertar el proyecto en la base de datos
    $query_proyecto = "INSERT INTO proyectos (id_cliente, nombre_proyecto, descripcion, fecha_inicio, fecha_fin, estado) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conexion, $query_proyecto);
    mysqli_stmt_bind_param($stmt, "isssss", $id_cliente, $nombre_proyecto, $descripcion, $fecha_inicio, $fecha_fin, $estado);
    mysqli_stmt_execute($stmt);
    $id_proyecto = mysqli_insert_id($conexion);
    mysqli_stmt_close($stmt);

    // Manejo de la carga de archivos
    $upload_dir = "proyectos/";
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true); // Crear el directorio si no existe
    }

    $files = $_FILES['archivos'];
    for ($i = 0; $i < count($files['name']); $i++) {
        if ($files['error'][$i] === UPLOAD_ERR_OK) {
            $file_name = $files['name'][$i];
            $file_tmp = $files['tmp_name'][$i];
            $file_type = $files['type'][$i];
            $file_path = $upload_dir . $file_name;

            // Mover el archivo a la carpeta de destino
            if (move_uploaded_file($file_tmp, $file_path)) {
                $query_archivo = "INSERT INTO archivos (id_proyecto, nombre_archivo, tipo, ruta) VALUES (?, ?, ?, ?)";
                $stmt = mysqli_prepare($conexion, $query_archivo);
                mysqli_stmt_bind_param($stmt, "isss", $id_proyecto, $file_name, $file_type, $file_path);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            } else {
                echo "No se pudo mover el archivo: $file_name";
            }
        } else {
            echo "Error en la carga del archivo: " . $files['error'][$i];
        }
    }

    $message = "Proyecto y archivos cargados exitosamente.";
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Cargar Proyecto</title>
    <link rel="stylesheet" href="gestioncliente.css">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-image: linear-gradient(0deg, rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('/imagen/servicios/canchas.png');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            min-height: 100vh;
        }

        .sf form {
            background-color: rgba(255, 255, 255, 0.95);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
            margin: 40px auto;
        }

        h1,
        h2,
        h3 {
            color: blueviolet;
            text-align: center;
        }
    </style>
</head>

<body>
    <header>
        <div class="container">
            <p class="logo">Mat Construcciones</p>
            <nav>
                <a href="alta.php" class="btn-green">Crear Cliente</a>
                <a href="carga.php">Cargar Proyecto</a>
                <a href="proyectos.php">Ver Proyecto</a>
                <a href="gestioncliente.php">Volver</a>
            </nav>
        </div>
    </header>

         <?php if (isset($message)) { ?>
            <p class="message success" style="color: #c5ecc6; font-weight: bold; text-align: center; font-size: 1.1em; margin: 18px;">
                <?php echo htmlspecialchars($message); ?>
            </p>
            <?php } ?>
    <main>
        <div class="sf">
           
            <form action="carga.php" method="post" enctype="multipart/form-data">
                <h3>Cargar Proyecto al Cliente</h3>
                <div class="form-group">
                    <label for="id_cliente">Cliente:</label>
                    <select name="id_cliente" id="id_cliente" required>
                        <?php
                        $result = mysqli_query($conexion, "SELECT id, nombre FROM clientes");
                        while ($row = mysqli_fetch_assoc($result)) {
                            echo "<option value='" . htmlspecialchars($row['id']) . "'>" . htmlspecialchars($row['nombre']) . "</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="nombre_proyecto">Nombre del Proyecto:</label>
                    <input type="text" id="nombre_proyecto" name="nombre_proyecto" required>
                </div>
                <div class="form-group">
                    <label for="descripcion">Descripción:</label>
                    <textarea id="descripcion" name="descripcion" required></textarea>
                </div>
                <div class="form-group">
                    <label for="fecha_inicio">Fecha de Inicio:</label>
                    <input type="date" id="fecha_inicio" name="fecha_inicio" required>
                </div>
                <div class="form-group">
                    <label for="fecha_fin">Fecha de Fin:</label>
                    <input type="date" id="fecha_fin" name="fecha_fin">
                </div>
                <div class="form-group">
                    <label for="estado">Estado (% de avance):</label>
                    <input type="number" id="estado" name="estado" min="0" max="100" required>
                </div>
                <div class="form-group">
                    <label for="archivos">Archivos:</label>
                    <input type="file" id="archivos" name="archivos[]" multiple>
                </div>
                <button type="submit" name="cargar_proyecto">Cargar Proyecto</button>
            </form>
        </div>
    </main>
</body>

</html>

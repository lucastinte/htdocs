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
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cargar Proyecto</title>
    <link rel="stylesheet" href="carga.css">
    <style>
        .message {
            text-align: center;
            padding: 10px;
            margin: 10px auto;
            width: 80%;
            max-width: 600px;
            border-radius: 5px;
            font-size: 16px;
        }
        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
       
    </style>
</head>

<body>

<header>
        <div class="container">
            <p class="logo">Mat Construcciones</p>
            <nav>
                <a href="alta.php">Crear Cliente</a>
                <a href="carga.php">Cargar Proyecto</a>
                <a href="proyectos.php">Ver Proyecto</a>
                <a href="gestioncliente.php">Volver</a>
            </nav>
        </div>
    </header>

    <section id="upload-project">
        <h1>Cargar Proyecto</h1>

        <?php if (isset($message)) { ?>
        <p><?php echo htmlspecialchars($message); ?></p>
        <?php } ?>

        <form action="carga.php" method="post" enctype="multipart/form-data">
            <label for="id_cliente">Cliente:</label>
            <select name="id_cliente" id="id_cliente" required>
                <?php
                $result = mysqli_query($conexion, "SELECT id, nombre FROM clientes");
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<option value='" . htmlspecialchars($row['id']) . "'>" . htmlspecialchars($row['nombre']) . "</option>";
                }
                ?>
            </select>

            <label for="nombre_proyecto">Nombre del Proyecto:</label>
            <input type="text" id="nombre_proyecto" name="nombre_proyecto" required>

            <label for="descripcion">Descripción:</label>
            <textarea id="descripcion" name="descripcion" required></textarea>

            <label for="fecha_inicio">Fecha de Inicio:</label>
            <input type="date" id="fecha_inicio" name="fecha_inicio" required>

            <label for="fecha_fin">Fecha de Fin:</label>
            <input type="date" id="fecha_fin" name="fecha_fin">

            <label for="estado">Estado (% de avance):</label>
<input type="number" id="estado" name="estado" min="0" max="100" required>

            <label for="archivos">Archivos:</label>
            <input type="file" id="archivos" name="archivos[]" multiple>

            <button type="submit" name="cargar_proyecto">Cargar Proyecto</button>
        </form>
    </section>

</body>

</html>

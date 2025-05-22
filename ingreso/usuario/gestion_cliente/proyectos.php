<?php
include('../../../db.php');
session_start();

// Redirigir si el usuario no está autenticado
if (!isset($_SESSION['usuario'])) {
    header("Location: ../ingreso/ingreso.php");
    exit();
}

// Manejar la solicitud de eliminación AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'eliminar') {
    $id_proyecto = $_POST['id_proyecto'];

    // Eliminar archivos relacionados con el proyecto (opcional)
    $query_archivos = "DELETE FROM archivos WHERE id_proyecto = ?";
    $stmt_archivos = mysqli_prepare($conexion, $query_archivos);
    mysqli_stmt_bind_param($stmt_archivos, "i", $id_proyecto);
    mysqli_stmt_execute($stmt_archivos);

    // Eliminar el proyecto de la base de datos
    $query_proyecto = "DELETE FROM proyectos WHERE id = ?";
    $stmt_proyecto = mysqli_prepare($conexion, $query_proyecto);
    mysqli_stmt_bind_param($stmt_proyecto, "i", $id_proyecto);

    if (mysqli_stmt_execute($stmt_proyecto)) {
        // Responder con éxito en formato JSON
        echo json_encode(['success' => true, 'message' => 'Proyecto eliminado exitosamente.']);
    } else {
        // Responder con error en formato JSON
        echo json_encode(['success' => false, 'message' => 'Error al eliminar el proyecto.']);
    }
    exit();
}

// Obtener todos los proyectos
$query = "SELECT p.id, p.nombre_proyecto, c.nombre AS cliente, p.descripcion, p.fecha_inicio, p.fecha_fin, p.estado 
          FROM proyectos p
          JOIN clientes c ON p.id_cliente = c.id";
$result = mysqli_query($conexion, $query);

if (!$result) {
    die("Error en la consulta: " . mysqli_error($conexion));
}

// Obtener archivos para cada proyecto
function getProjectFiles($id_proyecto) {
    global $conexion;
    $query = "SELECT nombre_archivo, ruta, tipo FROM archivos WHERE id_proyecto = ?";
    $stmt = mysqli_prepare($conexion, $query);
    mysqli_stmt_bind_param($stmt, "i", $id_proyecto);
    mysqli_stmt_execute($stmt);
    return mysqli_stmt_get_result($stmt);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Proyectos</title>
    <link rel="stylesheet" href="gestioncliente.css">
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
        .btn-red {
            color: white;
            background-color: red;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
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

<section id="project-list">
    <h2>Estado en porcentaje de la obra</h2>

    <table>
        <thead>
            <tr>
                <th>Nombre del Proyecto</th>
                <th>Cliente</th>
                <th>Descripción</th>
                <th>Fecha de Inicio</th>
                <th>Fecha de Fin</th>
                <th>Estado (%)</th>
                <th>Archivos</th>
                <th>Actualizar Estado</th>
                <th>Eliminar</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($result)) { ?>
            <tr id="proyecto-<?php echo $row['id']; ?>"> <!-- Se añade un id único para cada fila del proyecto -->
                <td><?php echo htmlspecialchars($row['nombre_proyecto']); ?></td>
                <td><?php echo htmlspecialchars($row['cliente']); ?></td>
                <td><?php echo htmlspecialchars($row['descripcion']); ?></td>
                <td><?php echo htmlspecialchars($row['fecha_inicio']); ?></td>
                <td><?php echo htmlspecialchars($row['fecha_fin']); ?></td>
                <td><?php echo htmlspecialchars($row['estado']) . '%'; ?></td>
                <td>
                    <?php
                    $files = getProjectFiles($row['id']);
                    if (mysqli_num_rows($files) > 0) {
                        echo '<ul>';
                        while ($file = mysqli_fetch_assoc($files)) {
                            echo '<li><a href="/ingreso/usuario/gestion_cliente/' . htmlspecialchars($file['ruta']) . '" download>' . htmlspecialchars($file['nombre_archivo']) . '</a> (' . htmlspecialchars($file['tipo']) . ')</li>';
                        }
                        echo '</ul>';
                    } else {
                        echo 'No hay archivos.';
                    }
                    ?>
                </td>
                <td>
                    <form action="actualizar_estado.php" method="post">
                        <input type="hidden" name="id_proyecto" value="<?php echo $row['id']; ?>">
                        <input type="number" name="estado" min="0" max="100" value="<?php echo htmlspecialchars($row['estado']); ?>" required>
                        <button type="submit">Actualizar</button>
                    </form>
                </td>
                <td>
                    <!-- Botón para eliminar el proyecto usando AJAX -->
                    <button class="btn-red" onclick="eliminarProyecto(<?php echo $row['id']; ?>)">Eliminar</button>
                </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</section>

<script>
function eliminarProyecto(idProyecto) {
    // Realizar la solicitud AJAX
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "proyectos.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            // Si la respuesta es exitosa, elimina el proyecto de la tabla
            var respuesta = JSON.parse(xhr.responseText);
            if (respuesta.success) {
                // Eliminar la fila del proyecto del DOM
                var filaProyecto = document.getElementById("proyecto-" + idProyecto);
                filaProyecto.parentNode.removeChild(filaProyecto);
                showModalQ(respuesta.message, false, null, 'Éxito');
            } else {
                showModalQ("Error al eliminar el proyecto: " + respuesta.message, true, null, 'Error');
            }
        }
    };

    // Enviar la solicitud con el ID del proyecto
    xhr.send("id_proyecto=" + idProyecto + "&accion=eliminar");
}
</script>

</body>
</html>

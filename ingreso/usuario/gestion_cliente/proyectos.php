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

// Filtrar proyectos por ID de cliente si se proporciona
$id_cliente = isset($_GET['id_cliente']) ? intval($_GET['id_cliente']) : 0;

// Lógica de paginación
$proyectos_por_pagina = 4;
$pagina_actual = isset($_GET['pagina']) ? intval($_GET['pagina']) : 1;
$offset = ($pagina_actual - 1) * $proyectos_por_pagina;

// Obtener el total de proyectos
$query_total_proyectos = "SELECT COUNT(*) as total FROM proyectos";
$result_total_proyectos = mysqli_query($conexion, $query_total_proyectos);
$total_proyectos = mysqli_fetch_assoc($result_total_proyectos)['total'];
$total_paginas = ceil($total_proyectos / $proyectos_por_pagina);

// Obtener los proyectos para la página actual
$query = "SELECT p.id, p.nombre_proyecto, c.nombre AS cliente, p.descripcion, p.fecha_inicio, p.fecha_fin, p.estado 
          FROM proyectos p
          JOIN clientes c ON p.id_cliente = c.id" . ($id_cliente > 0 ? " WHERE p.id_cliente = $id_cliente" : "") . " 
          LIMIT $offset, $proyectos_por_pagina";
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
    <link rel="stylesheet" href="/modal-q.css">
    <script src="/modal-q.js"></script>
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
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }
        .pagination a {
            margin: 0 5px;
            padding: 10px 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            text-decoration: none;
            color: #333;
            background-color: #fff;
        }
        .pagination a.active {
            background-color: #4CAF50;
            color: white;
        }
    </style>
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
            <a href="alta.php" class="btn-green">Crear Cliente</a>

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
            <?php if (mysqli_num_rows($result) > 0) { ?>
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
                        <form action="actualizar_estado.php" method="post" onsubmit="return confirmarActualizarEstado(this);">
                            <input type="hidden" name="id_proyecto" value="<?php echo $row['id']; ?>">
                            <input type="number" name="estado" min="0" max="100" value="<?php echo htmlspecialchars($row['estado']); ?>" required>
                            <button type="submit">Actualizar</button>
                        </form>
                    </td>
                    <td>
                        <!-- Botón para eliminar el proyecto usando AJAX -->
                        <button class="btn-red" type="button" onclick="eliminarProyecto(<?php echo $row['id']; ?>)">Eliminar</button>
                    </td>
                </tr>
                <?php } ?>
            <?php } else { ?>
                <tr>
                    <td colspan="9" style="text-align: center;">No hay proyectos cargados para este cliente.</td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

    <!-- Mostrar botones de paginación -->
    <?php if ($total_paginas > 1) { ?>
        <div class="pagination">
            <?php for ($i = 1; $i <= $total_paginas; $i++) { ?>
                <a href="proyectos.php?pagina=<?php echo $i; ?>" class="<?php echo ($i == $pagina_actual ? 'active' : ''); ?>">
                    <?php echo $i; ?>
                </a>
            <?php } ?>
        </div>
    <?php } ?>
</section>

<!-- Modal Q reutilizable -->
<div id="modal-q" style="display:none;position:fixed;z-index:9999;left:0;top:0;width:100vw;height:100vh;background:rgba(0,0,0,0.6);justify-content:center;align-items:center;">
  <div class="modal-content">
    <h2 id="modal-q-title"></h2>
    <p id="modal-q-msg"></p>
  </div>
</div>

<script>
function confirmarActualizarEstado(form) {
    showModalQ('¿Estás seguro de que deseas actualizar el estado del proyecto?', false, null, 'Confirmar Actualización');
    setTimeout(() => {
        const modal = document.getElementById('modal-q');
        const content = modal.querySelector('.modal-content');
        let btns = content.querySelectorAll('button');
        btns.forEach(btn => btn.remove());
        // Botón Sí
        const btnSi = document.createElement('button');
        btnSi.textContent = 'Sí';
        btnSi.onclick = function() {
            closeModalQ();
            form.submit();
        };
        // Botón No
        const btnNo = document.createElement('button');
        btnNo.textContent = 'No';
        btnNo.onclick = function() {
            closeModalQ();
        };
        content.appendChild(btnSi);
        content.appendChild(btnNo);
    }, 100);
    return false;
}

function eliminarProyecto(idProyecto) {
    showModalQ('¿Estás seguro de que deseas eliminar este proyecto?', false, null, 'Confirmar Eliminación');
    setTimeout(() => {
        const modal = document.getElementById('modal-q');
        const content = modal.querySelector('.modal-content');
        let btns = content.querySelectorAll('button');
        btns.forEach(btn => btn.remove());
        // Botón Sí
        const btnSi = document.createElement('button');
        btnSi.textContent = 'Sí';
        btnSi.onclick = function() {
            closeModalQ();
            // Realizar la solicitud AJAX
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "proyectos.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    var respuesta = JSON.parse(xhr.responseText);
                    // Mostrar solo OK en el modal de resultado
                    showModalQ(respuesta.message, !respuesta.success, null, respuesta.success ? 'Éxito' : 'Error', respuesta.success ? 'success' : 'error');
                    setTimeout(() => {
                        const modal = document.getElementById('modal-q');
                        const content = modal.querySelector('.modal-content');
                        let btns = content.querySelectorAll('button');
                        btns.forEach(btn => btn.remove());
                        const btnOk = document.createElement('button');
                        btnOk.textContent = 'OK';
                        btnOk.onclick = function() {
                            closeModalQ();
                            window.location.reload();
                        };
                        content.appendChild(btnOk);
                    }, 100);
                    // Eliminar la fila solo si fue éxito
                    if (respuesta.success) {
                        var filaProyecto = document.getElementById("proyecto-" + idProyecto);
                        if (filaProyecto) filaProyecto.parentNode.removeChild(filaProyecto);
                    }
                }
            };
            xhr.send("id_proyecto=" + idProyecto + "&accion=eliminar");
        };
        // Botón No
        const btnNo = document.createElement('button');
        btnNo.textContent = 'No';
        btnNo.onclick = function() {
            closeModalQ();
        };
        content.appendChild(btnSi);
        content.appendChild(btnNo);
    }, 100);
}
</script>

</body>
</html>

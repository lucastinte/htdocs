<?php
include('../../db.php');
session_start();

// Verifica que el usuario esté autenticado
if (!isset($_SESSION['usuario'])) {
    header("Location: ingreso.php");
    exit();
}

// Eliminar turnos anteriores
$delete_query = "DELETE FROM turnos WHERE CONCAT(fecha, ' ', hora) < NOW()";
mysqli_query($conexion, $delete_query);

// Manejo de la creación, edición y eliminación de turnos y horarios disponibles
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'eliminar') {
            $id = intval($_POST['id']);
            $query = "DELETE FROM turnos WHERE id = ?";
            $stmt = mysqli_prepare($conexion, $query);
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, 'i', $id);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
        } elseif ($_POST['action'] == 'crear_horario') {
    $fecha_hora = $_POST['fecha_hora'];

    // Buscar horarios con diferencia menor a 30 minutos
    $query = "SELECT * FROM horarios_disponibles WHERE ABS(TIMESTAMPDIFF(MINUTE, fecha_hora, ?)) < 30";
    $stmt = mysqli_prepare($conexion, $query);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 's', $fecha_hora);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if (mysqli_num_rows($result) > 0) {
            // Ya existe un horario dentro de 30 minutos
            $_SESSION['error'] = 'Ya existe un horario dentro de los 30 minutos de diferencia.';
        } else {
            // Insertar el nuevo horario
            $insert_query = "INSERT INTO horarios_disponibles (fecha_hora, disponible) VALUES (?, TRUE)";
            $insert_stmt = mysqli_prepare($conexion, $insert_query);
            if ($insert_stmt) {
                mysqli_stmt_bind_param($insert_stmt, 's', $fecha_hora);
                mysqli_stmt_execute($insert_stmt);
                mysqli_stmt_close($insert_stmt);
                $_SESSION['success'] = 'Horario creado exitosamente.';
            }
        }
        mysqli_stmt_close($stmt);
    }
        } elseif ($_POST['action'] == 'eliminar_vencidos') {
            $delete_query = "DELETE FROM horarios_disponibles WHERE fecha_hora < NOW()";
            mysqli_query($conexion, $delete_query);
        } elseif ($_POST['action'] == 'eliminar_horario') {
            $id = intval($_POST['id']);
            $query = "DELETE FROM horarios_disponibles WHERE id = ?";
            $stmt = mysqli_prepare($conexion, $query);
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, 'i', $id);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
        } elseif ($_POST['action'] == 'editar_horario') {
            $id = intval($_POST['id']);
            $fecha_hora = $_POST['fecha_hora'];
            $query = "UPDATE horarios_disponibles SET fecha_hora = ? WHERE id = ?";
            $stmt = mysqli_prepare($conexion, $query);
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, 'si', $fecha_hora, $id);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
        }
    }
    mysqli_close($conexion);
    header("Location: turnos.php");
    exit();
}

// --- Paginación de turnos ---
$turnos_por_pagina = 5;
$total_turnos_query = "SELECT COUNT(*) as total FROM turnos WHERE CONCAT(fecha, ' ', hora) >= NOW()";
$total_turnos_result = mysqli_query($conexion, $total_turnos_query);
$total_turnos_row = mysqli_fetch_assoc($total_turnos_result);
$total_turnos = $total_turnos_row['total'];
$total_paginas = ceil($total_turnos / $turnos_por_pagina);
$pagina_actual = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
$offset = ($pagina_actual - 1) * $turnos_por_pagina;

$query_turnos = "SELECT * FROM turnos WHERE CONCAT(fecha, ' ', hora) >= NOW() ORDER BY fecha, hora LIMIT $turnos_por_pagina OFFSET $offset";
$result_turnos = mysqli_query($conexion, $query_turnos);

// --- Paginación de horarios disponibles ---
$horarios_por_pagina = 5;
$total_horarios_query = "SELECT COUNT(*) as total FROM horarios_disponibles WHERE disponible = TRUE";
$total_horarios_result = mysqli_query($conexion, $total_horarios_query);
$total_horarios_row = mysqli_fetch_assoc($total_horarios_result);
$total_horarios = $total_horarios_row['total'];
$total_paginas_horarios = ceil($total_horarios / $horarios_por_pagina);
$pagina_actual_horarios = isset($_GET['pagina_horarios']) ? max(1, intval($_GET['pagina_horarios'])) : 1;
$offset_horarios = ($pagina_actual_horarios - 1) * $horarios_por_pagina;

$query_horarios = "SELECT * FROM horarios_disponibles WHERE disponible = TRUE ORDER BY fecha_hora ASC LIMIT $horarios_por_pagina OFFSET $offset_horarios";
$result_horarios = mysqli_query($conexion, $query_horarios);

mysqli_close($conexion);
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Turnos</title>
    <link rel="stylesheet" href="usuarioform.css">
    <style>
        .btn-delete {
            background-color: #ff4d4d;
            color: white;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
        }
        .btn-create {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
        }
        .btn-remove-expired {
            background-color: #ffa500;
            color: white;
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
            
            <a href="usuario.php"><button>Volver</button></a>
        </nav>
    </div>
</header>

<section id="turnos">
    <h1>Gestión de Turnos</h1>
    <?php if (mysqli_num_rows($result_turnos) == 0) { ?>
        <p>No hay turnos registrados.</p>
    <?php } else { ?>
        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Apellido</th>
                    <th>Email</th>
                    <th>Teléfono</th>
                    <th>Fecha</th>
                    <th>Hora</th>
                    <th>Comentario</th>
                    <th>Presupuesto</th>
                    <th>Cliente Existente</th>
                    <th>Creado en</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result_turnos)) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['nombre']); ?></td>
                        <td><?php echo htmlspecialchars($row['apellido']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo htmlspecialchars($row['telefono']); ?></td>
                        <td><?php echo htmlspecialchars($row['fecha']); ?></td>
                        <td><?php echo htmlspecialchars($row['hora']); ?></td>
                        <td><?php echo htmlspecialchars($row['comentario']); ?></td>
                        <td><?php echo htmlspecialchars($row['presupuesto'] ? 'Sí' : 'No'); ?></td>
                        <td><?php echo htmlspecialchars($row['cliente_existente'] ? 'Sí' : 'No'); ?></td>
                        <td><?php echo htmlspecialchars($row['creado_en']); ?></td>
                        <td>
                            <form action="" method="POST" style="display: inline;">
                                <input type="hidden" name="id" value="<?php echo htmlspecialchars($row['id']); ?>">
                                <input type="hidden" name="action" value="eliminar">
                                <button type="submit" class="btn-delete">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
        <!-- Controles de paginación -->
        <div style="text-align:center; margin: 20px 0;">
            <?php if ($total_paginas > 1): ?>
                <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                    <?php if ($i == $pagina_actual): ?>
                        <strong style="margin:0 6px; color:#4CAF50; font-size:1.1em;">[<?php echo $i; ?>]</strong>
                    <?php else: ?>
                        <a href="?pagina=<?php echo $i; ?>" style="margin:0 6px; color:#333; text-decoration:none; font-weight:bold;"> <?php echo $i; ?> </a>
                    <?php endif; ?>
                <?php endfor; ?>
            <?php endif; ?>
        </div>
    <?php } ?>
</section>

<section id="horarios">
    <?php
if (isset($_SESSION['error'])) {
    echo "<p style='color: red; font-weight: bold; text-align:center'>" . $_SESSION['error'] . "</p>";
    unset($_SESSION['error']);
}
if (isset($_SESSION['success'])) {
    echo "<p style='color: green; font-weight: bold; text-align:center'>" . $_SESSION['success'] . "</p>";
    unset($_SESSION['success']);
}
?>

    <h2>Horarios Disponibles</h2>
    <?php if (mysqli_num_rows($result_horarios) == 0) { ?>
        <p>No hay horarios disponibles.</p>
    <?php } else { ?>
        <table>
            <thead>
                <tr>
                    <th>Fecha y Hora</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row_horarios = mysqli_fetch_assoc($result_horarios)) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row_horarios['fecha_hora']); ?></td>
                        <td>
                            <form action="turnos.php" method="POST" style="display: inline;">
                                <input type="hidden" name="action" value="eliminar_horario">
                                <input type="hidden" name="id" value="<?php echo htmlspecialchars($row_horarios['id']); ?>">
                                <button type="submit" class="btn-delete">Eliminar</button>
                            </form>
                            <form action="turnos.php" method="POST" style="display: inline;">
                                <input type="hidden" name="action" value="editar_horario">
                                <input type="hidden" name="id" value="<?php echo htmlspecialchars($row_horarios['id']); ?>">
                                <input type="datetime-local" name="fecha_hora" value="<?php echo htmlspecialchars($row_horarios['fecha_hora']); ?>" required>
                                <button type="submit" class="btn-create">Editar</button>
                            </form>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
        <!-- Controles de paginación para horarios -->
        <div style="text-align:center; margin: 20px 0;">
            <?php if ($total_paginas_horarios > 1): ?>
                <?php for ($i = 1; $i <= $total_paginas_horarios; $i++): ?>
                    <?php if ($i == $pagina_actual_horarios): ?>
                        <strong style="margin:0 6px; color:#4CAF50; font-size:1.1em;">[<?php echo $i; ?>]</strong>
                    <?php else: ?>
                        <a href="?pagina_horarios=<?php echo $i; ?>" style="margin:0 6px; color:#333; text-decoration:none; font-weight:bold;"> <?php echo $i; ?> </a>
                    <?php endif; ?>
                <?php endfor; ?>
            <?php endif; ?>
        </div>
    <?php } ?>

    <h3>Crear Nuevo Horario</h3>
    <form action="turnos.php" method="POST">
        <input type="hidden" name="action" value="crear_horario">
        <label for="fecha_hora">Fecha y Hora:</label>
        <input type="datetime-local" id="fecha_hora" name="fecha_hora" required min="<?php echo date('Y-m-d\TH:i'); ?>">
        <script>
    // Función para prevenir la selección de fechas pasadas usando el modal
    function preventPastDate() {
        var fechaHoraInput = document.getElementById('fecha_hora');
        fechaHoraInput.addEventListener('change', function() {
            if (new Date(this.value) < new Date()) {
                showModalQ('Por favor, selecciona una fecha y hora futuras.', true, null, 'Fecha inválida');
                this.value = '';
            }
        });
    }
    window.onload = preventPastDate;
    </script>
    <!-- Modal Q reutilizable -->
    <div id="modal-q" style="display:none">
      <div class="modal-content">
        <h2 id="modal-q-title"></h2>
        <p id="modal-q-msg"></p>
        <button onclick="closeModalQ()">OK</button>
      </div>
    </div>
    <link rel="stylesheet" href="../modal-q.css">
    <script src="../modal-q.js"></script>
        <button type="submit" class="btn-create">Crear Horario</button>
    </form>

    <h3>Eliminar Horarios Vencidos</h3>
    <form action="turnos.php" method="POST">
        <input type="hidden" name="action" value="eliminar_vencidos">
        <button type="submit" class="btn-remove-expired">Eliminar Horarios Vencidos</button>
    </form>
</section>

</body>
</html>

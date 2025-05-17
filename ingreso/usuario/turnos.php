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

    // Buscar horarios con diferencia menor a 5 minutos
    $query = "SELECT * FROM horarios_disponibles WHERE ABS(TIMESTAMPDIFF(MINUTE, fecha_hora, ?)) < 5";
    $stmt = mysqli_prepare($conexion, $query);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 's', $fecha_hora);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if (mysqli_num_rows($result) > 0) {
            // Ya existe un horario dentro de 5 minutos
            $_SESSION['error'] = 'Ya existe un horario dentro de los 5 minutos de diferencia.';
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

// Consultar turnos
$query_turnos = "SELECT * FROM turnos WHERE CONCAT(fecha, ' ', hora) >= NOW()";
$result_turnos = mysqli_query($conexion, $query_turnos);

// Consultar horarios disponibles
$query_horarios = "SELECT * FROM horarios_disponibles WHERE disponible = TRUE ORDER BY fecha_hora ASC";
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
    <?php } ?>

    <h3>Crear Nuevo Horario</h3>
    <form action="turnos.php" method="POST">
        <input type="hidden" name="action" value="crear_horario">
        <label for="fecha_hora">Fecha y Hora:</label>
        <input type="datetime-local" id="fecha_hora" name="fecha_hora" required min="<?php echo date('Y-m-d\TH:i'); ?>">
        <script>
    // Función para prevenir la selección de fechas pasadas (opcional, si quieres una validación adicional)
    function preventPastDate() {
        var fechaHoraInput = document.getElementById('fecha_hora');
        fechaHoraInput.addEventListener('change', function() {
            if (new Date(this.value) < new Date()) {
                alert('Por favor, selecciona una fecha y hora futuras.');
                this.value = ''; // Limpia el campo si se selecciona una fecha pasada
            }
        });
    }

    window.onload = preventPastDate;
</script>
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

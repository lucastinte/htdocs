<?php
include('../../db.php');
session_start();

// Verifica que el usuario esté autenticado
if (!isset($_SESSION['usuario'])) {
    header("Location: ingreso.php");
    exit();
}

// Manejo de la eliminación de presupuestos
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'eliminar') {
    $id = intval($_POST['id']);

    // Eliminar las encuestas asociadas
    $query_primera_encuesta = "DELETE FROM primera_encuesta WHERE id_presupuesto = ?";
    $stmt1 = mysqli_prepare($conexion, $query_primera_encuesta);
    mysqli_stmt_bind_param($stmt1, 'i', $id);
    mysqli_stmt_execute($stmt1);
    mysqli_stmt_close($stmt1);

    $query_segunda_encuesta = "DELETE FROM segunda_encuesta WHERE id_presupuesto = ?";
    $stmt2 = mysqli_prepare($conexion, $query_segunda_encuesta);
    mysqli_stmt_bind_param($stmt2, 'i', $id);
    mysqli_stmt_execute($stmt2);
    mysqli_stmt_close($stmt2);

    // Ahora eliminar el presupuesto
    $query = "DELETE FROM presupuestos WHERE id = ?";
    $stmt = mysqli_prepare($conexion, $query);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    mysqli_close($conexion);

    // Redirige de vuelta a la vista de gestión de presupuestos
    header("Location: presupuestos.php");
    exit();
}

// Consultar presupuestos
$query_presupuestos = "SELECT * FROM presupuestos";
$result_presupuestos = mysqli_query($conexion, $query_presupuestos);
mysqli_close($conexion);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Presupuestos</title>
    <link rel="stylesheet" href="usuarioform.css">
    <style>
        a {
    text-decoration: none; /* Elimina la línea subrayada */
    color: inherit; /* Hereda el color del texto del elemento padre */
}
        .icon-button {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1.2em;
            margin: 0 5px;
            padding: 5px;
            position: relative;
            transition: transform 0.2s ease;
        }

        .icon-button:hover {
            transform: scale(1.2);
        }

        .icon-button .tooltip {
            visibility: hidden;
            background-color: rgba(0, 0, 0, 0.8);
            color: #fff;
            text-align: center;
            border-radius: 5px;
            padding: 5px;
            position: absolute;
            z-index: 1;
            bottom: 125%;
            left: 50%;
            transform: translateX(-50%);
            white-space: nowrap;
            font-size: 0.8em;
            opacity: 0;
            transition: opacity 0.3s;
        }

        .icon-button:hover .tooltip {
            visibility: visible;
            opacity: 1;
        }

   
    </style>
</head>
<body>

<header>
        <div class="container">
            <p class="logo">Mat Construcciones</p>
            <nav>
                <a href="usuario.php"> <button>Volver</button></a>
            </nav>
        </div>
    </header>

<section id="presupuestos">
    <h1>Gestión de Presupuestos</h1>
    <?php if (mysqli_num_rows($result_presupuestos) == 0) { ?>
        <p>No hay presupuestos registrados.</p>
    <?php } else { ?>
        <table>
            <thead>
                <tr>
                    <th>Apellido y Nombre</th>
                    <th>Teléfono</th>
                    <th>Email</th>
                    <th>Dirección</th>
                    <th>Turno</th>
                    <th>Fecha de Creación</th>
                    <th>Acciones</th> 
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result_presupuestos)) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['nombre']); ?></td>
                        <td><?php echo htmlspecialchars($row['telefono']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo htmlspecialchars($row['direccion']); ?></td>
                        <td><?php echo htmlspecialchars($row['turno']); ?></td>
                        <td><?php echo htmlspecialchars($row['fecha_creacion']); ?></td>
                        <td>
                            <form action="" method="POST" style="display: inline;">
                                <input type="hidden" name="id" value="<?php echo htmlspecialchars($row['id']); ?>">
                                <input type="hidden" name="action" value="eliminar">
                                <button type="submit" class="icon-button icon-delete">
                                    🗑️<span class="tooltip">Eliminar</span>
                                </button>
                            </form>
                            <a href="../../generar_pdf.php?id=<?php echo htmlspecialchars($row['id']); ?>" class="icon-button icon-download">📄<span class="tooltip">Descargar Cuestionario</span>
                            </a>
                            <?php if ($row['entrevista_completada']) : ?>
                                <a href="descargar_encuestas.php?id=<?php echo htmlspecialchars($row['id']); ?>" class="icon-button icon-download">📥<span class="tooltip">Descargar Encuestas</span>
                                </a>
                            <?php else : ?>
                                <a href="continuar_entrevista.php?id=<?php echo htmlspecialchars($row['id']); ?>" class="icon-button icon-continue">➡️<span class="tooltip">Continuar Entrevista</span>
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    <?php } ?>
</section>

</body>
</html>

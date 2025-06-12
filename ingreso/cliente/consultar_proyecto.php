<?php
include('../../db.php');
session_start();

// Verifica que el usuario esté autenticado
if (!isset($_SESSION['usuario'])) {
    header("Location: ingreso.php");
    exit();
}

$usuario = $_SESSION['usuario'];

// Obtener el ID del cliente basado en el correo electrónico almacenado en la sesión
$query_cliente = "SELECT id FROM clientes WHERE email = ?";
$stmt_cliente = mysqli_prepare($conexion, $query_cliente);
mysqli_stmt_bind_param($stmt_cliente, "s", $usuario);
mysqli_stmt_execute($stmt_cliente);
$result_cliente = mysqli_stmt_get_result($stmt_cliente);

// Verificar si se obtuvo el ID del cliente
if ($row_cliente = mysqli_fetch_assoc($result_cliente)) {
    $id_cliente = $row_cliente['id'];
} else {
    die("Error al obtener el ID del cliente.");
}

// Lógica de paginación
$proyectos_por_pagina = 5;
$pagina_actual = isset($_GET['pagina']) ? intval($_GET['pagina']) : 1;
$offset = ($pagina_actual - 1) * $proyectos_por_pagina;

// Consultar proyectos asociados al cliente con límite de paginación
$query_proyectos = "SELECT p.id, p.nombre_proyecto AS nombre, p.descripcion, p.fecha_inicio, p.fecha_fin, p.estado
                    FROM proyectos p
                    WHERE p.id_cliente = ?
                    LIMIT $offset, $proyectos_por_pagina";
$stmt_proyectos = mysqli_prepare($conexion, $query_proyectos);
mysqli_stmt_bind_param($stmt_proyectos, "i", $id_cliente);
mysqli_stmt_execute($stmt_proyectos);
$result_proyectos = mysqli_stmt_get_result($stmt_proyectos);

// Obtener el total de proyectos
$query_total_proyectos = "SELECT COUNT(*) as total FROM proyectos WHERE id_cliente = ?";
$stmt_total_proyectos = mysqli_prepare($conexion, $query_total_proyectos);
mysqli_stmt_bind_param($stmt_total_proyectos, "i", $id_cliente);
mysqli_stmt_execute($stmt_total_proyectos);
$result_total_proyectos = mysqli_stmt_get_result($stmt_total_proyectos);
$total_proyectos = mysqli_fetch_assoc($result_total_proyectos)['total'];
$total_paginas = ceil($total_proyectos / $proyectos_por_pagina);

// Consultar archivos asociados a cada proyecto
$proyectos = [];
while ($row_proyecto = mysqli_fetch_assoc($result_proyectos)) {
    $proyecto_id = $row_proyecto['id'];
    $query_archivos = "SELECT id, nombre_archivo, tipo, ruta FROM archivos WHERE id_proyecto = ?";
    $stmt_archivos = mysqli_prepare($conexion, $query_archivos);
    mysqli_stmt_bind_param($stmt_archivos, "i", $proyecto_id);
    mysqli_stmt_execute($stmt_archivos);
    $result_archivos = mysqli_stmt_get_result($stmt_archivos);

    $archivos = [];
    while ($row_archivo = mysqli_fetch_assoc($result_archivos)) {
        $archivos[] = $row_archivo;
    }

    $row_proyecto['archivos'] = $archivos;
    $proyectos[] = $row_proyecto;
}

mysqli_close($conexion);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultar Proyectos</title>
    <link rel="stylesheet" href="clienteform.css">
    <style>
        /* Estilos para la barra de progreso */
        .progress-bar {
            width: 100%;
            background-color: #f3f3f3;
            border-radius: 5px;
            overflow: hidden;
            margin-top: 5px;
        }
        .progress-bar-fill {
            height: 10px;
            background-color: #4caf50;
            width: 0;
            transition: width 0.4s ease;
        }
        /* Estilos para la paginación */
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
        <p class="logo">Mat Construcciones</p>
        <nav>
            <a href="logout.php" class="logout-button">Salir</a>
            <a href="cambiar_contrasena.php">Cambiar Contraseña</a>
            <a href="eliminar_cuenta.php">Eliminar Mi Cuenta</a>
            <a href="modificar_datos.php">Modificar Datos</a>
            <a href="consultar_proyecto.php">Consultar Proyecto</a>
        </nav>
    </div>
</header>

<section id="proyectos">
    <h1>Mis Proyectos</h1>

    <?php if (empty($proyectos)) { ?>
        <p>No tienes proyectos asignados.</p>
    <?php } else { ?>
        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Fecha de Inicio</th>
                    <th>Fecha de Fin</th>
                    <th>Estado (%)</th>
                    <th>Archivos</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($proyectos as $proyecto) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($proyecto['nombre']); ?></td>
                        <td><?php echo htmlspecialchars($proyecto['descripcion']); ?></td>
                        <td><?php echo htmlspecialchars($proyecto['fecha_inicio']); ?></td>
                        <td><?php echo htmlspecialchars($proyecto['fecha_fin']); ?></td>
                        <td>
                            <?php echo htmlspecialchars($proyecto['estado']); ?>%
                            <div class="progress-bar">
                                <div class="progress-bar-fill" style="width: <?php echo htmlspecialchars($proyecto['estado']); ?>%;"></div>
                            </div>
                        </td>
                        <td>
                            <?php if (!empty($proyecto['archivos'])) { ?>
                                <ul>
                                    <?php foreach ($proyecto['archivos'] as $archivo) { ?>
                                        <li>
                                            <a href="/ingreso/usuario/gestion_cliente/<?php echo htmlspecialchars($archivo['ruta']); ?>" download>
                                                <?php echo htmlspecialchars($archivo['nombre_archivo']); ?>
                                            </a> (<?php echo htmlspecialchars($archivo['tipo']); ?>)
                                        </li>
                                    <?php } ?>
                                </ul>
                            <?php } else { ?>
                                No hay archivos.
                            <?php } ?>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    <?php } ?>

    <!-- Mostrar botones de paginación -->
    <?php if ($total_paginas > 1) { ?>
        <div class="pagination">
            <?php for ($i = 1; $i <= $total_paginas; $i++) { ?>
                <a href="consultar_proyecto.php?pagina=<?php echo $i; ?>" class="<?php echo ($i == $pagina_actual ? 'active' : ''); ?>">
                    <?php echo $i; ?>
                </a>
            <?php } ?>
        </div>
    <?php } ?>
    <a href="cliente.php" class="back-button">Volver a Gestión de Cliente</a>
</section>

</body>
</html>

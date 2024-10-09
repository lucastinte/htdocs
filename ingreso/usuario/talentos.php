<?php
include('../../db.php');
session_start();

// Verifica que el usuario esté autenticado
if (!isset($_SESSION['usuario'])) {
    header("Location: ingreso.php");
    exit();
}

// Manejo de la eliminación de talentos
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'eliminar') {
    $id = intval($_POST['id']);

    // Consulta para eliminar el talento
    $query = "DELETE FROM talentos WHERE id = ?";
    $stmt = mysqli_prepare($conexion, $query);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    mysqli_close($conexion);

    // Redirige de vuelta a la vista de gestión de talentos
    header("Location: talentos.php");
    exit();
}

// Consultar talentos
$query_talentos = "SELECT * FROM talentos";
$result_talentos = mysqli_query($conexion, $query_talentos);

mysqli_close($conexion);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Talentos</title>
    <link rel="stylesheet" href="usuarioform.css">
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

<section id="talentos">
    <h1>Gestión de Talentos</h1>
    <?php if (mysqli_num_rows($result_talentos) == 0) { ?>
        <p>No hay talentos registrados.</p>
    <?php } else { ?>
        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Puesto</th>
                    <th>CV</th>
                    <th>Fecha de Postulación</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result_talentos)) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['nombre']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo htmlspecialchars($row['puesto']); ?></td>
                        <td><a href="/<?php echo htmlspecialchars($row['cv_path']); ?>" download>Descargar CV</a></td>
                        <td><?php echo htmlspecialchars($row['fecha_postulacion']); ?></td>
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

</body>
</html>

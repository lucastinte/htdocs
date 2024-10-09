<?php
// Incluir el archivo de conexión a la base de datos
include('../../../db.php');
session_start();

// Redirigir si el usuario no está autenticado
if (!isset($_SESSION['usuario'])) {
    header("Location: ../ingreso/ingreso.php");
    exit();
}

// Manejo de actualización de nombre de usuario
if (isset($_POST['actualizar_usuario'])) {
    $id_usuario = intval($_POST['id_usuario']);
    $usuario = $_POST['usuario'];

    $update_query = "UPDATE usuarios SET usuario = ? WHERE id_usuario = ?";
    $stmt = mysqli_prepare($conexion, $update_query);
    mysqli_stmt_bind_param($stmt, "si", $usuario, $id_usuario);
    mysqli_stmt_execute($stmt);

    if (mysqli_stmt_affected_rows($stmt) > 0) {
        $message_usuario = "Nombre de usuario actualizado exitosamente.";
    } else {
        $message_usuario = "Error al actualizar el nombre de usuario.";
    }
    mysqli_stmt_close($stmt);
}

// Manejo de actualización de contraseña
if (isset($_POST['actualizar_password'])) {
    $id_usuario = intval($_POST['id_usuario']);
    $password = $_POST['password']; // Cambié 'contrasena' por 'password'

    // Aquí puedes agregar hashing para la contraseña si es necesario
    // $password = password_hash($password, PASSWORD_BCRYPT);

    $update_query = "UPDATE usuarios SET password = ? WHERE id_usuario = ?"; // Cambié 'contraseña' por 'password'
    $stmt = mysqli_prepare($conexion, $update_query);
    mysqli_stmt_bind_param($stmt, "si", $password, $id_usuario); // Cambié 'contrasena' por 'password'
    mysqli_stmt_execute($stmt);

    if (mysqli_stmt_affected_rows($stmt) > 0) {
        $message_password = "Contraseña actualizada exitosamente.";
    } else {
        $message_password = "Error al actualizar la contraseña.";
    }
    mysqli_stmt_close($stmt);
}

// Obtener todos los usuarios
$query = "SELECT id_usuario, nombre, usuario FROM usuarios";
$result = mysqli_query($conexion, $query);

if (!$result) {
    die("Error en la consulta: " . mysqli_error($conexion));
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modificar Usuarios</title>
    <link rel="stylesheet" href="../usuarioform.css">
    <script>
        function confirmUpdate(message) {
            return confirm(message);
        }
    </script>
</head>

<body>

    <header>
        <div class="container">
            <p class="logo">Mat Construcciones</p>
            <nav>
                <a href="formulario.php">Alta</a>
                <a href="baja.php">Baja</a>
                <a href="modificar.php">Modificar</a>
                <a href="gestionusuario.html">Volver</a>
            </nav>
        </div>
    </header>

    <section id="user-list">
        <h1>Modificar Usuarios</h1>

        <?php if (isset($message_usuario)) { ?>
        <p><?php echo htmlspecialchars($message_usuario); ?></p>
        <?php } ?>
        <?php if (isset($message_password)) { ?>
        <p><?php echo htmlspecialchars($message_password); ?></p>
        <?php } ?>

        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Usuario</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['nombre']); ?></td>
                    <td><?php echo htmlspecialchars($row['usuario']); ?></td>
                    <td>
                        <!-- Formulario para actualizar el nombre de usuario -->
                        <form action="modificar.php" method="post" style="display:inline;" onsubmit="return confirmUpdate('¿Estás seguro de que deseas actualizar el nombre de usuario de este usuario?');">
                            <input type="hidden" name="id_usuario" value="<?php echo htmlspecialchars($row['id_usuario']); ?>">
                            <input type="text" name="usuario" value="<?php echo htmlspecialchars($row['usuario']); ?>" placeholder="Nuevo Nombre de Usuario" required>
                            <button type="submit" name="actualizar_usuario">Actualizar Usuario</button>
                        </form>
                        <!-- Formulario para actualizar la contraseña -->
                        <form action="modificar.php" method="post" style="display:inline;" onsubmit="return confirmUpdate('¿Estás seguro de que deseas actualizar la contraseña de este usuario?');">
                            <input type="hidden" name="id_usuario" value="<?php echo htmlspecialchars($row['id_usuario']); ?>">
                            <input type="password" name="password" placeholder="Nueva Contraseña" required> <!-- Cambié 'contrasena' por 'password' -->
                            <button type="submit" name="actualizar_password">Actualizar Contraseña</button>
                        </form>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </section>

</body>

</html>

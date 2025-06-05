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
    <style>
        #form-modificar-usuario,
        #titulo-modificar-usuario {
            display: none;
        }
    </style>
    <script>
        function confirmUpdate(message) {
            return confirm(message);
        }

        function mostrarFormularioModificarUsuario(id, nombre, usuario) {
            document.getElementById('form-modificar-usuario').style.display = 'block';
            document.getElementById('titulo-modificar-usuario').style.display = 'block';
            document.getElementById('id_usuario').value = id;
            document.getElementById('nombre_usuario').value = nombre;
            document.getElementById('usuario_usuario').value = usuario;
            window.scrollTo({
                top: document.getElementById('form-modificar-usuario').offsetTop - 40,
                behavior: 'smooth'
            });
        }

        function ocultarFormularioModificarUsuario() {
            document.getElementById('form-modificar-usuario').style.display = 'none';
            document.getElementById('titulo-modificar-usuario').style.display = 'none';
        }
    </script>
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
                <a href="formulario.php">Alta</a>
                <a href="baja.php">Baja</a>
                <a href="modificar.php">Modificar</a>
                <a href="gestionusuario.html">Volver</a>
            </nav>
        </div>
    </header>

    <section id="user-list">
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
                        <?php if ($row['usuario'] !== 'gerente') { ?>
                            <button type="button"
                                onclick="mostrarFormularioModificarUsuario('<?php echo htmlspecialchars($row['id_usuario']); ?>', '<?php echo htmlspecialchars($row['nombre']); ?>', '<?php echo htmlspecialchars($row['usuario']); ?>')">Modificar</button>
                            <button type="button" onclick="confirmDeleteUsuario('<?php echo htmlspecialchars($row['id_usuario']); ?>')">Eliminar</button>
                        <?php } ?>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
        <h1 id="titulo-modificar-usuario" style="display:none;">Modificar Usuario</h1>
        <form action="modificar.php" method="post" id="form-modificar-usuario" style="display:none;">
            <input type="hidden" name="id_usuario" id="id_usuario">
            <div class="form-group">
                <label for="nombre_usuario">Nombre:</label>
                <input type="text" id="nombre_usuario" name="nombre" required>
            </div>
            <div class="form-group">
                <label for="usuario_usuario">Usuario:</label>
                <input type="text" id="usuario_usuario" name="usuario" required>
            </div>
            <button type="submit">Guardar Cambios</button>
            <button type="button" onclick="ocultarFormularioModificarUsuario()"
                style="margin-left:12px;background:#888;color:#fff;border:none;padding:8px 18px;border-radius:6px;cursor:pointer;">Cancelar</button>
        </form>
    </section>

</body>

</html>

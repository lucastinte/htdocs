<?php
include('../../../db.php');
session_start();

// Redirigir si el usuario no está autenticado
if (!isset($_SESSION['usuario'])) {
    header("Location: ../ingreso/ingreso.php");
    exit();
}

// Manejo de actualización de email
if (isset($_POST['actualizar_email'])) {
    $id_cliente = intval($_POST['id_cliente']);
    $email = $_POST['email'];

    $update_query = "UPDATE clientes SET email = ? WHERE id = ?";
    $stmt = mysqli_prepare($conexion, $update_query);
    mysqli_stmt_bind_param($stmt, "si", $email, $id_cliente);
    mysqli_stmt_execute($stmt);

    if (mysqli_stmt_affected_rows($stmt) > 0) {
        $message_email = "Email actualizado exitosamente.";
    } else {
        $message_email = "Error al actualizar el email.";
    }
    mysqli_stmt_close($stmt);
}

// Manejo de actualización de contraseña
if (isset($_POST['actualizar_password'])) {
    $id_cliente = intval($_POST['id_cliente']);
    $contrasena = $_POST['contrasena'];

    // Opcional: encriptar la contraseña antes de actualizar
    $contrasena = password_hash($contrasena, PASSWORD_DEFAULT);

    $update_query = "UPDATE clientes SET password = ? WHERE id = ?";
    $stmt = mysqli_prepare($conexion, $update_query);
    mysqli_stmt_bind_param($stmt, "si", $contrasena, $id_cliente);
    mysqli_stmt_execute($stmt);

    if (mysqli_stmt_affected_rows($stmt) > 0) {
        $message_password = "Contraseña actualizada exitosamente.";
    } else {
        $message_password = "Error al actualizar la contraseña.";
    }
    mysqli_stmt_close($stmt);
}

// Obtener todos los clientes
$query = "SELECT id, nombre, email FROM clientes";
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
    <title>Modificar Clientes</title>
    <link rel="stylesheet" href="../usuarioform.css">
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
    <script src="/modal-q.js"></script>
    <link rel="stylesheet" href="/modal-q.css">
    <script>
        // Mostrar mensajes de éxito/error con modal
        <?php if (isset($message_email) && $message_email) { ?>
          showModalQ('<?php echo addslashes($message_email); ?>', <?php echo (strpos($message_email, 'exito') !== false ? 'false' : 'true'); ?>, null, <?php echo (strpos($message_email, 'exito') !== false ? "'Éxito'" : "'Error'"); ?>, <?php echo (strpos($message_email, 'exito') !== false ? "'success'" : "'error'"); ?>);
          <?php if (strpos($message_email, 'exito') !== false) { ?>
          document.addEventListener('DOMContentLoaded', function() {
            const okBtn = document.querySelector('#modal-q button');
            if (okBtn) {
              okBtn.addEventListener('click', function() {
                window.location.href = 'gestioncliente.php';
              });
            }
          });
          <?php } ?>
        <?php } elseif (isset($message_password) && $message_password) { ?>
          showModalQ('<?php echo addslashes($message_password); ?>', <?php echo (strpos($message_password, 'exito') !== false ? 'false' : 'true'); ?>, null, <?php echo (strpos($message_password, 'exito') !== false ? "'Éxito'" : "'Error'"); ?>, <?php echo (strpos($message_password, 'exito') !== false ? "'success'" : "'error'"); ?>);
          <?php if (strpos($message_password, 'exito') !== false) { ?>
          document.addEventListener('DOMContentLoaded', function() {
            const okBtn = document.querySelector('#modal-q button');
            if (okBtn) {
              okBtn.addEventListener('click', function() {
                window.location.href = 'gestioncliente.php';
              });
            }
          });
          <?php } ?>
        <?php } ?>
        // Confirmación con modal para actualizar
        function confirmUpdate(message) {
            showModalQ(message, false, null, 'Confirmar Acción');
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
                    event.target.form.submit();
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
        </script>
</head>

<body>

    <header>
        <div class="container">
            <p class="logo">Mat Construcciones</p>
            <nav>
                <a href="alta.php">Alta</a>
                <a href="baja.php">Baja</a>
                <a href="modificar.php">Modificar</a>
                <a href="carga.php">Cargar Proyecto</a>
                <a href="proyectos.php">Ver Proyectos</a>
                <a href="gestioncliente.html">Volver</a>
            </nav>
        </div>
    </header>

    <section id="client-list">
        <h1>Modificar Clientes</h1>
        <!-- Modal Q para mensajes -->
        <div id="modal-q" style="display:none;position:fixed;z-index:9999;left:0;top:0;width:100vw;height:100vh;background:rgba(0,0,0,0.6);justify-content:center;align-items:center;">
          <div class="modal-content">
            <h2 id="modal-q-title"></h2>
            <p id="modal-q-msg"></p>
            <button onclick="closeModalQ()">OK</button>
          </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['nombre']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td>
                        <!-- Formulario para actualizar el email -->
                        <form action="modificar.php" method="post" style="display:inline;" onsubmit="return confirmUpdate('¿Estás seguro de que deseas actualizar el email de este cliente?');">
                            <input type="hidden" name="id_cliente" value="<?php echo htmlspecialchars($row['id']); ?>">
                            <input type="email" name="email" value="<?php echo htmlspecialchars($row['email']); ?>" placeholder="Nuevo Email" required>
                            <button type="submit" name="actualizar_email">Actualizar Email</button>
                        </form>
                        <!-- Formulario para actualizar la contraseña -->
                        <form action="modificar.php" method="post" style="display:inline;" onsubmit="return confirmUpdate('¿Estás seguro de que deseas actualizar la contraseña de este cliente?');">
                            <input type="hidden" name="id_cliente" value="<?php echo htmlspecialchars($row['id']); ?>">
                            <input type="password" name="contrasena" placeholder="Nueva Contraseña" required>
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

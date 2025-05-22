<?php
include('../../../db.php');
session_start();

// Redirigir si el usuario no está autenticado
if (!isset($_SESSION['usuario'])) {
    header("Location: ../ingreso/ingreso.php");
    exit();
}

$message_usuario = "";
$message_password = "";
$message_proyecto = "";

// Manejo de acciones del formulario (Modificación de Cliente, Eliminación de Cliente, Carga de Proyecto)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $accion = $_POST['accion'];
    $id_cliente = $_POST['id_cliente'];

    if ($accion == 'modificar') {
        // Obtener los datos del formulario
        $apellido = mysqli_real_escape_string($conexion, $_POST['apellido']);
        $nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
        $dni = mysqli_real_escape_string($conexion, $_POST['dni']);
        $caracteristica_tel = mysqli_real_escape_string($conexion, $_POST['caracteristica_tel']);
        $numero_tel = mysqli_real_escape_string($conexion, $_POST['numero_tel']);
        $email = mysqli_real_escape_string($conexion, $_POST['email']);
        $usuario = mysqli_real_escape_string($conexion, $_POST['usuario']);
        $direccion = mysqli_real_escape_string($conexion, $_POST['direccion']);
        $fecha_nacimiento = mysqli_real_escape_string($conexion, $_POST['fecha_nacimiento']);
        $password = $_POST['password'];

        // Actualizar los datos del cliente
        if (!empty($password)) {
            // Si se proporciona una nueva contraseña, se actualiza
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $query = "UPDATE clientes SET apellido='$apellido', nombre='$nombre', dni='$dni', caracteristica_tel='$caracteristica_tel', numero_tel='$numero_tel', email='$email', usuario='$usuario', direccion='$direccion', fecha_nacimiento='$fecha_nacimiento', password='$hashed_password' WHERE id=$id_cliente";
        } else {
            // Solo actualizar los datos sin cambiar la contraseña
            $query = "UPDATE clientes SET apellido='$apellido', nombre='$nombre', dni='$dni', caracteristica_tel='$caracteristica_tel', numero_tel='$numero_tel', email='$email', usuario='$usuario', direccion='$direccion', fecha_nacimiento='$fecha_nacimiento' WHERE id=$id_cliente";
        }

        if (mysqli_query($conexion, $query)) {
            $message_usuario = "Cliente modificado correctamente.";
        } else {
            $message_usuario = "Error al modificar el cliente: " . mysqli_error($conexion);
        }
    } elseif ($accion == 'eliminar') {
        // Código para eliminar cliente
        $query = "DELETE FROM clientes WHERE id=$id_cliente";
    
        if (mysqli_query($conexion, $query)) {
            $message_usuario = "Cliente y sus proyectos asociados eliminados correctamente."; 
        } else {
            $message_usuario = "Error al eliminar el cliente: " . mysqli_error($conexion);
        }
    
    } elseif ($accion == 'cargar_proyecto') {
        // Código para carga de proyecto (aquí puedes añadir la lógica para cargar proyectos)
    } elseif ($accion == 'activar') {
        // Activar cliente
        $query = "UPDATE clientes SET activo = 1 WHERE id = $id_cliente";
        if (mysqli_query($conexion, $query)) {
            $message_usuario = "Cliente activado correctamente.";
        } else {
            $message_usuario = "Error al activar el cliente: " . mysqli_error($conexion);
        }
    }
}

// Obtener todos los clientes
$query = "SELECT id, apellido, nombre, dni, caracteristica_tel, numero_tel, email, usuario, direccion, fecha_nacimiento, activo FROM clientes";
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
    <title>Gestión de Clientes</title>
    <link rel="stylesheet" href="gestioncliente.css">
    <link rel="stylesheet" href="/modal-q.css">
    <script src="/modal-q.js"></script>
    <script>
        function confirmAction(message) {
            return confirm(message);
        }

        function confirmActionModal(message, callback) {
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
                    callback(true);
                };
                // Botón No
                const btnNo = document.createElement('button');
                btnNo.textContent = 'No';
                btnNo.onclick = function() {
                    closeModalQ();
                    callback(false);
                };
                content.appendChild(btnSi);
                content.appendChild(btnNo);
            }, 100);
        }

        // Reemplazo para formularios de eliminar y activar
        function handleFormSubmitWithModal(e, message) {
            e.preventDefault();
            confirmActionModal(message, function(confirmado) {
                if (confirmado) {
                    e.target.submit();
                }
            });
            return false;
        }

        function rellenarFormulario(id, apellido, nombre, dni, caracteristica_tel, numero_tel, email, usuario, direccion, fecha_nacimiento) {
            document.getElementById('accion').value = 'modificar';
            document.getElementById('id_cliente').value = id;
            document.getElementById('apellido').value = apellido;
            document.getElementById('nombre').value = nombre;
            document.getElementById('dni').value = dni;
            document.getElementById('caracteristica_tel').value = caracteristica_tel;
            document.getElementById('numero_tel').value = numero_tel;
            document.getElementById('email').value = email;
            document.getElementById('usuario').value = usuario;
            document.getElementById('direccion').value = direccion;
            document.getElementById('fecha_nacimiento').value = fecha_nacimiento;
        }
    </script>
</head>
<body>

    <header>
        <div class="container">
            <p class="logo">Mat Construcciones</p>
            <nav>
                <a href="alta.php" class="btn-green">Crear Cliente</a>
                <a href="carga.php">Cargar Proyecto</a>
                <a href="proyectos.php">Ver Proyecto</a>
                <a href="../usuario.php">Volver</a>
            </nav>
        </div>
    </header>

    <section id="client-management">
        <div class="container">

            <?php if (!empty($message_usuario) || !empty($message_password) || !empty($message_proyecto)) { ?>
            <p>
                <?php echo htmlspecialchars(isset($message_usuario) ? $message_usuario : (isset($message_password) ? $message_password : $message_proyecto)); ?>
            </p>
            <?php } ?>

            <h2>Lista de Clientes</h2>
            <table>
                <thead>
                    <tr>
                        <th>Apellido</th>
                        <th>Nombre</th>
                        <th>DNI</th>
                        <th>Código de Área</th>
                        <th>Número de Teléfono</th>
                        <th>Email</th>
                        <th>Usuario</th>
                        <th>Dirección</th>
                        <th>Fecha de Nacimiento</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['apellido']); ?></td>
                        <td><?php echo htmlspecialchars($row['nombre']); ?></td>
                        <td><?php echo htmlspecialchars($row['dni']); ?></td>
                        <td><?php echo htmlspecialchars($row['caracteristica_tel']); ?></td>
                        <td><?php echo htmlspecialchars($row['numero_tel']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo htmlspecialchars($row['usuario']); ?></td>
                        <td><?php echo htmlspecialchars($row['direccion']); ?></td>
                        <td><?php echo htmlspecialchars($row['fecha_nacimiento']); ?></td>
                        <td>
                            <button type="button" onclick="rellenarFormulario(
                                '<?php echo htmlspecialchars($row['id']); ?>',
                                '<?php echo htmlspecialchars($row['apellido']); ?>',
                                '<?php echo htmlspecialchars($row['nombre']); ?>',
                                '<?php echo htmlspecialchars($row['dni']); ?>',
                                '<?php echo htmlspecialchars($row['caracteristica_tel']); ?>',
                                '<?php echo htmlspecialchars($row['numero_tel']); ?>',
                                '<?php echo htmlspecialchars($row['email']); ?>',
                                '<?php echo htmlspecialchars($row['usuario']); ?>',
                                '<?php echo htmlspecialchars($row['direccion']); ?>',
                                '<?php echo htmlspecialchars($row['fecha_nacimiento']); ?>'
                            );">Seleccionar</button>
                            <form action="gestioncliente.php" method="post" style="display:inline;" onsubmit="return handleFormSubmitWithModal(event, '¿Estás seguro de que deseas eliminar este cliente?');">
                                <input type="hidden" name="accion" value="eliminar">
                                <input type="hidden" name="id_cliente" value="<?php echo htmlspecialchars($row['id']); ?>">
                                <button type="submit">Eliminar</button>
                            </form>
                            <?php if ($row['activo'] == 0) { ?>
<form action="gestioncliente.php" method="post" style="display:inline;" onsubmit="return handleFormSubmitWithModal(event, '¿Estás seguro de que deseas activar este cliente?');">
    <input type="hidden" name="accion" value="activar">
    <input type="hidden" name="id_cliente" value="<?php echo htmlspecialchars($row['id']); ?>">
    <button type="submit">Activar</button>
</form>
<?php } ?>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>

            <h2>Modificar Cliente</h2>
            <div class="sf">
                <form action="gestioncliente.php" method="post" id="form-modificar-cliente">
                    <input type="hidden" name="accion" id="accion" value="">
                    <input type="hidden" name="id_cliente" id="id_cliente" value="">
                    <div class="form-group">
                        <label for="apellido">Apellido:</label>
                        <input type="text" id="apellido" name="apellido" required>
                    </div>
                    <div class="form-group">
                        <label for="nombre">Nombre:</label>
                        <input type="text" id="nombre" name="nombre" required>
                    </div>
                    <div class="form-group">
                        <label for="dni">DNI:</label>
                        <input type="text" id="dni" name="dni" required>
                    </div>
                    <div class="form-group">
                        <label for="caracteristica_tel">Código de Área:</label>
                        <input type="text" id="caracteristica_tel" name="caracteristica_tel" required>
                    </div>
                    <div class="form-group">
                        <label for="numero_tel">Número de Teléfono:</label>
                        <input type="text" id="numero_tel" name="numero_tel" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="usuario">Usuario:</label>
                        <input type="text" id="usuario" name="usuario" required>
                    </div>
                    <div class="form-group">
                        <label for="direccion">Dirección:</label>
                        <input type="text" id="direccion" name="direccion" required>
                    </div>
                    <div class="form-group">
                        <label for="fecha_nacimiento">Fecha de Nacimiento:</label>
                        <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Contraseña:</label>
                        <input type="password" id="password" name="password" placeholder="Solo ingrese si desea cambiarla">
                    </div>
                    <button type="submit" onclick="document.getElementById('accion').value = 'modificar'; return confirmModificarCliente(event);">Modificar</button>
                </form>
            </div>
            <script>
function confirmModificarCliente(e) {
    e.preventDefault();
    confirmActionModal('¿Estás seguro de que deseas realizar esta acción?', function(confirmado) {
        if (confirmado) {
            document.getElementById('form-modificar-cliente').submit();
        }
    });
    return false;
}
</script>

        </div>
    </section>

    <!-- Modal Q para mensajes -->
<div id="modal-q" style="display:none;position:fixed;z-index:9999;left:0;top:0;width:100vw;height:100vh;background:rgba(0,0,0,0.6);justify-content:center;align-items:center;">
  <div class="modal-content" style="background:#fff;color:#181828;border-radius:24px;padding:40px 30px 30px 30px;text-align:center;min-width:320px;max-width:90vw;box-shadow:0 8px 32px rgba(0,0,0,0.25);transition:border 0.2s, color 0.2s;">
    <h2 id="modal-q-title"></h2>
    <p id="modal-q-msg"></p>
    <button onclick="closeModalQ()">OK</button>
  </div>
</div>

</body>
</html>

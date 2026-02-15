<?php
include('../../../db.php');
session_start();

// Redirigir si el usuario no está autenticado
if (!isset($_SESSION['usuario'])) {
    header("Location: ../ingreso/ingreso.php");
    exit();
}

$message_usuario = "";
$usuario_actual = $_SESSION['usuario'];

// Debug: Imprimir valores POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    error_log("POST recibido: " . print_r($_POST, true));
}

// Obtener el ID del usuario actual logueado
$query_usuario_logueado = "SELECT id_usuario FROM usuarios WHERE usuario = ?";
$stmt_usuario_logueado = $conexion->prepare($query_usuario_logueado);
$stmt_usuario_logueado->bind_param("s", $usuario_actual);
$stmt_usuario_logueado->execute();
$result_usuario_logueado = $stmt_usuario_logueado->get_result();
$id_usuario_logueado = null;

if ($result_usuario_logueado && $result_usuario_logueado->num_rows > 0) {
    $id_usuario_logueado = $result_usuario_logueado->fetch_assoc()['id_usuario'];
}
// Obtener permisos del usuario actual
$query_permisos = "SELECT permisos FROM usuarios WHERE usuario = ?";
$stmt_permisos = $conexion->prepare($query_permisos);
$stmt_permisos->bind_param("s", $usuario_actual);
$stmt_permisos->execute();
$result_permisos = $stmt_permisos->get_result();
$permisos_usuario_actual = null;
if ($result_permisos && $result_permisos->num_rows > 0) {
    $permisos_usuario_actual = $result_permisos->fetch_assoc()['permisos'];
} else {
    // Manejar el caso en que no se encuentra el usuario
    $message_usuario = "No se encontraron permisos para el usuario actual.";
    // Dependiendo de la lógica de tu aplicación, podrías redirigir o mostrar un mensaje de error
    header("Location: /ingreso/ingreso.php"); 
    exit();
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $accion = $_POST['accion'];
    $id_usuario = $_POST['id_usuario'];
    $nombre = isset($_POST['nombre']) ? mysqli_real_escape_string($conexion, $_POST['nombre']) : '';
    $apellido = isset($_POST['apellido']) ? mysqli_real_escape_string($conexion, $_POST['apellido']) : '';
    $dni = isset($_POST['dni']) ? mysqli_real_escape_string($conexion, $_POST['dni']) : '';
    $email = isset($_POST['email']) ? mysqli_real_escape_string($conexion, $_POST['email']) : '';
    $fecha_nacimiento = isset($_POST['fecha_nacimiento']) ? mysqli_real_escape_string($conexion, $_POST['fecha_nacimiento']) : '';
    $telefono = isset($_POST['telefono']) ? mysqli_real_escape_string($conexion, $_POST['telefono']) : '';
    $puesto = isset($_POST['puesto']) ? mysqli_real_escape_string($conexion, $_POST['puesto']) : '';
    $usuario = isset($_POST['usuario']) ? mysqli_real_escape_string($conexion, $_POST['usuario']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $usuario = isset($_POST['usuario']) ? mysqli_real_escape_string($conexion, $_POST['usuario']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    // Obtener el permiso original de la base de datos
    $query_permiso_original = "SELECT permisos FROM usuarios WHERE id_usuario = ?";
    $stmt_permiso_original = $conexion->prepare($query_permiso_original);
    $stmt_permiso_original->bind_param("i", $id_usuario);
    $stmt_permiso_original->execute();
    $result_permiso_original = $stmt_permiso_original->get_result();
    $permiso_original = '';
    if ($result_permiso_original && $result_permiso_original->num_rows > 0) {
        $permiso_original = $result_permiso_original->fetch_assoc()['permisos'];
    }

    // Si el campo permisos no viene en el POST o está vacío, usar el original
    if (isset($_POST['permisos']) && trim($_POST['permisos']) !== '') {
        $permisos = mysqli_real_escape_string($conexion, $_POST['permisos']);
    } else {
        $permisos = $permiso_original;
    }

    // Si el usuario edita su propio registro, nunca permitir modificar el permiso
    if ($id_usuario == $id_usuario_logueado) {
        $permisos = $permiso_original;
    }

    // Unificar permisos de "crear" y "modificar"
    if (($accion == 'modificar' && $permisos_usuario_actual == 'modificar') || ($accion == 'modificar' && $permisos_usuario_actual == 'crear')) {
        $query = "UPDATE usuarios SET nombre = ?, apellido = ?, dni = ?, email = ?, fecha_nacimiento = ?, telefono = ?, puesto = ?, permisos = ?, usuario = ?";
        if (!empty($password)) {
            $query .= ", password = ?";
        }
        $query .= " WHERE id_usuario = ?";

        $stmt_update = $conexion->prepare($query);
        
        if (!empty($password)) {
            $tipos = "ssssssssssi";
            $params = array($nombre, $apellido, $dni, $email, $fecha_nacimiento, $telefono, $puesto, $permisos, $usuario, $password, $id_usuario);
        } else {
            $tipos = "sssssssssi";
            $params = array($nombre, $apellido, $dni, $email, $fecha_nacimiento, $telefono, $puesto, $permisos, $usuario, $id_usuario);
        }
        
        $stmt_update->bind_param($tipos, ...$params);
        
        if ($stmt_update->execute()) {
            $message_usuario = "Usuario modificado con éxito.";
            error_log("UPDATE exitoso.");
        } else {
            $message_usuario = "Error al modificar el usuario: " . $stmt_update->error;
            error_log("Error en UPDATE: " . $stmt_update->error);
        }
    } elseif ($accion == 'eliminar' && ($permisos_usuario_actual == 'modificar' || $permisos_usuario_actual == 'crear')) {
        // Verificar si el usuario a eliminar es el gerente
        $query_verificar_gerente = "SELECT email FROM usuarios WHERE id_usuario = ?";
        $stmt_verificar_gerente = $conexion->prepare($query_verificar_gerente);
        $stmt_verificar_gerente->bind_param("i", $id_usuario);
        $stmt_verificar_gerente->execute();
        $result_verificar_gerente = $stmt_verificar_gerente->get_result();

        if ($result_verificar_gerente && $result_verificar_gerente->num_rows > 0) {
            $email_usuario = $result_verificar_gerente->fetch_assoc()['email'];
            if ($email_usuario === 'durandamian523@gmail.com') {
                $message_usuario = "No se puede eliminar al gerente.";
                echo "<script>showModalQ('$message_usuario', true, null, 'Acción no permitida');</script>";
                exit();
            }
        }

        $query = "DELETE FROM usuarios WHERE id_usuario = ?";
        $stmt_delete = $conexion->prepare($query);
        $stmt_delete->bind_param("i", $id_usuario);

        if ($stmt_delete->execute()) {
            $message_usuario = "<span style='color: #2ecc40; font-weight: bold;'>Usuario eliminado con éxito.</span>";
        } else {
            $message_usuario = "Error al eliminar el usuario: " . $stmt_delete->error;
            echo "<script>showModalQ('$message_usuario', true, null, 'Error', 'error');</script>";
        }
    } else {
        $message_usuario = "No tienes permisos para realizar esta acción.";
    }
    
}

// Lógica de paginación
$usuarios_por_pagina = 4;
$pagina_actual = isset($_GET['pagina']) ? intval($_GET['pagina']) : 1;
$offset = ($pagina_actual - 1) * $usuarios_por_pagina;

// Obtener el total de usuarios
$query_total_usuarios = "SELECT COUNT(*) as total FROM usuarios";
$result_total_usuarios = mysqli_query($conexion, $query_total_usuarios);
$total_usuarios = mysqli_fetch_assoc($result_total_usuarios)['total'];
$total_paginas = ceil($total_usuarios / $usuarios_por_pagina);

// Obtener los usuarios para la página actual
$query = "SELECT id_usuario, nombre, apellido, dni, email, fecha_nacimiento, telefono, puesto, permisos, usuario FROM usuarios LIMIT $offset, $usuarios_por_pagina";
$result = mysqli_query($conexion, $query);

if (!$result) {
    die("Error en la consulta: " . mysqli_error($conexion));
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios</title>
    <link rel="stylesheet" href="gestioncliente.css">
    <link rel="stylesheet" href="/modal-q.css">
    <script src="/modal-q.js"></script>
    <script>
    function confirmAction(message) {
        return confirm(message);
    }

    function rellenarFormularioDesdeJson(usuario) {
        console.log('Datos del usuario:', usuario); // Para debug
        document.getElementById('form-gestionusuario').style.display = 'block';
        document.getElementById('titulo-gestionusuario').style.display = 'block';
        document.getElementById('accion').value = 'modificar';
        document.getElementById('id_usuario').value = usuario.id_usuario;
        document.getElementById('nombre').value = usuario.nombre || '';
        document.getElementById('apellido').value = usuario.apellido || '';
        document.getElementById('dni').value = usuario.dni || '';
        document.getElementById('email').value = usuario.email || '';
        document.getElementById('fecha_nacimiento').value = usuario.fecha_nacimiento || '';
        document.getElementById('telefono').value = usuario.telefono || '';
        document.getElementById('puesto').value = usuario.puesto || '';
        document.getElementById('permisos').value = usuario.permisos || '';
        document.getElementById('usuario').value = usuario.usuario || '';
        
        // Bloquear el campo permisos si el usuario edita su propio usuario
        if (usuario.id_usuario == '<?php echo $id_usuario_logueado; ?>') {
            document.getElementById('permisos').setAttribute('disabled', 'disabled');
        } else {
            document.getElementById('permisos').removeAttribute('disabled');
        }
        window.scrollTo({top: document.getElementById('form-gestionusuario').offsetTop - 40, behavior: 'smooth'});
    }

    function confirmDeleteUsuario(idUsuario) {
        showModalQ(
            '¿Estás seguro de que deseas eliminar este usuario?',
            false,
            null,
            'Confirmar Eliminación'
        );
        // Guardar el id temporalmente para usarlo en la acción de sí
        window.usuarioAEliminar = idUsuario;
        // Cambiar el botón OK del modal por Sí/No
        setTimeout(() => {
            const modal = document.getElementById('modal-q');
            const content = modal.querySelector('.modal-content');
            let btns = content.querySelectorAll('button');
            btns.forEach(btn => btn.remove());
            // Botón Sí
            const btnSi = document.createElement('button');
            btnSi.textContent = 'Sí';
            btnSi.onclick = function() {
                // Crear y enviar el formulario de eliminación
                const form = document.createElement('form');
                form.method = 'post';
                form.action = 'gestionusuario.php';
                form.style.display = 'none';
                const inputAccion = document.createElement('input');
                inputAccion.type = 'hidden';
                inputAccion.name = 'accion';
                inputAccion.value = 'eliminar';
                form.appendChild(inputAccion);
                const inputId = document.createElement('input');
                inputId.type = 'hidden';
                inputId.name = 'id_usuario';
                inputId.value = window.usuarioAEliminar;
                form.appendChild(inputId);
                document.body.appendChild(form);
                form.submit();
            };
            // Botón No
            const btnNo = document.createElement('button');
            btnNo.textContent = 'No';
            btnNo.onclick = closeModalQ;
            content.appendChild(btnSi);
            content.appendChild(btnNo);
        }, 100);
    }

    function confirmFormAction(event) {
        event.preventDefault();
        showModalQ(
            '¿Estás seguro de que deseas realizar esta acción?',
            false,
            null,
            'Confirmar Acción'
        );
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
                document.getElementById('form-gestionusuario').submit();
            };
            // Botón No
            const btnNo = document.createElement('button');
            btnNo.textContent = 'No';
            btnNo.onclick = closeModalQ;
            content.appendChild(btnSi);
            content.appendChild(btnNo);
        }, 100);
        return false;
    }

    function ocultarFormularioGestionUsuario() {
        document.getElementById('form-gestionusuario').reset();
        document.getElementById('form-gestionusuario').style.display = 'none';
        document.getElementById('titulo-gestionusuario').style.display = 'none';
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
            <?php if ($permisos_usuario_actual == 'crear') { ?>
                <a href="formulario.php">Crear Usuario</a>
            <?php } ?>
            <a href="../usuario.php">Volver</a>
        </nav>
    </div>
</header>

<section id="user-management">
    <h2>Lista de Usuarios</h2>
    <table>
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Apellido</th>
                <th>DNI</th>
                <th>Email</th>
                <th>Fecha de Nacimiento</th>
                <th>Teléfono</th>
                <th>Puesto</th>
                <th>Permisos</th>
                <th>Usuario</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($usuario = mysqli_fetch_assoc($result)) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($usuario['nombre']); ?></td>
                    <td><?php echo htmlspecialchars($usuario['apellido']); ?></td>
                    <td><?php echo htmlspecialchars($usuario['dni']); ?></td>
                    <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                    <td><?php echo htmlspecialchars($usuario['fecha_nacimiento']); ?></td>
                    <td><?php echo htmlspecialchars($usuario['telefono']); ?></td>
                    <td><?php echo htmlspecialchars($usuario['puesto']); ?></td>
                    <td><?php echo htmlspecialchars($usuario['permisos']); ?></td>
                    <td><?php echo htmlspecialchars($usuario['usuario']); ?></td>
                    <td>
                        <?php 
                        if ($usuario['email'] !== 'durandamian523@gmail.com') { 
                            $usuario_data = array_map('htmlspecialchars', $usuario);
                            $usuario_json = json_encode($usuario_data);
                            // Ocultar botón eliminar si es el usuario logueado
                            $es_usuario_logueado = ($usuario['id_usuario'] == $id_usuario_logueado);
                        ?>
                            <button type="button" onclick='rellenarFormularioDesdeJson(<?php echo $usuario_json; ?>)'>Modificar</button>
                            <?php if (!$es_usuario_logueado) { ?>
                                <button type="button" onclick="confirmDeleteUsuario('<?php echo $usuario['id_usuario']; ?>')">Eliminar</button>
                            <?php } ?>
                        <?php } ?>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

    <!-- Mostrar botones de paginación -->
    <?php if ($total_paginas > 1) { ?>
        <div class="pagination" style="display: flex; justify-content: center; margin-top: 20px;">
            <?php for ($i = 1; $i <= $total_paginas; $i++) { ?>
                <a href="gestionusuario.php?pagina=<?php echo $i; ?>" style="margin: 0 5px; padding: 10px 15px; border: 1px solid #ccc; border-radius: 5px; text-decoration: none; color: #333; background-color: <?php echo ($i == $pagina_actual ? '#4CAF50' : '#fff'); ?>;">
                    <?php echo $i; ?>
                </a>
            <?php } ?>
        </div>
    <?php } ?>

    <h1 id="titulo-gestionusuario" style="display:none;">Gestión de Usuarios</h1>
    <form action="gestionusuario.php" method="post" id="form-gestionusuario" style="display:none;">
        <input type="hidden" name="accion" id="accion" value="">
        <input type="hidden" name="id_usuario" id="id_usuario" value="">
        <input type="hidden" name="permisos_original" id="permisos_original" value="">
        <div class="form-group">
            <label for="nombre">Nombre:</label>
            <input type="text" id="nombre" name="nombre" required>
        </div>
        <div class="form-group">
            <label for="apellido">Apellido:</label>
            <input type="text" id="apellido" name="apellido" required>
        </div>
        <div class="form-group">
            <label for="dni">DNI:</label>
            <input type="text" id="dni" name="dni" required>
        </div>
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
        </div>
        <div class="form-group">
            <label for="fecha_nacimiento">Fecha de Nacimiento:</label>
            <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" required>
        </div>
        <div class="form-group">
            <label for="telefono">Teléfono:</label>
            <input type="text" id="telefono" name="telefono" required>
        </div>
        <div class="form-group">
            <label for="puesto">Puesto:</label>
            <input type="text" id="puesto" name="puesto" required>
        </div>
        <div class="form-group">
            <label for="permisos">Permisos:</label>
            <select id="permisos" name="permisos" required>
                <option value="crear">Crear</option>
                <option value="modificar">Modificar</option>
            </select>
        </div>
        <div class="form-group">
            <label for="usuario">Usuario:</label>
            <input type="text" id="usuario" name="usuario" required>
        </div>
        <div class="form-group">
            <label for="password">Contraseña:</label>
            <input type="password" id="password" name="password">
        </div>
        <button type="submit" onclick="return confirmFormAction(event)">Guardar</button>
        <button type="button" onclick="ocultarFormularioGestionUsuario()">Cancelar</button>
    </form>
</section>

<!-- Modal Q para mensajes -->
<div id="modal-q" style="display:none;position:fixed;z-index:9999;left:0;top:0;width:100vw;height:100vh;background:rgba(0,0,0,0.6);justify-content:center;align-items:center;">
  <div class="modal-content" style="background:#fff;color:#181828;border-radius:24px;padding:40px 30px 30px 30px;text-align:center;min-width:320px;max-width:90vw;box-shadow:0 8px 32px rgba(0,0,0,0.25);transition:border 0.2s, color 0.2s;">
    <h2 id="modal-q-title"></h2>
    <p id="modal-q-msg"></p>
    <button onclick="closeModalQ()">OK</button>
  </div>
</div>

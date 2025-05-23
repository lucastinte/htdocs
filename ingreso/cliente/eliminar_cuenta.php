<?php
include('../../db.php');
session_start();

$mensaje = '';
$exito = false;

if (!isset($_SESSION['usuario'])) {
    header("Location: ingreso.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = $_SESSION['usuario'];
    $password = $_POST['password'];

    // Verificar la contraseña
    $consulta_cliente = "SELECT * FROM clientes WHERE email='$usuario'";
    $resultado_cliente = mysqli_query($conexion, $consulta_cliente);
    $cliente = mysqli_fetch_assoc($resultado_cliente);

    if ($cliente && password_verify($password, $cliente['password'])) {
     // En lugar de eliminar:
// $sql = "DELETE FROM clientes WHERE email='$usuario'";

// Actualizar el estado a inactivo:
$sql = "UPDATE clientes SET activo = 0 WHERE email='$usuario'";
        if (mysqli_query($conexion, $sql)) {
            session_unset();
            session_destroy();
            $mensaje = 'Cuenta eliminada exitosamente.';
            $exito = true;
        } else {
            $mensaje = 'Error al eliminar la cuenta. Inténtelo de nuevo.';
        }
    } else {
        $mensaje = 'Contraseña incorrecta.';
    }

    mysqli_close($conexion);

    if ($exito) {
        echo '<script>window.onload = function() { showModalQ("' . addslashes($mensaje) . '", false, null, "Cuenta eliminada"); setTimeout(function(){ window.location.href = "/index.php"; }, 2000); };</script>';
    } else {
        echo '<script>window.onload = function() { showModalQ("' . addslashes($mensaje) . '", true, null, "Error"); setTimeout(function(){ window.location.href = "eliminar_cuenta.php"; }, 2000); };</script>';
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eliminar Cuenta</title>
    <link rel="stylesheet" href="clienteform.css">
    <link rel="stylesheet" href="/ingreso/usuario/talentos-modal-q.css">
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

    <section id="delete-account">
        <h1>Eliminar Cuenta</h1>
        <form id="form-eliminar-cuenta" method="post" action="eliminar_cuenta.php" onsubmit="return confirmarEliminarCuenta(event);">
            <div class="form-group">
                <label for="password">Contraseña Actual:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Eliminar Cuenta</button>
        </form>
        <a href="cliente.php" class="back-button">Volver a Gestión de Cliente</a>
    </section>
<!-- Modal Q reutilizable -->
<div id="modal-q" style="display:none;position:fixed;z-index:9999;left:0;top:0;width:100vw;height:100vh;background:rgba(20,20,30,0.85);justify-content:center;align-items:center;">
  <div class="modal-content modal-content-talentos">
    <h2 id="modal-q-title"></h2>
    <p id="modal-q-msg"></p>
    <button onclick="closeModalQ()">OK</button>
  </div>
</div>
<script src="/modal-q.js"></script>
<script>
function confirmarEliminarCuenta(e) {
    e.preventDefault();
    // Aquí podrías validar la contraseña antes de mostrar el modal si lo deseas
    showModalQ('¿Seguro que quieres eliminar tu cuenta?', false, null, 'Confirmar Eliminación');
    setTimeout(() => {
        const modal = document.getElementById('modal-q');
        const content = modal.querySelector('.modal-content-talentos');
        let btns = content.querySelectorAll('button');
        btns.forEach(btn => btn.remove());
        // Botón Sí
        const btnSi = document.createElement('button');
        btnSi.textContent = 'Sí';
        btnSi.onclick = function() {
            closeModalQ();
            document.getElementById('form-eliminar-cuenta').submit();
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
<?php
if (isset($eliminacion_exitosa) && $eliminacion_exitosa) {
    echo '<script>';
    echo 'showModalQ("Su cuenta fue eliminada exitosamente.", false, null, "Cuenta eliminada", "success");';
    echo 'setTimeout(function() {';
    echo '  const modal = document.getElementById("modal-q");';
    echo '  const content = modal.querySelector(".modal-content-talentos");';
    echo '  let btns = content.querySelectorAll("button");';
    echo '  btns.forEach(btn => btn.remove());';
    echo '  const btnOk = document.createElement("button");';
    echo '  btnOk.textContent = "OK";';
    echo '  btnOk.onclick = function() {';
    echo '    closeModalQ(); window.location.href = "/ingreso/ingreso.php";';
    echo '  };';
    echo '  content.appendChild(btnOk);';
    echo '}, 100);';
    echo '</script>';
}
?>
</body>
</html>

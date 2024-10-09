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
        // Eliminar la cuenta del usuario
        $sql = "DELETE FROM clientes WHERE email='$usuario'";

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
        echo "<script>
            alert('$mensaje');
            window.location.href = '/index.php';
        </script>";
    } else {
        echo "<script>
            alert('$mensaje');
            window.location.href = 'eliminar_cuenta.php';
        </script>";
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
        <form action="eliminar_cuenta.php" method="post">
            <div class="form-group">
                <label for="password">Contraseña Actual:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Eliminar Cuenta</button>
        </form>
        <a href="cliente.php" class="back-button">Volver a Gestión de Cliente</a>
    </section>
</body>

</html>

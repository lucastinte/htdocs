<?php
include('../db.php');
session_start();

// Manejar el envío del formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = $_POST['usuario'];
    $password = $_POST['password']; // Cambié 'contraseña' por 'password'

    // Verificar en la tabla 'clientes'
    $consulta_cliente = "SELECT * FROM clientes WHERE email = ?";
    $stmt_cliente = $conexion->prepare($consulta_cliente);
    $stmt_cliente->bind_param("s", $usuario);
    $stmt_cliente->execute();
    $resultado_cliente = $stmt_cliente->get_result();
    $cliente = $resultado_cliente->fetch_assoc();

    if ($cliente && password_verify($password, $cliente['password'])) { // Verificar contraseña hasheada
        // Si la contraseña es correcta para clientes, redirige a la vista de clientes
        $_SESSION['usuario'] = $usuario;
        header("Location: cliente/cliente.php");
        exit();
    } else {
        // Verificar en la tabla 'usuarios' para usuarios con contraseñas en texto plano
        $consulta_usuario = "SELECT * FROM usuarios WHERE usuario = ? AND password = ?";
        $stmt_usuario = $conexion->prepare($consulta_usuario);
        $stmt_usuario->bind_param("ss", $usuario, $password);
        $stmt_usuario->execute();
        $resultado_usuario = $stmt_usuario->get_result();

        if ($resultado_usuario->num_rows > 0) {
            // Si hay coincidencias en 'usuarios', redirige a la vista de usuarios
            $_SESSION['usuario'] = $usuario;
            header("Location: usuario/usuario.php");
            exit();
        } else {
            // Si no hay coincidencias en ninguna tabla
            echo "<script>
                alert('Error en el ingreso. Verifica tus credenciales e intenta de nuevo.');
                window.location.href = 'ingreso.php';
            </script>";
        }

        $stmt_usuario->close();
    }

    $stmt_cliente->close();
    $conexion->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio de Sesión</title>
    <link rel="stylesheet" href="../index.css">
</head>
<body>
    <div class="container">
        <form action="ingreso.php" method="post" class="login-form">
            <h1>INGRESAR</h1>
            <hr>
            <div class="form-group">
                <label for="usuario">Usuario o Email:</label>
                <input type="text" id="usuario" name="usuario" placeholder="Ingrese su nombre" required>
            </div>
            <div class="form-group">
                <label for="password">Contraseña:</label> <!-- Cambié 'contraseña' por 'password' -->
                <input type="password" id="password" name="password" placeholder="Ingrese su contraseña" required> <!-- Cambié 'contraseña' por 'password' -->
            </div>
            <hr>
            <button type="submit">Ingresar</button>
        </form>
        <p></p>
        <a href="../index.php"><button>Volver</button></a>
    </div>
</body>
</html>

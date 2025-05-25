<?php
include('../db.php');
include('../header.php'); 
session_start();

// Manejar el envío del formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = $_POST['usuario'];
    $password = $_POST['password']; // 

    // Verificar en la tabla 'clientes'
    $consulta_cliente = "SELECT * FROM clientes WHERE email = ?";
    $stmt_cliente = $conexion->prepare($consulta_cliente);
    $stmt_cliente->bind_param("s", $usuario);
    $stmt_cliente->execute();
    $resultado_cliente = $stmt_cliente->get_result();
    $cliente = $resultado_cliente->fetch_assoc();

    if ($cliente && password_verify($password, $cliente['password'])) { // Verificar contraseña hasheada
        // Si la contraseña es correcta para clientes, redirige a la vista de clientes
        if ($cliente['activo'] == 1) {
            $_SESSION['usuario'] = $usuario;
            header("Location: cliente/cliente.php");
            exit();
        } else {
            // Mostrar un mensaje si el cliente está inactivo
            echo "<script>window.onload = function(){ showAndRedirect('Tu cuenta está desactivada. Comunícate con el soporte para más información.', true, 'Cuenta Inactiva'); };</script>";
            echo "<script>setTimeout(function(){ window.location.href = 'ingreso.php'; }, 2000);</script>";
        }
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
            echo "<script>window.onload = function(){ showAndRedirect('Error en el ingreso. Verifica tus credenciales e intenta de nuevo.', true, 'Error de Ingreso'); };</script>";
            echo "<script>setTimeout(function(){ window.location.href = 'ingreso.php'; }, 2000);</script>";
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
    <style>
        body {
            position: relative;
            background-image: url("../imagen/portada/p10.jpg");
            background-size: cover;
            background-repeat: no-repeat;
            height: 100vh;
            margin: 0;
            display: flex;
            flex-direction: column;
        }

        body::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.7);
            z-index: -1;
        }

        header {
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
        }

        .form-container {
            width: 100%;
            max-width: 400px;
            background-color: rgba(255, 255, 255, 0.9);
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin: auto;
            margin-top: 120px; /* Ensure the form is below the header */
        }

        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
        }

        input[type="text"],
        input[type="password"] {
            width: calc(100% - 20px);
            padding: 10px;
            margin: 5px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }

        button {
            background-color: blueviolet;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
        }

        button:hover {
            background-color: rgb(101, 33, 165);
        }

        button:active {
            background-color: rgb(129, 9, 241);
        }

        hr {
            background-color: #242323;
            height: 1px;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <form action="ingreso.php" method="post" class="login-form">
            <h1>INGRESAR</h1>
            <hr>
            <div class="form-group">
                <label for="usuario">Usuario o Email (si es cliente):</label>
                <input type="text" id="usuario" name="usuario" placeholder="Ingrese su nombre" required>
            </div>
            <div class="form-group">
                <label for="password">Contraseña:</label>
                <input type="password" id="password" name="password" placeholder="Ingrese su contraseña" required>
            </div>
            <div style="text-align:right;margin-bottom:10px;">
                <a href="recuperar_contrasena.php" target="_blank" style="font-size:0.98em;color:#5a2ea6;text-decoration:inherit">¿Olvidaste tu contraseña?</a>
            </div>
            <hr>
            <button type="submit">Ingresar</button>
        </form>
        <p></p>
        <a href="../index.php"><button>Volver</button></a>
    </div>
    <!-- Modal Q reutilizable -->
    <div id="modal-q" style="display:none">
      <div class="modal-content">
        <h2 id="modal-q-title"></h2>
        <p id="modal-q-msg"></p>
        <button onclick="closeModalQ()">OK</button>
      </div>
    </div>
    <link rel="stylesheet" href="../modal-q.css">
    <script src="../modal-q.js"></script>
    <script>
    function showAndRedirect(msg, isError, title) {
      showModalQ(msg, isError, null, title);
      setTimeout(function(){ window.location.href = 'ingreso.php'; }, 2000);
    }
    </script>
</body>
</html>

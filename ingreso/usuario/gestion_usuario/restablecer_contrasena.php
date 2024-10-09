<?php
include('../../../db.php');
session_start();
$config = include('../../../config.php');

// Access base_url from the config array
$base_url = $config['base_url']; 

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        // Validar que las contraseñas coincidan
        if ($new_password !== $confirm_password) {
            $_SESSION['mensaje'] = 'Las contraseñas no coinciden.';
            $_SESSION['exito'] = false;
            header("Location: http://{$base_url}/ingreso/usuario/gestion_usuario/restablecer_contrasena.php?token=" . $token);
            exit();
        }

        // Preparar la consulta SQL para actualizar la contraseña
        $update_stmt = $conexion->prepare("UPDATE usuarios SET password = ? WHERE token = ?");
        // Usar la contraseña sin hashear en la consulta
        $update_stmt->bind_param("ss", $new_password, $token); 

        if ($update_stmt->execute()) {
            $_SESSION['mensaje'] = 'Contraseña restablecida con éxito.';
            $_SESSION['exito'] = true;
            header("Location: http://{$base_url}/ingreso/ingreso.php"); 
            exit();
        } else {
            $_SESSION['mensaje'] = 'Error al restablecer la contraseña.';
            $_SESSION['exito'] = false;
            header("Location: http://{$base_url}/ingreso/usuario/gestion_usuario/restablecer_contrasena.php?token=" . $token);
            exit();
        }

        $update_stmt->close();
    } 
} else {
    $_SESSION['mensaje'] = 'Token no proporcionado.';
    $_SESSION['exito'] = false;
    // Use base_url instead of localhost
    header("Location: http://{$base_url}/ingreso/ingreso.php"); 
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña</title>
    <link rel="stylesheet" href="clienteform.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        header {
            background-color: #333;
            color: #fff;
            padding: 10px 0;
            text-align: center;
        }

        .container {
            max-width: 600px; /* Ajustar el ancho máximo del contenedor */
            margin: 0 auto;
            padding: 20px;
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
        }

        .form-group input {
            width: 100%;
            max-width: 100%; /* Asegura que el input no exceda el ancho del contenedor */
            padding: 8px;
            box-sizing: border-box;
        }

        button {
            background-color: #28a745;
            color: #fff;
            border: none;
            padding: 10px 15px;
            cursor: pointer;
            border-radius: 5px;
            display: block;
            width: 100%;
            max-width: 150px; /* Ajustar el ancho máximo del botón */
            margin: 20px auto; /* Centrar el botón */
        }

        button:hover {
            background-color: #218838;
        }

        .alert {
            text-align: center;
            padding: 10px;
            margin: 10px auto;
            width: 80%;
            max-width: 500px; /* Ajustar el ancho máximo de las alertas */
            border-radius: 5px;
            font-size: 16px;
        }

        .alert.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <p class="logo">Mat Construcciones</p>
        </div>
    </header>

    <section id="reset-password">
        <div class="container">
            <h1>Restablecer Contraseña</h1>
            <?php
            if (isset($_SESSION['mensaje'])) {
                echo '<div class="alert ' . ($_SESSION['exito'] ? 'success' : 'error') . '">';
                echo htmlspecialchars($_SESSION['mensaje']);
                echo '</div>';

                // Limpiar los mensajes después de mostrarlos
                unset($_SESSION['mensaje']);
                unset($_SESSION['exito']);
            }
            ?>
            <form action="restablecer_contrasena.php?token=<?php echo htmlspecialchars($token); ?>" method="post">
                <div class="form-group">
                    <label for="new_password">Nueva Contraseña:</label>
                    <input type="password" id="new_password" name="new_password" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Repetir Contraseña:</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                <button type="submit">Establecer Contraseña</button>
            </form>
        </div>
    </section>
</body>
</html>

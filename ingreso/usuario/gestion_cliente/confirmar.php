<?php
include('../../../db.php');
session_start();

// Verificar si la conexión se ha establecido correctamente
if (!$conexion) {
    die("Error de conexión: " . mysqli_connect_error());
}

// Verificar si el token está presente en la URL
if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Consultar la base de datos para encontrar el token
    $stmt = $conexion->prepare("SELECT email FROM clientes WHERE token = ?");
    if ($stmt === false) {
        die("Error en la preparación de la consulta: " . $conexion->error);
    }

    $stmt->bind_param("s", $token);
    $stmt->execute();
    $stmt->store_result();

    // Verificar si se encontró el token
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($email);
        $stmt->fetch();
    } else {
        die("Token no válido o expirado.");
    }

    $stmt->close();
} else {
    die("Token no proporcionado.");
}

// Manejar el envío del formulario de establecimiento de contraseña
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Verificar que las contraseñas coincidan
    if ($password === $confirm_password) {
        // Hashear la nueva contraseña
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        // Actualizar la base de datos con la nueva contraseña y eliminar el token
        $stmt = $conexion->prepare("UPDATE clientes SET password = ?, token = NULL WHERE email = ?");
        if ($stmt === false) {
            die("Error en la preparación de la consulta: " . $conexion->error);
        }

        $stmt->bind_param("ss", $hashed_password, $email);
        if ($stmt->execute()) {
            echo "<script>
                alert('Contraseña establecida exitosamente.');
                window.location.href = '/';
            </script>";
        } else {
            echo "Error al actualizar la contraseña: " . $stmt->error;
        }

        $stmt->close();
        $conexion->close();
        exit();
    } else {
        echo "<script>alert('Las contraseñas no coinciden.');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Establecer Contraseña</title>
    <link rel="stylesheet" href="../../../../index.css">
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
</head>

<body>
    <main style="padding: 20px;">
        <form action="confirmar.php?token=<?php echo htmlspecialchars($token); ?>" method="post">
            <h3 style="text-align: center;">Establecer Contraseña</h3>

            <label for="password">Nueva Contraseña:</label>
            <input type="password" id="password" name="password" required><br><br>

            <label for="confirm_password">Confirmar Contraseña:</label>
            <input type="password" id="confirm_password" name="confirm_password" required><br><br>

            <input type="submit" value="Establecer Contraseña">
        </form>
    </main>
    <button style="width: 100px; margin: 20px auto ; padding: 10px;">
        <a href="./gestioncliente.html" style="text-decoration: none;color: aliceblue;">Volver</a>
    </button>
</body>

</html>

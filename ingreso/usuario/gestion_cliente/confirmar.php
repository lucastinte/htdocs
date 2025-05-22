<?php
include('../../../db.php');
session_start();

$token_error = '';
$contraseña_error = '';
$exito = false;

// Verificar si la conexión se ha establecido correctamente
if (!$conexion) {
    die("Error de conexión: " . mysqli_connect_error());
}

// Verificar si el token está presente en la URL
echo '<link rel="stylesheet" href="/modal-q.css">';
echo '<script src="/modal-q.js"></script>';

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Consultar la base de datos para encontrar el token
    $stmt = $conexion->prepare("SELECT email FROM clientes WHERE token = ?");
    if ($stmt === false) {
        $token_error = "Error en la preparación de la consulta.";
    } else {
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $stmt->store_result();

        // Verificar si se encontró el token
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($email);
            $stmt->fetch();
        } else {
            $token_error = "Token no válido o expirado.";
        }
        $stmt->close();
    }
} else {
    $token_error = "Token no proporcionado.";
}

// Si hay error de token, mostrar modal y redirigir al login
if ($token_error) {
    echo '<div id="modal-q" style="display:none;position:fixed;z-index:9999;left:0;top:0;width:100vw;height:100vh;background:rgba(0,0,0,0.6);justify-content:center;align-items:center;">';
    echo '<div class="modal-content">';
    echo '<h2 id="modal-q-title"></h2>';
    echo '<p id="modal-q-msg"></p>';
    echo '<button id="modal-q-ok-btn" onclick="closeModalQ(); window.location.href=\'/ingreso/ingreso.php\';">OK</button>';
    echo '</div></div>';
    echo "<script>showModalQ('" . addslashes($token_error) . "', true, null, 'Token inválido', 'error');</script>";
    exit();
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
            echo "<script>showModalQ('Error en la preparación de la consulta.', true, null, 'Error', 'error');</script>";
            exit();
        } else {
            $stmt->bind_param("ss", $hashed_password, $email);
            if ($stmt->execute()) {
                // Iniciar sesión automáticamente
                $_SESSION['usuario'] = $email;
                $_SESSION['tipo_usuario'] = 'cliente';
                // Imprimir solo el modal y el script, sin HTML previo
                echo '<!DOCTYPE html><html lang="es"><head>';
                echo '<meta charset="UTF-8">';
                echo '<link rel="stylesheet" href="/modal-q.css">';
                echo '</head><body>';
                echo '<div id="modal-q" style="display:none;position:fixed;z-index:9999;left:0;top:0;width:100vw;height:100vh;background:rgba(0,0,0,0.6);justify-content:center;align-items:center;">';
                echo '<div class="modal-content">';
                echo '<h2 id="modal-q-title"></h2>';
                echo '<p id="modal-q-msg"></p>';
                echo '</div></div>';
                echo '<script src="/modal-q.js"></script>';
                echo '<script>';
                echo 'window.onload = function() {';
                echo "showModalQ('Contraseña establecida exitosamente. ¡Ingresa!', false, null, 'Éxito', 'success');";
                echo 'setTimeout(function() { window.location.href = "/ingreso/ingreso.php"; }, 3000);';
                echo '};';
                echo '</script>';
                echo '</body></html>';
                $stmt->close();
                $conexion->close();
                exit();
            } else {
                echo "<script>showModalQ('Error al actualizar la contraseña: " . addslashes($stmt->error) . "', true, null, 'Error', 'error');</script>";
                exit();
            }
        }
    } else {
        $contraseña_error = "Las contraseñas no coinciden.";
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
        .error-message {
            color: #d32f2f;
            font-size: 0.95em;
            margin-top: 2px;
            margin-bottom: 8px;
            min-height: 18px;
        }
    </style>
</head>

<body>
    <main style="padding: 20px;">
        <form action="confirmar.php?token=<?php echo htmlspecialchars($token); ?>" method="post" id="form-confirmar">
            <h3 style="text-align: center;">Establecer Contraseña</h3>

            <label for="password">Nueva Contraseña:</label>
            <input type="password" id="password" name="password" required><br><br>

            <label for="confirm_password">Confirmar Contraseña:</label>
            <input type="password" id="confirm_password" name="confirm_password" required>
            <div class="error-message" id="error-confirm">
                <?php if (!empty($contraseña_error)) echo $contraseña_error; ?>
            </div>
            <br>
            <input type="submit" value="Establecer Contraseña">
        </form>
    </main>
    <button style="width: 100px; margin: 20px auto ; padding: 10px;">
        <a href="./gestioncliente.php" style="text-decoration: none;color: aliceblue;">Volver</a>
    </button>
    <script>
    // Validación en vivo para contraseñas coincidentes
    const form = document.getElementById('form-confirmar');
    const pass1 = document.getElementById('password');
    const pass2 = document.getElementById('confirm_password');
    const errorDiv = document.getElementById('error-confirm');
    form.addEventListener('submit', function(e) {
        if (pass1.value !== pass2.value) {
            errorDiv.textContent = 'Las contraseñas no coinciden.';
            e.preventDefault();
        }
    });
    pass2.addEventListener('input', function() {
        if (pass1.value !== pass2.value) {
            errorDiv.textContent = 'Las contraseñas no coinciden.';
        } else {
            errorDiv.textContent = '';
        }
    });
    </script>
</body>

</html>

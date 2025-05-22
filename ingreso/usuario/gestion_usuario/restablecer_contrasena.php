<?php
include('../../../db.php');
session_start();
$config = include('../../../config.php');
$base_url = $config['base_url']; 

$token = isset($_GET['token']) ? $_GET['token'] : null;
$usuario_nombre = '';
$token_valido = false;
$contraseña_error = '';

if ($token) {
    // Verificar si el token existe y obtener el usuario
    $stmt = $conexion->prepare("SELECT usuario FROM usuarios WHERE token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $usuario_nombre = $row['usuario'];
        $token_valido = true;
    } else {
        // Token inválido
        echo "<html><head>"
            . '<link rel="stylesheet" href="/ingreso/usuario/gestion_usuario/clienteform.css">'
            . '<link rel="stylesheet" href="/modal-q.css">'
            . '<script src="/modal-q.js"></script>'
            . "</head><body>"
            . '<div id="modal-q" style="display:none;position:fixed;z-index:9999;left:0;top:0;width:100vw;height:100vh;background:rgba(0,0,0,0.6);justify-content:center;align-items:center;">'
            . '<div class="modal-content" style="background:#fff;color:#181828;border-radius:24px;padding:40px 30px 30px 30px;text-align:center;min-width:320px;max-width:90vw;box-shadow:0 8px 32px rgba(0,0,0,0.25);transition:border 0.2s, color 0.2s;">'
            . '<h2 id="modal-q-title"></h2>'
            . '<p id="modal-q-msg"></p>'
            . '<button onclick="closeModalQ(); window.location.href=\'/ingreso/ingreso.php\';">OK</button>'
            . '</div></div>'
            . "<script>showModalQ('Token no válido o expirado.', true, null, 'Token inválido', 'error');</script>"
            . "</body></html>";
        exit();
    }
    $stmt->close();

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        // Validar que las contraseñas coincidan
        if ($new_password !== $confirm_password) {
            $contraseña_error = 'Las contraseñas no coinciden.';
        } else {
            // Actualizar la contraseña (sin hashear)
            $update_stmt = $conexion->prepare("UPDATE usuarios SET password = ?, token = NULL WHERE token = ?");
            $update_stmt->bind_param("ss", $new_password, $token);

            if ($update_stmt->execute()) {
                echo "<html><head>"
                    . '<link rel="stylesheet" href="/ingreso/usuario/gestion_usuario/clienteform.css">'
                    . '<link rel="stylesheet" href="/modal-q.css">'
                    . '<script src="/modal-q.js"></script>'
                    . "</head><body>"
                    . '<div id="modal-q" style="display:none;position:fixed;z-index:9999;left:0;top:0;width:100vw;height:100vh;background:rgba(0,0,0,0.6);justify-content:center;align-items:center;">'
                    . '<div class="modal-content" style="background:#fff;color:#181828;border-radius:24px;padding:40px 30px 30px 30px;text-align:center;min-width:320px;max-width:90vw;box-shadow:0 8px 32px rgba(0,0,0,0.25);transition:border 0.2s, color 0.2s;">'
                    . '<h2 id="modal-q-title"></h2>'
                    . '<p id="modal-q-msg"></p>'
                    . '<button onclick="closeModalQ(); window.location.href=\'/ingreso/ingreso.php\';">OK</button>'
                    . '</div></div>'
                    . "<script>showModalQ('Se creó la contraseña con éxito. Vuelva a iniciar sesión.', false, null, 'Éxito', 'success');</script>"
                    . "</body></html>";
                exit();
            } else {
                echo "<html><head>"
                    . '<link rel="stylesheet" href="/ingreso/usuario/gestion_usuario/clienteform.css">'
                    . '<link rel="stylesheet" href="/modal-q.css">'
                    . '<script src="/modal-q.js"></script>'
                    . "</head><body>"
                    . '<div id="modal-q" style="display:none;position:fixed;z-index:9999;left:0;top:0;width:100vw;height:100vh;background:rgba(0,0,0,0.6);justify-content:center;align-items:center;">'
                    . '<div class="modal-content" style="background:#fff;color:#181828;border-radius:24px;padding:40px 30px 30px 30px;text-align:center;min-width:320px;max-width:90vw;box-shadow:0 8px 32px rgba(0,0,0,0.25);transition:border 0.2s, color 0.2s;">'
                    . '<h2 id="modal-q-title"></h2>'
                    . '<p id="modal-q-msg"></p>'
                    . '<button onclick="closeModalQ();">OK</button>'
                    . '</div></div>'
                    . "<script>showModalQ('Error al restablecer la contraseña.', true, null, 'Error', 'error');</script>"
                    . "</body></html>";
                exit();
            }
        }
    }
} else {
    // Token no proporcionado
    echo "<html><head>"
        . '<link rel="stylesheet" href="/ingreso/usuario/gestion_usuario/clienteform.css">'
        . '<link rel="stylesheet" href="/modal-q.css">'
        . '<script src="/modal-q.js"></script>'
        . "</head><body>"
        . '<div id="modal-q" style="display:none;position:fixed;z-index:9999;left:0;top:0;width:100vw;height:100vh;background:rgba(0,0,0,0.6);justify-content:center;align-items:center;">'
        . '<div class="modal-content" style="background:#fff;color:#181828;border-radius:24px;padding:40px 30px 30px 30px;text-align:center;min-width:320px;max-width:90vw;box-shadow:0 8px 32px rgba(0,0,0,0.25);transition:border 0.2s, color 0.2s;">'
        . '<h2 id="modal-q-title"></h2>'
        . '<p id="modal-q-msg"></p>'
        . '<button onclick="closeModalQ(); window.location.href=\'/ingreso/ingreso.php\';">OK</button>'
        . '</div></div>'
        . "<script>showModalQ('Token no proporcionado.', true, null, 'Token inválido', 'error');</script>"
        . "</body></html>";
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña</title>
    <link rel="stylesheet" href="/ingreso/cliente/clienteform.css">
    <link rel="stylesheet" href="/modal-q.css">
    <script src="/modal-q.js"></script>
</head>
<body>
    <header>
        <div class="container">
            <p class="logo">Mat Construcciones</p>
        </div>
    </header>

    <section class="container">
        <h1 style="margin-bottom: 20px;">Restablecer Contraseña</h1>
        <?php if ($token_valido) { ?>
            <div class="alert success" style="margin-bottom: 20px;">
                Se está creando contraseña para usuario: <b><?php echo htmlspecialchars($usuario_nombre); ?></b>
            </div>
            <form action="restablecer_contrasena.php?token=<?php echo htmlspecialchars($token); ?>" method="post" class="login-form">
                <div class="form-group">
                    <label for="new_password">Nueva Contraseña:</label>
                    <input type="password" id="new_password" name="new_password" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Repetir Contraseña:</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                    <?php if (!empty($contraseña_error)) { ?>
                        <span style="color: #d32f2f; font-size: 0.95em; display: block; margin-top: 4px;"> <?php echo $contraseña_error; ?> </span>
                    <?php } ?>
                </div>
                <button type="submit">Establecer Contraseña</button>
            </form>
        <?php } ?>
    </section>
</body>
</html>

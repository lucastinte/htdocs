<?php
// Función para obtener el puerto correcto
function get_server_port() {
    $port = $_SERVER['SERVER_PORT'];
    return $port == '80' ? '' : ':' . $port;
}

require_once '../../config.php';
require_once '../../db.php';

if (!isset($_GET['token'])) {
    $port = get_server_port();
    header("Location: http://{$_SERVER['SERVER_NAME']}{$port}/index.php");
    exit;
}

$token = $_GET['token'];
$error = '';
$success = '';

// Validar token antes de mostrar el sitio
$stmt = $conexion->prepare("SELECT id, nombre FROM clientes WHERE token = ?");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    // Token inválido o expirado
    header('Location: ../../index.php');
    exit;
}
$cliente_row = $result->fetch_assoc();
$nombre_cliente = $cliente_row['nombre'];
$cliente_id = $cliente_row['id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if ($new_password === $confirm_password) {
        // Verificar token en la tabla de clientes
        $stmt = $conexion->prepare("SELECT id, email FROM clientes WHERE token = ?");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $cliente = $result->fetch_assoc();
            
            // Hashear la contraseña para clientes
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            // Actualizar contraseña y limpiar token
            $update = $conexion->prepare("UPDATE clientes SET password = ?, token = NULL WHERE id = ?");
            $update->bind_param("si", $hashed_password, $cliente['id']);
            
            if ($update->execute()) {
                $success = "Contraseña actualizada correctamente";
            } else {
                $error = "Error al actualizar la contraseña: " . $conexion->error;
            }
        } else {
            $error = "Token inválido o expirado.";
        }
    } else {
        $error = "Las contraseñas no coinciden.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Restablecer Contraseña - Cliente</title>
    <link rel="stylesheet" href="../ingreso.css">
    <link rel="stylesheet" href="../../modal-q.css">
    <script src="../../modal-q.js"></script>
    <style>
        body {
            min-height: 100vh;
            margin: 0;
            padding: 0;
            background: #f7f7fa;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .login-container {
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.10);
            max-width: 400px;
            width: 100%;
            margin: 40px 0;
            padding: 40px 30px 30px 30px;
            text-align: center;
        }
        .login-container h2 {
            font-size: 2.2em;
            font-weight: 800;
            margin-bottom: 30px;
        }
        .nombre-cliente {
            font-size: 1.1em;
            color: #7c2ae8;
            font-weight: 600;
            margin-bottom: 18px;
        }
        .form-group {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 20px;
        }
        .form-group label {
            font-weight: 600;
            color: #222;
            margin-bottom: 8px;
            display: block;
            text-align: left;
            width: 100%;
            max-width: 340px;
        }
        .form-group input[type="password"] {
            width: 100%;
            max-width: 340px;
            padding: 12px;
            border-radius: 8px;
            border: 1.5px solid #ccc;
            margin-bottom: 18px;
            font-size: 1.1em;
            box-sizing: border-box;
            display: block;
        }
        .btn-primary {
            width: 100%;
            padding: 14px;
            background: #a259f7;
            color: #fff;
            font-weight: bold;
            border: none;
            border-radius: 8px;
            font-size: 1.2em;
            cursor: pointer;
            margin-top: 10px;
            transition: background 0.2s;
        }
        .btn-primary:hover {
            background: #7c2ae8;
        }
        @media (max-width: 600px) {
            .login-container {
                max-width: 95vw;
                padding: 25px 8vw;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="nombre-cliente">Recuperando contraseña de: <b><?php echo htmlspecialchars($nombre_cliente); ?></b></div>
        <h2>Restablecer Contraseña</h2>
        <?php $port = get_server_port(); ?>
        <form method="POST" action="http://<?php echo $_SERVER['SERVER_NAME'] . $port; ?>/ingreso/cliente/restablecer_contrasena.php?token=<?php echo htmlspecialchars($token); ?>" id="resetForm">
            <div class="form-group">
                <label>Nueva Contraseña:</label>
                <input type="password" name="new_password" required>
            </div>
            <div class="form-group">
                <label>Confirmar Contraseña:</label>
                <input type="password" name="confirm_password" required>
            </div>
            <button type="submit" class="btn-primary">Cambiar Contraseña</button>
        </form>
        <?php if ($error): ?>
            <div class="error-message" style="color: red; margin-top: 20px;"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div id="modal-q" class="modal-q" style="display: none;">
                <div class="modal-content">
                    <span class="close" onclick="closeModalQ()">&times;</span>
                    <h3 id="modal-q-title"></h3>
                    <p id="modal-q-msg"></p>
                </div>
            </div>
            <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Solo mostrar el modal si existe en el DOM
                if (document.getElementById('modal-q')) {
                    showModalQ('<?php echo $success; ?>', false, null, 'Éxito', 'success');
                    setTimeout(function() {
                        closeModalQ();
                        window.location.href = '../ingreso.php';
                    }, 2000);
                } else {
                    window.location.href = '../ingreso.php';
                }
            });
            </script>
        <?php endif; ?>
    </div>
</body>
</html>

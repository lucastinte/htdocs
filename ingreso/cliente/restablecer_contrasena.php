<?php
require_once '../../config.php';
require_once '../../db.php';

if (!isset($_GET['token'])) {
    header('Location: ../../index.php');
    exit;
}

$token = $_GET['token'];
$error = '';
$success = '';

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
</head>
<body>
    <div class="login-container">
        <h2>Restablecer Contraseña</h2>
        <form method="POST" id="resetForm">
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
    </div>

    <!-- Modal Q -->
    <div id="modal-q" class="modal-q" style="display: none;">
        <div class="modal-content">
            <span class="close" onclick="closeModalQ()">&times;</span>
            <h3 id="modal-q-title"></h3>
            <p id="modal-q-msg"></p>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        <?php if ($success): ?>
            showModalQ('<?php echo $success; ?>', false, 'resetForm', 'Éxito', 'success');
            setTimeout(function() {
                window.location.href = '../ingreso.php';
            }, 3000);
        <?php endif; ?>
        
        <?php if ($error): ?>
            showModalQ('<?php echo $error; ?>', true, null, 'Error', 'error');
        <?php endif; ?>
    });
    </script>
</body>
</html>

<?php
include('../../../db.php');
include('send_email.php');
session_start();

// Verificar si la conexión se ha establecido correctamente
if (!$conexion) {
    die("Error de conexión: " . mysqli_connect_error());
}

// Manejar el envío del formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $apellido = $_POST['apellido'];
    $nombre = $_POST['nombre'];
    $dni = $_POST['dni'];
    $caracteristica_tel = $_POST['caracteristica_tel'];
    $numero_tel = $_POST['numero_tel'];
    $email = $_POST['email'];
    $usuario = $_POST['usuario'];
    $direccion = $_POST['direccion'];
    $fecha_nacimiento = $_POST['fecha_nacimiento'];
    $token = bin2hex(random_bytes(16)); // Generar un token aleatorio
    $errores = [];
    // Validación del DNI
    if (!is_numeric($dni) || $dni < 5000000) {
        $errores['dni'] = 'El DNI debe ser un número mayor o igual a 5 millones.';
    }
    // Validación de la fecha de nacimiento
    $fecha_nacimiento_dt = new DateTime($fecha_nacimiento);
    $hoy = new DateTime();
    $edad = $hoy->diff($fecha_nacimiento_dt)->y;
    if ($edad < 18) {
        $errores['fecha_nacimiento'] = 'Debes tener al menos 18 años para registrarte.';
    }
    // Validación del teléfono
    $isCellular = false;
    if (preg_match('/^15\d{8}$/', $numero_tel)) {
        $isCellular = true;
    }
    if ($isCellular) {
        if (!preg_match('/^\d{3,5}$/', $caracteristica_tel) || !preg_match('/^15\d{8}$/', $numero_tel)) {
            $errores['numero_tel'] = 'Para números de celular, el número debe comenzar con 15 seguido de 8 dígitos.';
        }
    } else {
        if (!preg_match('/^0\d{2,4}$/', $caracteristica_tel) || !preg_match('/^\d{6,8}$/', $numero_tel)) {
            $errores['numero_tel'] = 'El teléfono fijo debe tener el prefijo 0 seguido de entre 2 y 4 dígitos de código de área, y entre 6 y 8 dígitos de número local.';
        }
    }
    // Verificar si el email o el DNI ya existen
    $checkQuery = "SELECT id FROM clientes WHERE email = ? OR dni = ?";
    $stmt = $conexion->prepare($checkQuery);
    $stmt->bind_param("ss", $email, $dni);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $errores['email'] = 'El correo electrónico o DNI ya están registrados. Por favor, utiliza otro.';
    }
    if (!empty($errores)) {
        // Guardar errores en sesión y recargar para mostrar en el formulario
        $_SESSION['alta_errores'] = $errores;
        $_SESSION['alta_data'] = $_POST;
        header('Location: alta.php');
        exit();
    }
    $stmt = $conexion->prepare("INSERT INTO clientes (apellido, nombre, dni, caracteristica_tel, numero_tel, email, usuario, direccion, fecha_nacimiento, token) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    if ($stmt === false) {
        die("Error en la preparación de la consulta: " . $conexion->error);
    }

    $formatted_fecha_nacimiento = $fecha_nacimiento_dt->format('Y-m-d');
    $stmt->bind_param("ssssssssss", $apellido, $nombre, $dni, $caracteristica_tel, $numero_tel, $email, $usuario, $direccion, $formatted_fecha_nacimiento, $token);
    if ($stmt->execute()) {
        // Enviar correo electrónico de confirmación
        sendConfirmationEmail($email, $token);
        $_SESSION['alta_exito'] = 'Registro exitoso. Por favor, revisa tu correo electrónico.';
        header('Location: alta.php');
        exit();
    } else {
        $_SESSION['alta_errores'] = ['general' => 'Error al registrar los datos: ' . $stmt->error];
        $_SESSION['alta_data'] = $_POST;
        header('Location: alta.php');
        exit();
    }

    $stmt->close();
    $conexion->close();
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Formulario de Registro</title>
    <link rel="stylesheet" href="gestioncliente.css">
    <style>
        body {
            background-image: linear-gradient(0deg, rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('/imagen/servicios/casas.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            min-height: 100vh;
        }
        .sf form {
            background-color: rgba(255,255,255,0.95);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
            margin: 40px auto;
        }
        h2, h3 {
            color: blueviolet;
            text-align: center;
        }
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
    <header>
        <div class="container">
            <p class="logo">Mat Construcciones</p>
            <nav>
                <a href="alta.php" class="btn-green">Crear Cliente</a>
                <a href="carga.php">Cargar Proyecto</a>
                <a href="proyectos.php">Ver Proyecto</a>
                <a href="gestioncliente.php">Volver</a>
            </nav>
        </div>
    </header>
    <main>
        <div class="sf">
            <form id="altaForm" action="alta.php" method="post" novalidate>
                <h3>Registro de Clientes</h3>
                <?php
                $errores = isset($_SESSION['alta_errores']) ? $_SESSION['alta_errores'] : [];
                $data = isset($_SESSION['alta_data']) ? $_SESSION['alta_data'] : [];
                $exito = isset($_SESSION['alta_exito']) ? $_SESSION['alta_exito'] : '';
                unset($_SESSION['alta_errores'], $_SESSION['alta_data'], $_SESSION['alta_exito']);
                ?>
                <!-- Mensajes generales ahora se muestran con showModalQ -->
                <div id="modal-q" style="display:none;position:fixed;z-index:9999;left:0;top:0;width:100vw;height:100vh;background:rgba(0,0,0,0.6);justify-content:center;align-items:center;">
                  <div class="modal-content">
                    <h2 id="modal-q-title"></h2>
                    <p id="modal-q-msg"></p>
                    <button onclick="closeModalQ()">OK</button>
                  </div>
                </div>
                <link rel="stylesheet" href="/modal-q.css">
                <script src="/modal-q.js"></script>
                <script>
                <?php if (!empty($exito)) { ?>
                  showModalQ('<?php echo addslashes($exito); ?>', false, null, 'Registro Exitoso', 'success');
                  document.addEventListener('DOMContentLoaded', function() {
                    const okBtn = document.querySelector('#modal-q button');
                    if (okBtn) {
                      okBtn.addEventListener('click', function() {
                        window.location.href = 'gestioncliente.php';
                      });
                    }
                  });
                <?php } elseif (isset($errores['general'])) { ?>
                  showModalQ('<?php echo addslashes($errores['general']); ?>', true, null, 'Error', 'error');
                <?php } ?>
                </script>
                <div class="form-group">
                    <label for="apellido">Apellido:</label>
                    <input type="text" id="apellido" name="apellido" required value="<?= isset($data['apellido']) ? htmlspecialchars($data['apellido']) : '' ?>">
                    <div class="error-message" id="error-apellido"><?= isset($errores['apellido']) ? $errores['apellido'] : '' ?></div>
                </div>
                <div class="form-group">
                    <label for="nombre">Nombre:</label>
                    <input type="text" id="nombre" name="nombre" required value="<?= isset($data['nombre']) ? htmlspecialchars($data['nombre']) : '' ?>">
                    <div class="error-message" id="error-nombre"><?= isset($errores['nombre']) ? $errores['nombre'] : '' ?></div>
                </div>
                <div class="form-group">
                    <label for="dni">DNI:</label>
                    <input type="text" id="dni" name="dni" required value="<?= isset($data['dni']) ? htmlspecialchars($data['dni']) : '' ?>">
                    <div class="error-message" id="error-dni"><?= isset($errores['dni']) ? $errores['dni'] : '' ?></div>
                </div>
                <div class="form-group">
                    <label for="caracteristica_tel">Código de Área (Argentina):</label>
                    <input type="text" id="caracteristica_tel" name="caracteristica_tel" required value="<?= isset($data['caracteristica_tel']) ? htmlspecialchars($data['caracteristica_tel']) : '' ?>">
                    <div class="error-message" id="error-caracteristica_tel"><?= isset($errores['caracteristica_tel']) ? $errores['caracteristica_tel'] : '' ?></div>
                </div>
                <div class="form-group">
                    <label for="numero_tel">Número de Teléfono:</label>
                    <input type="text" id="numero_tel" name="numero_tel" required value="<?= isset($data['numero_tel']) ? htmlspecialchars($data['numero_tel']) : '' ?>">
                    <div class="error-message" id="error-numero_tel"><?= isset($errores['numero_tel']) ? $errores['numero_tel'] : '' ?></div>
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required value="<?= isset($data['email']) ? htmlspecialchars($data['email']) : '' ?>">
                    <div class="error-message" id="error-email"><?= isset($errores['email']) ? $errores['email'] : '' ?></div>
                </div>
                <div class="form-group">
                    <label for="usuario">Nombre de Usuario:</label>
                    <input type="text" id="usuario" name="usuario" required value="<?= isset($data['usuario']) ? htmlspecialchars($data['usuario']) : '' ?>">
                    <div class="error-message" id="error-usuario"><?= isset($errores['usuario']) ? $errores['usuario'] : '' ?></div>
                </div>
                <div class="form-group">
                    <label for="direccion">Dirección:</label>
                    <input type="text" id="direccion" name="direccion" required value="<?= isset($data['direccion']) ? htmlspecialchars($data['direccion']) : '' ?>">
                    <div class="error-message" id="error-direccion"><?= isset($errores['direccion']) ? $errores['direccion'] : '' ?></div>
                </div>
                <div class="form-group">
                    <label for="fecha_nacimiento">Fecha de Nacimiento:</label>
                    <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" required value="<?= isset($data['fecha_nacimiento']) ? htmlspecialchars($data['fecha_nacimiento']) : '' ?>">
                    <div class="error-message" id="error-fecha_nacimiento"><?= isset($errores['fecha_nacimiento']) ? $errores['fecha_nacimiento'] : '' ?></div>
                </div>
                <button type="submit">Registrar</button>
            </form>
        </div>
    </main>
    <script>
    // Validaciones en vivo para cada campo
    const form = document.getElementById('altaForm');
    const fields = {
        apellido: {
            input: document.getElementById('apellido'),
            error: document.getElementById('error-apellido'),
            validate: v => v.trim() !== '' ? '' : 'El apellido es obligatorio.'
        },
        nombre: {
            input: document.getElementById('nombre'),
            error: document.getElementById('error-nombre'),
            validate: v => v.trim() !== '' ? '' : 'El nombre es obligatorio.'
        },
        dni: {
            input: document.getElementById('dni'),
            error: document.getElementById('error-dni'),
            validate: v => {
                if (!/^[0-9]+$/.test(v)) return 'El DNI debe ser numérico.';
                if (parseInt(v, 10) < 5000000) return 'El DNI debe ser mayor o igual a 5 millones.';
                return '';
            }
        },
        caracteristica_tel: {
            input: document.getElementById('caracteristica_tel'),
            error: document.getElementById('error-caracteristica_tel'),
            validate: v => {
                if (!/^0?\d{2,5}$/.test(v)) return 'Debe tener entre 2 y 5 dígitos, puede empezar con 0.';
                return '';
            }
        },
        numero_tel: {
            input: document.getElementById('numero_tel'),
            error: document.getElementById('error-numero_tel'),
            validate: v => {
                if (/^15\d{8}$/.test(v)) return '';
                if (/^\d{6,8}$/.test(v)) return '';
                return 'Debe ser celular (15+8 dígitos) o fijo (6-8 dígitos).';
            }
        },
        email: {
            input: document.getElementById('email'),
            error: document.getElementById('error-email'),
            validate: v => /^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(v) ? '' : 'Email inválido.'
        },
        usuario: {
            input: document.getElementById('usuario'),
            error: document.getElementById('error-usuario'),
            validate: v => v.trim() !== '' ? '' : 'El usuario es obligatorio.'
        },
        direccion: {
            input: document.getElementById('direccion'),
            error: document.getElementById('error-direccion'),
            validate: v => v.trim() !== '' ? '' : 'La dirección es obligatoria.'
        },
        fecha_nacimiento: {
            input: document.getElementById('fecha_nacimiento'),
            error: document.getElementById('error-fecha_nacimiento'),
            validate: v => {
                if (!v) return 'La fecha de nacimiento es obligatoria.';
                const fecha = new Date(v);
                const hoy = new Date();
                let edad = hoy.getFullYear() - fecha.getFullYear();
                const m = hoy.getMonth() - fecha.getMonth();
                if (m < 0 || (m === 0 && hoy.getDate() < fecha.getDate())) edad--;
                if (edad < 18) return 'Debes tener al menos 18 años para registrarte.';
                return '';
            }
        }
    };
    Object.values(fields).forEach(f => {
        f.input.addEventListener('input', function() {
            const msg = f.validate(f.input.value);
            f.error.textContent = msg;
        });
    });
    form.addEventListener('submit', function(e) {
        let valid = true;
        Object.values(fields).forEach(f => {
            const msg = f.validate(f.input.value);
            f.error.textContent = msg;
            if (msg) valid = false;
        });
        if (!valid) e.preventDefault();
    });
    </script>
</body>
</html>

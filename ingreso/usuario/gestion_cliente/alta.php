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
    // Validación del DNI
    if (!is_numeric($dni) || $dni < 5000000) {
        echo "<script>
            alert('El DNI debe ser un número mayor o igual a 5 millones.');
            window.history.back();
        </script>";
        exit();
    }

    // Validación de la fecha de nacimiento
    $fecha_nacimiento = new DateTime($fecha_nacimiento);
    $hoy = new DateTime();
    $edad = $hoy->diff($fecha_nacimiento)->y;

    if ($edad < 18) {
        echo "<script>
            alert('Debes tener al menos 18 años para registrarte.');
            window.history.back();
        </script>";
        exit();
    }

    // Validación del teléfono
    $isCellular = false; // Asumimos que no es celular inicialmente
    if (preg_match('/^15\d{8}$/', $numero_tel)) {
        $isCellular = true;
    }

    if ($isCellular) {
        // Para celulares, número debe tener el prefijo '15' seguido de 8 dígitos
        if (!preg_match('/^\d{3,5}$/', $caracteristica_tel) || !preg_match('/^15\d{8}$/', $numero_tel)) {
            echo "<script>
                alert('Para números de celular, el número debe comenzar con 15 seguido de 8 dígitos.');
                window.history.back();
            </script>";
            exit();
        }
    } else {
        // Para teléfonos fijos, se debe anteponer '0' al código de área y el número debe tener entre 6 y 8 dígitos
        if (!preg_match('/^0\d{2,4}$/', $caracteristica_tel) || !preg_match('/^\d{6,8}$/', $numero_tel)) {
            echo "<script>
                alert('El teléfono fijo debe tener el prefijo 0 seguido de entre 2 y 4 dígitos de código de área, y entre 6 y 8 dígitos de número local.');
                window.history.back();
            </script>";
            exit();
        }
    }

    // Verificar si el email o el DNI ya existen
    $checkQuery = "SELECT id FROM clientes WHERE email = ? OR dni = ?";
    $stmt = $conexion->prepare($checkQuery);
    $stmt->bind_param("ss", $email, $dni);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo "<script>
            alert('El correo electrónico o DNI ya están registrados. Por favor, utiliza otro.');
            window.history.back();
        </script>";
    } else {
        $stmt = $conexion->prepare("INSERT INTO clientes (apellido, nombre, dni, caracteristica_tel, numero_tel, email, usuario, direccion, fecha_nacimiento, token) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        if ($stmt === false) {
            die("Error en la preparación de la consulta: " . $conexion->error);
        }
    
        $formatted_fecha_nacimiento = $fecha_nacimiento->format('Y-m-d');
        $stmt->bind_param("ssssssssss", $apellido, $nombre, $dni, $caracteristica_tel, $numero_tel, $email, $usuario, $direccion, $formatted_fecha_nacimiento, $token);
        if ($stmt->execute()) {
            // Enviar correo electrónico de confirmación
            sendConfirmationEmail($email, $token);
        
            // Mostrar mensaje de éxito y redirigir después de 2 segundos (opcional)
            echo "<script>
                alert('Registro exitoso. Por favor, revisa tu correo electrónico.');
                setTimeout(() => {
                    window.location.href = './gestioncliente.php';
                }, 1000);
            </script>";
            exit();
        } else {
            // Mostrar mensaje de error
            echo "<script>alert('Error al registrar los datos: " . $stmt->error . "');</script>";
        }
    
        $stmt->close();
        $conexion->close();
    }
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
            <form action="alta.php" method="post">
                <h3>Registro de Clientes</h3>
                <div class="form-group">
                    <label for="apellido">Apellido:</label>
                    <input type="text" id="apellido" name="apellido" required>
                </div>
                <div class="form-group">
                    <label for="nombre">Nombre:</label>
                    <input type="text" id="nombre" name="nombre" required>
                </div>
                <div class="form-group">
                    <label for="dni">DNI:</label>
                    <input type="text" id="dni" name="dni" required>
                </div>
                <div class="form-group">
                    <label for="caracteristica_tel">Código de Área (Argentina):</label>
                    <input type="text" id="caracteristica_tel" name="caracteristica_tel" required>
                </div>
                <div class="form-group">
                    <label for="numero_tel">Número de Teléfono:</label>
                    <input type="text" id="numero_tel" name="numero_tel" required>
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="usuario">Nombre de Usuario:</label>
                    <input type="text" id="usuario" name="usuario" required>
                </div>
                <div class="form-group">
                    <label for="direccion">Dirección:</label>
                    <input type="text" id="direccion" name="direccion" required>
                </div>
                <div class="form-group">
                    <label for="fecha_nacimiento">Fecha de Nacimiento:</label>
                    <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" required>
                </div>
                <button type="submit">Registrar</button>
            </form>
        </div>
    </main>
</body>
</html>

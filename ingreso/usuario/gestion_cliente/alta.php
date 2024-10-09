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
        .error {
            color: red;
            font-size: 0.875em;
        }
    </style>
    <script>
        function validateDni(dni) {
            const errorElement = document.getElementById('dni-error');
            if (!/^\d+$/.test(dni) || dni < 5000000) {
                errorElement.textContent = 'El DNI debe ser un número mayor o igual a 5 millones.';
                return false;
            } else {
                errorElement.textContent = '';
                return true;
            }
        }
        function validatePhone(caracteristica, numero) {
    const errorElement = document.getElementById('tel-error');
    
    if (/^15\d{8}$/.test(numero)) {
        // Validación para números de celular
        if (!/^\d{3,5}$/.test(caracteristica) || !/^15\d{8}$/.test(numero)) {
            errorElement.textContent = 'Para celulares, el número debe comenzar con 15 seguido de 8 dígitos.';
            return false;
        }
    } else {
        // Validación para teléfonos fijos
        if (!/^0\d{2,4}$/.test(caracteristica) || !/^\d{6,8}$/.test(numero)) {
            errorElement.textContent = 'Para teléfonos fijos, el código de área debe comenzar con 0, seguido de 2 a 4 dígitos, y el número local debe tener entre 6 y 8 dígitos.';
            return false;
        }
    }
    
    errorElement.textContent = '';
    return true;
}
        function validateAge(birthDate) {
            const errorElement = document.getElementById('age-error');
            const today = new Date();
            const birth = new Date(birthDate);
            let age = today.getFullYear() - birth.getFullYear();
            const month = today.getMonth() - birth.getMonth();
            if (month < 0 || (month === 0 && today.getDate() < birth.getDate())) {
                age--;
            }

            if (age < 18) {
                errorElement.textContent = 'Debes tener al menos 18 años para registrarte.';
                return false;
            } else {
                errorElement.textContent = '';
                return true;
            }
        }

       
function handleInput(event) {
    const field = event.target;
    const fieldName = field.name;
    if (fieldName === 'dni') {
        validateDni(field.value);
    } else if (fieldName === 'caracteristica_tel' || fieldName === 'numero_tel') {
        const caracteristica = document.getElementById('caracteristica_tel').value;
        const numero = document.getElementById('numero_tel').value;
        validatePhone(caracteristica, numero);
    } else if (fieldName === 'fecha_nacimiento') {
        validateAge(field.value);
    }
}
    </script>
</head>
<body>
    <main style="padding: 20px;">
    <form action="alta.php" method="post" onsubmit="return validateForm();">
    <h3 style="text-align: center;">Registro de Clientes</h3>

    <label for="apellido">Apellido:</label>
    <input type="text" id="apellido" name="apellido" required><br><br>

    <label for="nombre">Nombre:</label>
    <input type="text" id="nombre" name="nombre" required><br><br>

    <label for="dni">DNI:</label>
    <input type="text" id="dni" name="dni" oninput="handleInput(event)" required>
    <div id="dni-error" class="error"></div><br>

    <label for="caracteristica_tel">Código de Área (Argentina):</label>
    <input type="text" id="caracteristica_tel" name="caracteristica_tel" oninput="handleInput(event)" required>
    <div id="tel-error" class="error"></div><br>

    <label for="numero_tel">Número de Teléfono:</label>
    <input type="text" id="numero_tel" name="numero_tel" oninput="handleInput(event)" required>
    <div id="tel-error" class="error"></div><br>

    <label for="email">Email:</label>
    <input type="email" id="email" name="email" required><br><br>

    <label for="usuario">Nombre de Usuario:</label>
    <input type="text" id="usuario" name="usuario" required><br><br>
    
    <label for="direccion">Dirección:</label>
    <input type="text" id="direccion" name="direccion" required><br><br>

    <label for="fecha_nacimiento">Fecha de Nacimiento:</label>
    <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" oninput="handleInput(event)" required>
    <div id="age-error" class="error"></div><br>

    <input type="submit" value="Registrar">
</form>

    </main>
    <button style="width: 100px; margin: 20px auto; padding: 10px;">
        <a href="./gestioncliente.php" style="text-decoration: none; color: aliceblue;">Volver</a>
    </button>
</body>
</html>

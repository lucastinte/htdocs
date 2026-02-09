<?php
include('../../db.php');

session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: ingreso.php");
    exit();
}

$usuario = $_SESSION['usuario'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $apellido = $_POST['apellido'];
    $nombre = $_POST['nombre'];
    $dni = $_POST['dni'];
    $caracteristica_tel = $_POST['caracteristica_tel']; // Obtén la característica del teléfono
    $numero_tel = $_POST['numero_tel']; // Obtén el número de teléfono
    $direccion = $_POST['direccion'];
    $fecha_nacimiento = $_POST['fecha_nacimiento'];

    if (updateUserData($_SESSION['usuario'], $apellido, $nombre, $dni, $caracteristica_tel, $numero_tel, $direccion, $fecha_nacimiento)) {
        // Redirigir a la misma página para mostrar los datos actualizados
        header("Location: modificar_datos.php");
        exit();
    } else {
        // Manejo de error, redirigir a la misma página o mostrar un mensaje de error
        header("Location: modificar_datos.php?error=1");
        exit();
    }
}

$cliente = getUserData($_SESSION['usuario']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modificar Datos</title>
    <link rel="stylesheet" href="clienteform.css">
</head>
<body>
<header>
    <div class="container">
        <p class="logo">Mat Construcciones</p>
        <nav>
        <a href="logout.php" class="logout-button">Salir</a>

            <a href="cambiar_contrasena.php">Cambiar Contraseña</a>
            <a href="eliminar_cuenta.php">Eliminar Mi Cuenta</a>
            <a href="modificar_datos.php">Modificar Datos</a>
            <a href="consultar_proyecto.php">Consultar Proyecto</a>
        </nav>
    </div>
</header>

<section id="modify-data">
    <h1>Modificar Datos</h1>
    <form action="modificar_datos.php" method="post">
        <div class="form-group">
            <label for="apellido">Apellido:</label>
            <input type="text" id="apellido" name="apellido" value="<?php echo htmlspecialchars($cliente['apellido']); ?>" required>
        </div>
        <div class="form-group">
            <label for="nombre">Nombre:</label>
            <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($cliente['nombre']); ?>" required>
        </div>
        <div class="form-group">
            <label for="dni">DNI:</label>
            <input type="text" id="dni" name="dni" value="<?php echo htmlspecialchars($cliente['dni']); ?>" required>
        </div>
        <div class="form-group">
            <label for="caracteristica_tel">Característica:</label>
            <input type="text" id="caracteristica_tel" name="caracteristica_tel" value="<?php echo htmlspecialchars($cliente['caracteristica_tel']); ?>" required>
        </div>
        <div class="form-group">
            <label for="numero_tel">Número de Teléfono:</label>
            <input type="text" id="numero_tel" name="numero_tel" value="<?php echo htmlspecialchars($cliente['numero_tel']); ?>" required>
        </div>
        <div class="form-group">
            <label for="direccion">Dirección:</label>
            <input type="text" id="direccion" name="direccion" value="<?php echo htmlspecialchars($cliente['direccion']); ?>" required>
        </div>
        <div class="form-group">
            <label for="fecha_nacimiento">Fecha de Nacimiento:</label>
            <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" value="<?php echo htmlspecialchars($cliente['fecha_nacimiento']); ?>" required>
        </div>
        <button type="submit">Actualizar Datos</button>
    </form>
    <a href="cliente.php" class="back-button">Volver a Gestión de Cliente</a>
</section>
</body>
</html>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión Cliente</title>
    <link rel="stylesheet" href="cliente.css">
</head>
<body>
    
    <?php
    session_start();
    
    if (!isset($_SESSION['usuario'])) {
        header("Location: ingreso.php");
        exit();
    }

    $usuario = $_SESSION['usuario'];
    ?>

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

    <section id="hero">
        <h1>BIENVENIDO, <?php echo htmlspecialchars($usuario); ?>!</h1>
        <p>¡Estamos encantados de tenerte aquí!</p>
    </section>

</body>
</html>

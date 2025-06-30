<?php
// Definir la raíz del proyecto si no está definida
if (!defined('PROJECT_ROOT')) {
    define('PROJECT_ROOT', '/'); // Cambia '/' por la ruta base de tu proyecto si es necesario
}


// Obtener la configuración actual para mostrar 'talentos'
$sql = "SELECT mostrar_talentos FROM configuraciones WHERE id = 1";
$result = mysqli_query($conexion, $sql);
$row = mysqli_fetch_assoc($result);
$mostrar_talentos = $row['mostrar_talentos'];
?>

<header>
    <div class="container">
        <p class="logo">Mat Construcciones</p>
        <nav>
            <a href="<?php echo PROJECT_ROOT; ?>index.php">Inicio</a>
            <a href="<?php echo PROJECT_ROOT; ?>#Servicios">Servicios</a>
            <?php if ($mostrar_talentos): ?>
                <a href="<?php echo PROJECT_ROOT; ?>talentos.php">Talentos</a>
            <?php endif; ?>
            <a href="<?php echo PROJECT_ROOT; ?>ingreso/ingreso.php">Ingresar</a>
            <a href="<?php echo PROJECT_ROOT; ?>turnos.php">Turnos</a>
            <a href="<?php echo PROJECT_ROOT; ?>enviar_presupuesto.php">Empezá tu proyecto</a>
        </nav>
    </div>
</header>

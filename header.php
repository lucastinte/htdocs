<?php


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
            <a href="index.php">Inicio</a>
            <a href="#Servicios">Servicios</a>
            <?php if ($mostrar_talentos): ?>
                <a href="talentos.php">Talentos</a>
            <?php endif; ?>
            <a href="ingreso/ingreso.php">Ingresar</a>
            <a href="turnos.php">Turnos</a>
            <a href="enviar_presupuesto.php">Presupuestos</a>
        </nav>
    </div>
</header>

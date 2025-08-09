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
            <div class="dropdown">
                <a href="<?php echo PROJECT_ROOT; ?>enviar_presupuesto.php" class="dropdown-trigger">Cotizaciones ▾</a>
                <div class="dropdown-content">
                    <a href="<?php echo PROJECT_ROOT; ?>enviar_presupuesto.php">Casas</a>
                    <a href="<?php echo PROJECT_ROOT; ?>enviar_presupuesto.php">Canchas</a>
                    <a href="<?php echo PROJECT_ROOT; ?>enviar_presupuesto.php">Piletas</a>
                </div>
            </div>
        </nav>
    </div>
</header>

<style>
.dropdown {
    position: relative;
    display: inline-flex;
    align-items: center;
    height: 100%;
}

.dropdown-trigger {
    text-decoration: none;
    padding: 8px 12px;
    color: inherit;
    display: inline-flex;
    align-items: center;
    height: 100%;
    position: relative;
}

.dropdown-content {
    display: none;
    position: absolute;
    top: 100%;
    left: 50%;
    transform: translateX(-50%) translateY(-10px);
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(8px);
    min-width: 200px; /* Aumentado el ancho mínimo */
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
    border-radius: 12px;
    overflow: hidden;
    z-index: 1000;
    opacity: 0;
    transition: all 0.3s ease;
    border: 1px solid rgba(0, 0, 0, 0.08);
    margin-top: 5px;
    padding: 8px 0; /* Añadido padding vertical */
}

/* Agregamos un área invisible para evitar que el menú se cierre */
.dropdown:before {
    content: '';
    position: absolute;
    top: 100%;
    left: 0;
    width: 100%;
    height: 20px; /* Área de buffer para el movimiento del mouse */
}

.dropdown-content a {
    color: #333;
    padding: 12px 24px; /* Aumentado el padding horizontal */
    text-decoration: none;
    display: block;
    transition: all 0.2s ease;
    font-size: 0.95em;
    position: relative;
    white-space: nowrap; /* Evita que el texto se rompa en múltiples líneas */
    margin: 2px 0; /* Añade espacio entre elementos */
}

.dropdown-content a:after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 20px;
    right: 20px;
    height: 1px;
    background: rgba(0, 0, 0, 0.04);
}

.dropdown-content a:last-child:after {
    display: none;
}

.dropdown-content a:hover {
    background-color: rgba(138, 43, 226, 0.05);
    color: #8a2be2;
    padding-left: 25px;
}

.dropdown:hover .dropdown-content {
    display: block;
    opacity: 1;
    transform: translateX(-50%) translateY(0);
    pointer-events: auto; /* Asegura que el menú sea interactivo */
}

/* Añadimos un retraso en el cierre del menú */
.dropdown-content {
    pointer-events: none;
    transition: opacity 0.3s ease, transform 0.3s ease, visibility 0s linear 0.3s;
    visibility: hidden;
}

.dropdown:hover .dropdown-content {
    visibility: visible;
    transition-delay: 0s;
}

.dropdown-trigger:after {
    content: '▾';
    margin-left: 5px;
    transition: transform 0.3s;
    font-size: 0.8em;
    opacity: 0.7;
}

.dropdown:hover .dropdown-trigger:after {
    transform: rotate(180deg);
}

/* Añadir un pseudo-elemento para el hover suave del trigger */
.dropdown-trigger:before {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 50%;
    width: 0;
    height: 2px;
    background: #8a2be2;
    transition: all 0.3s ease;
    transform: translateX(-50%);
}

.dropdown:hover .dropdown-trigger:before {
    width: 80%;
}
</style>

<?php
// Iniciar la sesión e incluir la conexión a la base de datos
session_start();
include 'db.php';
include "header.php";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mat</title>
    <link rel="stylesheet" href="index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

</head>

<body>

                <main>
    <section id="hero">
        <h1>Construimos tus ideas <br> desarrollamos tus sueños</h1>
            <button><a href="enviar_presupuesto.php">COMIENZA</a></button>
    </section>

    <section id="Nosotros">
        <div class="container">
            <div class="img-container"></div>
            <div class="texto">
                <h2>Somos <br><span class="color-acento">Mat Construcciones</span></h2>
                <h1>Un equipo caracterizado por el cumplimiento</h1>
                <p>Pensamos que el desarrollo de un proyecto es
                    la construccion de un sueño, la alquimia de lo posible</p>
                <button><a href="enviar_presupuesto.php">Saber Mas</a></button>
            </div>
        </div>
    </section>

    <section id="Servicios">
        <div class="container">
            <h3>GENERAR CONFIANZA</h3>
            <h2>LA EXCELENCIA EN NUESTROS TRABAJOS <br> NOS REPRESENTA</h2>
            <div class="trabajo">
                <?php
                // Obtener servicios de la base de datos
                $result = $conexion->query("SELECT * FROM servicios WHERE activo = 1 ORDER BY orden");
                while ($servicio = $result->fetch_assoc()): ?>
                    <div class="tarjeta" style="background-image: linear-gradient(0deg, rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('imagen/servicios/<?php echo htmlspecialchars($servicio['imagen']); ?>');">
                        <h3><?php echo htmlspecialchars($servicio['titulo']); ?></h3>
                        <p><?php echo htmlspecialchars($servicio['descripcion']); ?></p>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </section>

    <div class="container__service">
  <div class="slider">
    <button class="slider-prev">&lt;</button>
    <div class="slider-track">
      <div class="card__service">
        <img src="/imagen/servicios/casa.png" alt="Planos de Casas">
        <h2>Planos de Casas</h2>
        <p>El plano de una casa muestra la distribución del espacio desde arriba, incluyendo puertas y ventanas.</p>
      </div>
      <div class="card__service">
        <img src="/imagen/servicios/agua-del-grifo.png" alt="Planos Sanitarios">
        <h2>Planos Sanitarios</h2>
        <p>Representación gráfica de redes de agua, desagüe y accesorios.</p>
      </div>
      <div class="card__service">
        <img src="/imagen/servicios/bombilla.png" alt="Planos Eléctricos">
        <h2>Planos Eléctricos</h2>
        <p>Representación de circuitos eléctricos, materiales y dispositivos.</p>
      </div>
      <div class="card__service">
        <img src="/imagen/servicios/arquitecto.png" alt="Aprobación de Planos">
        <h2>Aprobación de Planos</h2>
        <p>Incluye planos de localización, arquitectura y estructuras.</p>
      </div>
      <div class="card__service">
        <img src="/imagen/servicios/seguridad-en-el-trabajo.png" alt="Control de Obra">
        <h2>Control de Obra</h2>
        <p>Supervisión, planes de mejora y control de inventario de materiales.</p>
      </div>
      <div class="card__service">
        <img src="/imagen/servicios/construccion.png" alt="Otros">
        <h2>Otros</h2>
        <p>Piscinas, canchas, tinglados y presupuestos.</p>
      </div>
    </div>
    <button class="slider-next">&gt;</button>
  </div>
</div>

    <section id="final">
        <h2>Listo para Construir?</h2>
        <button><a href="enviar_presupuesto.php">COMIENZA</button>
    </section>
    </main>

    <footer class="footer">
  <div class="footer-content">
    <div class="footer-info">
      <p class="slogan">Construimos tus ideas,<br> desarrollamos tus sueños.</p>
      <div class="social-icons">
        <a href="https://instagram.com" target="_blank" title="Instagram"><i class="fab fa-instagram"></i></a>
        <a href="https://facebook.com" target="_blank" title="Facebook"><i class="fab fa-facebook-f"></i></a>
        <a href="https://wa.me/5491112345678" target="_blank" title="WhatsApp"><i class="fab fa-whatsapp"></i></a>
      </div>
    </div>
    <div class="footer-links">
      <h4>Enlaces</h4>
      <ul>
        <li><a href="index.php">Inicio</a></li>
        <li><a href="servicios.php">Servicios</a></li>
        <li><a href="talentos.php">Talentos</a></li>
        <li><a href="presupuestos.php">Presupuestos</a></li>
      </ul>
    </div>
    <div class="footer-contact">
      <h4>Contacto</h4>
      <p>📍 El Manzano N 689</p>
      <p>📞 3884800555</p>
      <p>✉️ matiasgalo22@gmail.com</p>
    </div>
  </div>
  <div class="footer-bottom">
    &copy; 2025 Mat Construcciones. Todos los derechos reservados.
  </div>
</footer>
<script src="slider.js"></script>
</body>
</html>

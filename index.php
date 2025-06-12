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

    <div class="container__service container__tarjeta-primaria div__offset">
            <div class="service tarjeta__primaria">
                <div class="text__service texto__tarjeta-primaria">
                    <h1>QUE HACEMOS?</h1>
                    <h1>Una gran variedad de servicios y de la mejor calidad</h1>
                </div>

                <div class="container__tarjeta-service container__caja-tarjetaPrimaria">
                    <div class="card__service caja__tarjeta-primaria">
                        <img src="/imagen/servicios/casa.png" alt="">
                        <h2>Planos de Casas</h2>
                        <p>El plano de una casa es un dibujo que muestra la distribución del espacio de una casa desde arriba. Incluye elementos clave como puertas, ventanas y escaleras, así como el tamaño de cada dormitorio. También muestra los nombres
                             y superficie de las habitaciones, así como las medidas entre paredes.</p>
                        <a href="#">
                            <img src="/imagen/servicios/flecha-correcta.png" alt="">
                        </a>    
                    </div>
                    <div class="card__service caja__tarjeta-primaria">
                        <img src="/imagen/servicios/agua-del-grifo.png" alt="">
                        <h2>Planos Sanitarios</h2>
                        <p>El plano de instalaciones sanitarias es la representación gráfica del desarrollo de: redes de agua (A.A.P.P), desagüe o aguas servidas (A.A.S.S), agua lluvias (A.A.L.L), válvulas, accesorios. También diámetros de tuberías y accesorios que las unen y
                             controlan el caudal, así como la ubicación y características de los equipos y piezas sanitarias que permiten el funcionamiento y servicio.</p>
                        <a href="#">
                            <img src="/imagen/servicios/flecha-correcta.png" alt="">
                        </a>    
                    </div>
                    <div class="card__service caja__tarjeta-primaria">
                        <img src="/imagen/servicios/bombilla.png" alt="">
                        <h2>Planos Electricos</h2>
                        <p>Un plano eléctrico es la representación de los diferentes circuitos que componen y definen las características 
                            de una instalación eléctrica y donde se detallan las particularidades de los materiales y dispositivos existentes.</p>
                        <a href="#">
                            <img src="/imagen/servicios/flecha-correcta.png" alt="">
                        </a>    
                    </div>
                    <div class="card__service caja__tarjeta-primaria">
                        <img src="/imagen/servicios/arquitecto.png" alt="">
                        <h2>Aprobacion de Planos</h2>
                        <p>Plano de localización. Plano de arquitectura. Plano de estructuras. Si la propiedad tiene más de 4 niveles, plano de suelos; si es vivienda comercial, a partir del primer nivel.</p>
                        <a href="#">
                            <img src="/imagen/servicios/flecha-correcta.png" alt="">
                        </a>    
                    </div>
                    <div class="card__service caja__tarjeta-primaria">
                        <img src="/imagen/servicios/seguridad-en-el-trabajo.png" alt="">
                        <h2>Control de Obra</h2>
                        <p>Nos aseguramos de tener toda la documentación en regla, realizamos inspecciones de supervisión de obra, hacemos planes de mejora, utilizamos materiales 
                            adecuados suministrados por un aliado estratégico e implementamos un control de inventario de materiales de construcción.</p>
                        <a href="#">
                            <img src="/imagen/servicios/flecha-correcta.png" alt="">
                        </a>    
                    </div>
                    <div class="card__service caja__tarjeta-primaria">
                        <img src="/imagen/servicios/construccion.png" alt="">
                        <h2>Otros</h2>
                        <p>Piscinas, canchas, tinglados, presupuestos, materiales, mano de obra…
                            Y muchos más, acércate y consúltanos sin compromiso….</p>
                        <a href="#">
                            <img src="/imagen/servicios/flecha-correcta.png" alt="">
                        </a>    
                    </div>
                </div>
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
<style>
  .footer {
    background-color: #000;
    color: #fff;
    padding: 40px 20px 20px;
    font-family: 'Segoe UI', sans-serif;
    box-shadow: 0 -2px 12px rgba(0, 0, 0, 0.4);
  }
  .footer-content {
    display: flex;
    justify-content: space-between;
    flex-wrap: wrap;
    max-width: 1100px;
    margin: auto;
    gap: 40px;
  }
  .footer-info {
    flex: 1;
    min-width: 200px;
  }
  .slogan {
    margin-bottom: 15px;
    font-size: 0.95em;
    color: #fff;
  }
  .social-icons a {
    font-size: 1.5em;
    margin-right: 15px;
    text-decoration: none;
    color: white;
    transition: color 0.3s;
  }
  .social-icons a:hover {
    color: #9b59b6;
  }
  .footer-links,
  .footer-contact {
    flex: 1;
    min-width: 200px;
  }
  .footer-links h4,
  .footer-contact h4 {
    margin-bottom: 12px;
    font-size: 1.1em;
    border-bottom: 2px solid #fff2;
    display: inline-block;
    padding-bottom: 4px;
    color: white;
  }
  .footer-links ul {
    list-style: none;
    padding: 0;
    margin: 0;
  }
  .footer-links ul li {
    margin-bottom: 10px;
  }
  .footer-links ul li a {
    color: white;
    text-decoration: none;
    transition: color 0.3s;
  }
  .footer-links ul li a:hover {
    color: #9b59b6;
  }
  .footer-contact p {
    margin: 6px 0;
    font-size: 0.95em;
    color: white;
  }
  .footer-bottom {
    text-align: center;
    padding-top: 20px;
    border-top: 1px solid #444;
    font-size: 0.85em;
    color: #aaa;
    margin-top: 30px;
  }
  @media (max-width: 768px) {
    .footer-content {
      flex-direction: column;
      align-items: center;
      text-align: center;
    }
    .social-icons {
      justify-content: center;
    }
  }
</style>

</body>
</html>

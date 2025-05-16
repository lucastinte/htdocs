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
</head>

<body>

                <main>
    <section id="hero">
        <h1>Construimos tus ideas <br> desarrollamos tus sueños</h1>
            <button><a href="enviar_presupuesto.php">COMIEzzNZA</a></button>
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
                <div class="tarjeta">
                    <h3>CANCHAS</h3>
                    <p>Canchas de todo tipo, de cesped sintetico o natural.
                        Para uso familiar o de alquiler.</p>
                </div>
                <div class="tarjeta">
                    <h3>HOGARES</h3>
                    <p>Casas modernas, amplias, iluminadas, un hogar para
                        vos y tu familia.</p>
                </div>
                <div class="tarjeta">
                    <h3>PISCINAS</h3>
                    <p>Todo tipo de piscinas, piletas, a medida. Con diseño
                        unico y sorprendente.</p>
                </div>
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
        <button>COMIENZA</button>
    </section>
    </main>

    <footer>
        <div class="container">
            <p>&copy;MatC</p>
        </div>
    </footer>
</body>
</html>

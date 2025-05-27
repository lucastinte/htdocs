<?php
session_start();
include '../../db.php';

// Obtener el estado actual de la base de datos
$sql = "SELECT mostrar_talentos FROM configuraciones WHERE id = 1";
$result = mysqli_query($conexion, $sql);

if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $mostrar_talentos = $row['mostrar_talentos'];
} else {
    // Si no se encuentra la fila, inicializar con valor predeterminado
    $mostrar_talentos = 0;
}

// Manejar el cambio de estado del botón
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['activar_talentos'])) {
        $sql = "UPDATE configuraciones SET mostrar_talentos = 1 WHERE id = 1";
        mysqli_query($conexion, $sql);
        $mostrar_talentos = true;
    } elseif (isset($_POST['desactivar_talentos'])) {
        $sql = "UPDATE configuraciones SET mostrar_talentos = 0 WHERE id = 1";
        mysqli_query($conexion, $sql);
        $mostrar_talentos = false;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion Usuario</title>
    <link rel="stylesheet" href="usuario.css">
    <link rel="stylesheet" href="/modal-q.css">
    <script src="/modal-q.js"></script>
</head>

<body>
  <header>
    <div class="container">
        <p class="logo">Mat Construcciones</p>
        <nav>
            <ul>
                <li class="dropdown">
                    <a href="#" class="dropbtn">Empresa</a>
                    <div class="dropdown-content">
                        <a href="talentos.php"> Talentos</a>
                        <a href="turnos.php"> Turnos</a>
                        <a href="presupuestos.php"> Presupuesto</a>
                    </div>
                </li>
                <li class="dropdown">
                    <a href="#" class="dropbtn">Administrar</a>
                    <div class="dropdown-content">
                        <a href="gestion_cliente/gestioncliente.php"> Clientes</a>
                        <a href="gestion_usuario/gestionusuario.php"> Usuarios</a>
                    </div>
                </li>
                <li><a href="../../index.php">Volver a Inicio</a></li>
            </ul>
        </nav>
    </div>
</header>

    <section id="hero">
        <h1>BIENVENIDO<br> AL SISTEMA DE GESTION <br> COMO USUARIO</h1>
        <form id="talentosForm" method="post">
            <?php if (!$mostrar_talentos): ?>
                <button type="button" onclick="confirmarTalentos('activar')">Activar Talentos</button>
            <?php else: ?>
                <button type="button" onclick="confirmarTalentos('desactivar')">Desactivar Talentos</button>
            <?php endif; ?>
        </form>
<button onclick="window.location.href='gestionar_servicios.php'" style="margin:10px;">Ir a gestión</button>


    </section> 

    <!-- Modal Q reutilizable -->
    <div id="modal-q" style="display:none;position:fixed;z-index:9999;left:0;top:0;width:100vw;height:100vh;background:rgba(0,0,0,0.6);justify-content:center;align-items:center;">
      <div class="modal-content">
        <h2 id="modal-q-title"></h2>
        <p id="modal-q-msg"></p>
        <button onclick="closeModalQ()">OK</button>
      </div>
    </div>
    <script>
    function confirmarTalentos(accion) {
      let mensaje = accion === 'activar' ? '¿Estás seguro de que deseas activar la sección Talentos en el menú principal?' : '¿Estás seguro de que deseas ocultar la sección Talentos del menú principal?';
      showModalQ(mensaje, false, null, 'Confirmar Acción');
      setTimeout(() => {
        const modal = document.getElementById('modal-q');
        const content = modal.querySelector('.modal-content');
        let btns = content.querySelectorAll('button');
        btns.forEach(btn => btn.remove());
        // Botón Sí
        const btnSi = document.createElement('button');
        btnSi.textContent = 'Sí';
        btnSi.onclick = function() {
          closeModalQ();
          // Crear y enviar el formulario
          const form = document.getElementById('talentosForm');
          // Eliminar cualquier input hidden previo
          let prev = form.querySelector('input[name="activar_talentos"], input[name="desactivar_talentos"]');
          if (prev) prev.remove();
          const input = document.createElement('input');
          input.type = 'hidden';
          input.name = accion === 'activar' ? 'activar_talentos' : 'desactivar_talentos';
          input.value = '1';
          form.appendChild(input);
          form.submit();
        };
        // Botón No
        const btnNo = document.createElement('button');
        btnNo.textContent = 'No';
        btnNo.onclick = function() {
          closeModalQ();
        };
        content.appendChild(btnSi);
        content.appendChild(btnNo);
      }, 100);
    }
    </script>
</body>
</html>

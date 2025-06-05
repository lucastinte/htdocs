<?php
session_start();
require_once '../../config.php';
require_once '../../db.php';

// Verificar si el usuario está logueado y tiene permisos
if (!isset($_SESSION['usuario'])) {
    header('Location: ../ingreso.php');
    exit();
}

function manejarError($mensaje) {
    header('Location: gestionar_servicios.php?error=1&mensaje=' . urlencode($mensaje));
    exit();
}

// Manejo de acciones CRUD
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        try {
            switch ($_POST['action']) {
                case 'create':
                    $titulo = $_POST['titulo'];
                    $descripcion = $_POST['descripcion'];
                    $orden = $_POST['orden'];
                    
                    // Manejo de la imagen
                    $imagen = '';
                    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === 0) {
                        $imagen_temp = $_FILES['imagen']['tmp_name'];
                        $imagen_nombre = strtolower($_FILES['imagen']['name']); // Convertir a minúsculas
                        $imagen_tipo = $_FILES['imagen']['type'];
                        $imagen_tamano = $_FILES['imagen']['size'];
                        
                        // Verificar el tipo de archivo
                        $permitidos = array('image/jpeg', 'image/png', 'image/gif', 'image/jpg');
                        if (!in_array($imagen_tipo, $permitidos)) {
                            manejarError('Solo se permiten archivos JPG, JPEG, PNG y GIF.');
                        }
                        
                        // Verificar tamaño (5MB máximo)
                        if ($imagen_tamano > 5242880) {
                            manejarError('El archivo es demasiado grande. El tamaño máximo es 5MB.');
                        }
                        
                        // Asegurar que el directorio existe y tiene permisos correctos
                        $directorio_destino = "../../imagen/servicios";
                        if (!file_exists($directorio_destino)) {
                            if (!mkdir($directorio_destino, 0755, true)) {
                                manejarError('No se pudo crear el directorio para las imágenes.');
                            }
                        }
                        
                        // Verificar permisos de escritura
                        if (!is_writable($directorio_destino)) {
                            manejarError('El directorio de destino no tiene permisos de escritura.');
                        }
                        
                        $imagen_destino = $directorio_destino . "/" . $imagen_nombre;
                        
                        // Verificar y copiar la imagen
                        if (!move_uploaded_file($imagen_temp, $imagen_destino)) {
                            $error = error_get_last();
                            manejarError('Error al subir la imagen: ' . ($error ? $error['message'] : 'Error desconocido'));
                        }
                        
                        // Asignar permisos de lectura/escritura
                        if (!chmod($imagen_destino, 0644)) {
                            unlink($imagen_destino); // Eliminar archivo si no se pueden establecer permisos
                            manejarError('No se pudieron establecer los permisos del archivo.');
                        }
                        
                        $imagen = $imagen_nombre;
                    }
                    
                    $stmt = $conexion->prepare("INSERT INTO servicios (titulo, descripcion, imagen, orden) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param("sssi", $titulo, $descripcion, $imagen, $orden);
                    if (!$stmt->execute()) {
                        throw new Exception("Error al guardar en la base de datos");
                    }
                    header('Location: gestionar_servicios.php?success=create');
                    break;

                case 'update':
                    $id = $_POST['id'];
                    $titulo = $_POST['titulo'];
                    $descripcion = $_POST['descripcion'];
                    $orden = $_POST['orden'];
                    
                    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === 0) {
                        // Si hay nueva imagen, actualizarla
                        $imagen_temp = $_FILES['imagen']['tmp_name'];
                        $imagen_nombre = strtolower($_FILES['imagen']['name']); // Convertir a minúsculas
                        $imagen_tipo = $_FILES['imagen']['type'];
                        $imagen_tamano = $_FILES['imagen']['size'];
                        
                        // Verificar el tipo de archivo
                        $permitidos = array('image/jpeg', 'image/png', 'image/gif', 'image/jpg');
                        if (!in_array($imagen_tipo, $permitidos)) {
                            manejarError('Solo se permiten archivos JPG, JPEG, PNG y GIF.');
                        }
                        
                        // Verificar tamaño (5MB máximo)
                        if ($imagen_tamano > 5242880) {
                            manejarError('El archivo es demasiado grande. El tamaño máximo es 5MB.');
                        }
                        
                        // Asegurar que el directorio existe y tiene permisos correctos
                        $directorio_destino = "../../imagen/servicios";
                        if (!file_exists($directorio_destino)) {
                            if (!mkdir($directorio_destino, 0755, true)) {
                                manejarError('No se pudo crear el directorio para las imágenes.');
                            }
                        }
                        
                        // Verificar permisos de escritura
                        if (!is_writable($directorio_destino)) {
                            manejarError('El directorio de destino no tiene permisos de escritura.');
                        }
                        
                        $imagen_destino = $directorio_destino . "/" . $imagen_nombre;
                        
                        // Verificar y copiar la imagen
                        if (!move_uploaded_file($imagen_temp, $imagen_destino)) {
                            $error = error_get_last();
                            manejarError('Error al subir la imagen: ' . ($error ? $error['message'] : 'Error desconocido'));
                        }
                        
                        // Asignar permisos de lectura/escritura
                        if (!chmod($imagen_destino, 0644)) {
                            unlink($imagen_destino); // Eliminar archivo si no se pueden establecer permisos
                            manejarError('No se pudieron establecer los permisos del archivo.');
                        }
                        
                        $stmt = $conexion->prepare("UPDATE servicios SET titulo = ?, descripcion = ?, imagen = ?, orden = ? WHERE id = ?");
                        $stmt->bind_param("sssii", $titulo, $descripcion, $imagen_nombre, $orden, $id);
                    } else {
                        // Si no hay nueva imagen, mantener la existente
                        $stmt = $conexion->prepare("UPDATE servicios SET titulo = ?, descripcion = ?, orden = ? WHERE id = ?");
                        $stmt->bind_param("ssii", $titulo, $descripcion, $orden, $id);
                    }
                    if (!$stmt->execute()) {
                        throw new Exception("Error al actualizar en la base de datos");
                    }
                    header('Location: gestionar_servicios.php?success=update');
                    break;

                case 'delete':
                    $id = $_POST['id'];
                    // Obtener nombre de la imagen antes de eliminar
                    $stmt = $conexion->prepare("SELECT imagen FROM servicios WHERE id = ?");
                    $stmt->bind_param("i", $id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    if ($row = $result->fetch_assoc()) {
                        $imagen = "../../imagen/servicios/" . $row['imagen'];
                        if (file_exists($imagen)) {
                            unlink($imagen);
                        }
                    }
                    
                    $stmt = $conexion->prepare("DELETE FROM servicios WHERE id = ?");
                    $stmt->bind_param("i", $id);
                    $stmt->execute();
                    header('Location: gestionar_servicios.php?success=delete');
                    break;
            }
        } catch (Exception $e) {
            manejarError($e->getMessage());
        }
        exit();
    }
}

// Obtener todos los servicios
$result = $conexion->query("SELECT * FROM servicios ORDER BY orden");
$servicios = $result->fetch_all(MYSQLI_ASSOC);

// Mensajes para el modal
$mensajes = [
    'create' => 'Servicio agregado exitosamente',
    'update' => 'Servicio actualizado exitosamente',
    'delete' => 'Servicio eliminado exitosamente'
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Servicios</title>
    <link rel="stylesheet" href="../index.css">
    <link rel="stylesheet" href="usuario.css">
    <link rel="stylesheet" href="../../modal-q.css">
    <style>
        :root {
            --primary-color: blueviolet;
            --primary-hover: rgb(101, 33, 165);
            --danger-color: #ef4444;
            --danger-hover: #dc2626;
            --text-primary: #333;
            --text-secondary: #666;
            --border-color: #e5e7eb;
            --bg-color: #f9fafb;
            --card-bg: #ffffff;
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-primary);
            line-height: 1.5;
        }
        
        .servicios-container {
            padding: 40px 20px;
            max-width: 1200px;
            margin: 80px auto 0;
            animation: fadeIn 0.5s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
#modal-q .close {
  display: none !important;
}

        .servicios-container h2 {
            color: var(--text-primary);
            font-size: 2.5em;
            margin-bottom: 40px;
            text-align: center;
            font-weight: 800;
            letter-spacing: -0.5px;
        }

        .servicios-container h3 {
            color: var(--text-secondary);
            font-size: 1.8em;
            margin: 40px 0 25px;
            text-align: center;
            font-weight: 700;
        }

        .servicio-card {
            background: var(--card-bg);
            padding: 35px;
            margin-bottom: 30px;
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1), 0 1px 2px -1px rgba(0,0,0,0.1);
            border: 1px solid var(--border-color);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .servicio-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -4px rgba(0,0,0,0.1);
            border-color: var(--primary-color);
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 35px;
            margin-bottom: 25px;
        }

        .form-grid > div {
            display: flex;
            flex-direction: column;
        }

        .form-grid label {
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 10px;
            font-size: 1.1em;
        }

        .form-grid input[type="text"],
        .form-grid input[type="number"],
        .form-grid textarea {
            padding: 14px;
            border: 2px solid var(--border-color);
            border-radius: 10px;
            font-size: 1em;
            transition: all 0.2s ease;
            background-color: var(--bg-color);
        }

        .form-grid textarea {
            min-height: 140px;
            resize: vertical;
            line-height: 1.6;
        }

        .form-grid input:focus,
        .form-grid textarea:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(99,102,241,0.1);
            background-color: var(--card-bg);
        }

        .servicio-imagen {
            max-width: 300px;
            height: auto;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -2px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
            object-fit: cover;
        }

        .servicio-imagen:hover {
            transform: scale(1.02);
        }

        input[type="file"] {
            padding: 12px;
            background: var(--bg-color);
            border-radius: 10px;
            border: 2px dashed var(--border-color);
            cursor: pointer;
            width: 100%;
            transition: all 0.2s ease;
        }

        input[type="file"]:hover {
            border-color: var(--primary-color);
            background-color: rgba(99,102,241,0.05);
        }

        small {
            color: var(--text-secondary);
            margin-top: 8px;
            font-style: italic;
            font-size: 0.9em;
        }

        button {
            padding: 14px 28px;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            width: auto;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin: 5px;
            font-size: 1em;
            letter-spacing: 0.025em;
        }

        button[type="submit"] {
            background-color: blueviolet;
            color: white;
            font-size: 1.5em;
            font-weight: bold;
            padding: 10px 20px;
            box-shadow: 2px 2px 10px rgba(0,0,0,0.5);
        }

        button[type="submit"]:hover {
            background-color: rgb(101, 33, 165);
            transform: translateY(-2px);
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.3);
        }

        button[type="submit"]:active {
            transform: translateY(0);
        }

        button[style*="background-color: #dc3545"] {
            background-color: var(--danger-color) !important;
            color: white;
        }

        button[style*="background-color: #dc3545"]:hover {
            background-color: var(--danger-hover) !important;
            transform: translateY(-2px);
            box-shadow: 0 4px 6px -1px rgba(239,68,68,0.2);
        }

        .acciones {
            display: flex;
            justify-content: center;
            gap: 12px;
            margin-top: 25px;
        }

        @media (max-width: 768px) {
            .servicios-container {
                padding: 20px 15px;
                margin-top: 60px;
            }
            
            .form-grid {
                grid-template-columns: 1fr;
                gap: 25px;
            }
            
            .servicio-card {
                padding: 25px;
            }

            .servicios-container h2 {
                font-size: 2em;
                margin-bottom: 30px;
            }

            .servicios-container h3 {
                font-size: 1.5em;
                margin: 30px 0 20px;
            }

            button {
                width: 100%;
                margin: 5px 0;
            }
        }
    </style>
</head>
<body>
    <script>
document.addEventListener('DOMContentLoaded', () => {
  // Traemos desde PHP los órdenes ya usados
  let usedOrders = <?php echo json_encode(array_column($servicios, 'orden')); ?>;
  const inputOrden = document.getElementById('nuevo-orden');
  const errorEl    = document.getElementById('orden-error');
  const formNuevo  = inputOrden.closest('form');
  const submitBtn  = formNuevo.querySelector('button[type="submit"]');

  function showModalQ(msg) {
    if (typeof window.showModalQ === 'function') {
      window.showModalQ(msg, true, null, 'Error', 'error');
      setTimeout(window.closeModalQ, 2000);
    } else {
      alert(msg);
    }
  }

  function validarOrden() {
    const v = parseInt(inputOrden.value, 10);
    if (usedOrders.length >= 5) {
      errorEl.textContent = 'No se pueden agregar más servicios, todos los órdenes del 1 al 5 están ocupados.';
      submitBtn.disabled = true;
      return;
    }
    if (isNaN(v) || v < 1 || v > 5) {
      errorEl.textContent = 'El orden debe ser un número entre 1 y 5.';
      submitBtn.disabled = true;
    } else if (usedOrders.includes(v)) {
      errorEl.textContent = 'Ya existe un servicio con ese orden.';
      submitBtn.disabled = true;
    } else {
      errorEl.textContent = '';
      submitBtn.disabled = false;
    }
  }

  inputOrden.addEventListener('input', validarOrden);
  validarOrden(); // validar al cargar

  // Validación al enviar el formulario de nuevo servicio
  formNuevo.addEventListener('submit', function(e) {
    // Refrescar órdenes ocupados justo antes de enviar
    usedOrders = Array.from(document.querySelectorAll('.orden-input')).map(i => parseInt(i.value, 10));
    const v = parseInt(inputOrden.value, 10);
    if (usedOrders.length >= 5) {
      showModalQ('No se pueden agregar más servicios, todos los órdenes del 1 al 5 están ocupados.');
      e.preventDefault();
      return;
    }
    if (isNaN(v) || v < 1 || v > 5) {
      showModalQ('El orden debe ser un número entre 1 y 5.');
      e.preventDefault();
      return;
    }
    if (usedOrders.includes(v)) {
      showModalQ('Ya existe un servicio con ese orden.');
      e.preventDefault();
      return;
    }
  });
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Para el formulario de nuevo servicio
    const nuevoOrden = document.getElementById('nuevo-orden');
    if (nuevoOrden) {
        nuevoOrden.value = 1;
        nuevoOrden.addEventListener('input', function() {
            let value = parseInt(this.value);
            if (value < 1) this.value = 1;
            if (value > 5) this.value = 5;
        });
    }

    // Para los formularios de servicios existentes
    document.querySelectorAll('.servicio-card form').forEach(function(form) {
        const ordenInput = form.querySelector('.orden-input');
        if (!ordenInput) return;
        const idInput = form.querySelector('input[name="id"]');
        const servicioId = idInput ? parseInt(idInput.value) : null;
        const ordenesOcupados = <?php echo json_encode(array_map(function($s){return ['id'=>$s['id'],'orden'=>$s['orden']];}, $servicios)); ?>;
        ordenInput.addEventListener('input', function() {
            let value = parseInt(this.value);
            if (value < 1) this.value = 1;
            if (value > 5) this.value = 5;
            // Validar que no se repita el orden (excepto el propio)
            const repetido = ordenesOcupados.some(function(s) {
                return s.orden == value && s.id != servicioId;
            });
            const errorEl = ordenInput.parentElement.querySelector('.orden-error');
            const submitBtn = form.querySelector('button[type="submit"]');
            if (repetido) {
                errorEl.textContent = 'Ya existe un servicio con ese orden.';
                submitBtn.disabled = true;
            } else {
                errorEl.textContent = '';
                submitBtn.disabled = false;
            }
        });
        // Validación al enviar el formulario de edición
        form.addEventListener('submit', function(e) {
            const value = parseInt(ordenInput.value);
            const repetido = ordenesOcupados.some(function(s) {
                return s.orden == value && s.id != servicioId;
            });
            if (repetido) {
                const errorEl = ordenInput.parentElement.querySelector('.orden-error');
                errorEl.textContent = 'Ya existe un servicio con ese orden.';
                if (typeof window.showModalQ === 'function') {
                  window.showModalQ('Ya existe un servicio con ese orden.', true, null, 'Error', 'error');
                  setTimeout(window.closeModalQ, 2000);
                }
                e.preventDefault();
                return false;
            }
        });
    });
});
</script>

    <header>
    <div class="container">
         <div class="user-badge">
          <?php if (isset($_SESSION['usuario'])): ?>
           <p class="logo"> <span class="user-icon">&#128100;</span> <?php echo htmlspecialchars($_SESSION['usuario']); ?></p>
          <?php endif; ?>
        </div>
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
                <li><a href="usuario.php">Volver</a></li>
            </ul>
        </nav>
    </div>
</header>

    <div class="servicios-container">
        <h2>Gestionar Servicios</h2>
        
        <!-- Formulario para crear nuevo servicio -->
        <div class="servicio-card">
            <h3>Agregar Nuevo Servicio</h3>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="create">
                <div class="form-grid">
                    <div>
                        <label>Título:</label>
                        <input type="text" name="titulo" required>
                    </div>
                    <div>
                        <label>Descripción:</label>
                        <textarea name="descripcion" required></textarea>
                    </div>
                    <div>
                        <label>Imagen:</label>
                        <input type="file" name="imagen" accept="image/*" required>
                    </div>
                    <!-- Dentro de <div class="form-grid"> de “Agregar Nuevo Servicio”, reemplaza tu bloque de Orden por esto: -->
<div>
  <label>Orden (1–5):</label>
  <input type="number" name="orden" id="nuevo-orden" min="1" max="5" value="1" required>
  <small id="orden-error" style="color: red; display: block; margin-top: 5px;"></small>
</div>

                </div>
                <button type="submit">Agregar Servicio</button>
            </form>
        </div>

        <!-- Lista de servicios existentes -->
        <h3>Servicios Existentes</h3>
        <?php foreach ($servicios as $servicio): ?>
        <div class="servicio-card">
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" value="<?php echo $servicio['id']; ?>">
                <div class="form-grid">
                    <div>
                        <label>Título:</label>
                        <input type="text" name="titulo" value="<?php echo htmlspecialchars($servicio['titulo']); ?>" required>
                    </div>
                    <div>
                        <label>Descripción:</label>
                        <textarea name="descripcion" required><?php echo htmlspecialchars($servicio['descripcion']); ?></textarea>
                    </div>
                    <div>
                        <label>Imagen Actual:</label>
                        <img src="../../imagen/servicios/<?php echo htmlspecialchars($servicio['imagen']); ?>" class="servicio-imagen">
                        <input type="file" name="imagen" accept="image/*">
                        <small>Deja vacío para mantener la imagen actual</small>
                    </div>
                    <div>
                        <label>Orden (1-5):</label>
                        <input type="number" name="orden" class="orden-input" min="1" max="5" value="<?php echo $servicio['orden']; ?>" required>
                        <small class="orden-error" style="color: red; display: block; margin-top: 5px;"></small>
                    </div>
                </div>
                <button type="submit">Actualizar</button>
            </form>
            <form method="POST" style="margin-top: 10px;" id="form-delete-<?php echo $servicio['id']; ?>">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?php echo $servicio['id']; ?>">
                <button type="submit" onclick="return confirmarEliminar(event, 'form-delete-<?php echo $servicio['id']; ?>');" style="background-color: var(--danger-color);">Eliminar</button>
            </form>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Modal -->
    <div id="modal-q" class="modal">
        <div class="modal-content">
            <button class="close" onclick="closeModalQ()" aria-label="Cerrar">×</button>
            <h2 id="modal-q-title"></h2>
            <p id="modal-q-msg"></p>
            <!-- Los botones se agregarán dinámicamente aquí -->
        </div>
    </div>

   <script src="../../modal-q.js"></script>
<script>
    // Función para mostrar botones en el modal de confirmación
    function mostrarBotonesConfirmacion(form) {
        const botonesDiv = document.createElement('div');
        botonesDiv.style.display = 'flex';
        botonesDiv.style.justifyContent = 'center';
        botonesDiv.style.gap = '10px';
        botonesDiv.style.marginTop = '20px';

        const cancelarBtn = document.createElement('button');
        cancelarBtn.textContent = 'Cancelar';
        cancelarBtn.className = 'cancel';
        cancelarBtn.onclick = closeModalQ;

        const eliminarBtn = document.createElement('button');
        eliminarBtn.textContent = 'Eliminar';
        eliminarBtn.className = 'confirm';
        eliminarBtn.onclick = () => eliminarServicio(form);

        botonesDiv.appendChild(cancelarBtn);
        botonesDiv.appendChild(eliminarBtn);

        document.querySelector('.modal-content').appendChild(botonesDiv);
    }

    document.addEventListener('DOMContentLoaded', function() {
        <?php if (isset($_GET['success']) && isset($mensajes[$_GET['success']])): ?>
            showModalQ('<?php echo $mensajes[$_GET['success']]; ?>', false, null, 'Éxito', 'success');
            // cerrar a los 2s si fue éxito
            setTimeout(closeModalQ, 2000);
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            showModalQ('<?php echo htmlspecialchars($_GET['mensaje']); ?>', true, null, 'Error', 'error');
            // cerrar a los 2s si fue error
            setTimeout(closeModalQ, 2000);
        <?php endif; ?>
    });

    function confirmarEliminar(event, form) {
        event.preventDefault();
        showModalQ(
          '¿Estás seguro de que deseas eliminar este servicio? Esta acción no se puede deshacer.',
          false, null, 'Confirmar eliminación', 'default'
        );
        mostrarBotonesConfirmacion(form);
    }

    function eliminarServicio(formId) {
        document.getElementById(formId).submit();
        closeModalQ();
    }
</script>

</body>
</html>

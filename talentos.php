<?php
session_start();
include 'db.php';

// Obtener puestos activos desde la tabla puestos_talento
$puestos = [];
$res_puestos = mysqli_query($conexion, "SELECT puesto FROM puestos_talento ORDER BY puesto ASC");
while ($row = mysqli_fetch_assoc($res_puestos)) {
    $puestos[] = $row['puesto'];
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $email = $_POST['email'];
    $puesto = $_POST['puesto'];
    $telefono = $_POST['telefono'];
    $comentarios = $_POST['comentarios'];
    
    // Maneja el archivo del CV
    $cv = $_FILES['cv'];
    $cv_name = $cv['name'];
    $cv_tmp_name = $cv['tmp_name'];
    $cv_ext = strtolower(pathinfo($cv_name, PATHINFO_EXTENSION));
    $allowed_exts = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];
    if (!in_array($cv_ext, $allowed_exts)) {
        $message = "Solo se permiten archivos PDF, imágenes (JPG, PNG) o Word (.doc, .docx) como CV.";
    } else {
        $cv_path = 'cv/' . $cv_name;
        if (move_uploaded_file($cv_tmp_name, $cv_path)) {
            $insert_query = "INSERT INTO talentos (nombre, apellido, email, puesto, tel, cv_path, comentarios) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($conexion, $insert_query);
            mysqli_stmt_bind_param($stmt, "sssssss", $nombre, $apellido, $email, $puesto, $telefono, $cv_path, $comentarios);
            mysqli_stmt_execute($stmt);

            if (mysqli_stmt_affected_rows($stmt) > 0) {
                mysqli_stmt_close($stmt);
                mysqli_close($conexion);
                header("Location: enviar_correo_talentos.php?email=" . urlencode($email));
                exit();
            } else {
                $message = "Error al procesar tu postulación.";
            }

            mysqli_stmt_close($stmt);
        } else {
            $message = "Error al subir el CV.";
        }
    }
}

// Mostrar mensaje de éxito si viene de enviar_correo_talentos.php
$success = isset($_GET['success']) && $_GET['success'] == '1';

include "header.php";
mysqli_close($conexion);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulario Talentos</title>
    <link rel="stylesheet" href="index.css">
     <style>
        body {
            position: relative;
            background-image: url("../../imagen/nosotros/imagen2.png");
            background-size: cover;
            background-repeat: no-repeat;
        }
         body::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.7);
            z-index: -1;
        }
    </style>
</head>
<body>
    <main>
        <section id="talentos-form">
            <h1 class="color-acento">Postula tu Talento</h1>
            <p style="text-align:center; max-width:600px; margin:0 auto 18px auto; color:#444; font-size:1.1em;">
              Solo se aceptan archivos PDF, imágenes (JPG, PNG) o Word (.doc, .docx) como CV.<br>
              En <b>Comentarios</b> puedes destacar logros, habilidades o motivaciones que te hacen ideal para el puesto, más allá de lo que figura en tu CV.
            </p>
            <?php if (count($puestos) > 0): ?>
            <form id="talentosForm" action="talentos.php" method="post" enctype="multipart/form-data" onsubmit="return validarCV(event)">
                <input type="text" name="nombre" placeholder="Nombre" required>
                <input type="text" name="apellido" placeholder="Apellido" required>
                <input type="email" name="email" placeholder="Email" required>
                <select name="puesto" required>
                  <option value="">Selecciona el puesto al que aspiras</option>
                  <?php foreach($puestos as $p): ?>
                    <option value="<?php echo htmlspecialchars($p); ?>"><?php echo htmlspecialchars($p); ?></option>
                  <?php endforeach; ?>
                </select>
                <input type="text" name="telefono" placeholder="Teléfono" required>
                <textarea name="comentarios" placeholder="Ej: Soy proactivo, tengo experiencia en obras y me apasiona el trabajo en equipo..." rows="3"></textarea>
                <input type="file" name="cv" id="cvInput" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" required>
                <div id="cv-error" style="color:#b00; font-size:0.98em; margin-bottom:8px;"></div>
                <button type="submit">Enviar</button>
            </form>
            <script>
            function validarCV(e) {
                var input = document.getElementById('cvInput');
                var errorDiv = document.getElementById('cv-error');
                var file = input.files[0];
                if (!file) return true;
                var allowed = ['pdf','jpg','jpeg','png','doc','docx'];
                var ext = file.name.split('.').pop().toLowerCase();
                if (allowed.indexOf(ext) === -1) {
                    errorDiv.textContent = 'Solo se permiten archivos PDF, imágenes (JPG, PNG) o Word (.doc, .docx) como CV.';
                    e.preventDefault();
                    return false;
                }
                errorDiv.textContent = '';
                return true;
            }
            document.getElementById('cvInput').addEventListener('change', function() {
                validarCV({preventDefault:function(){}});
            });
            </script>
            <?php if (isset($message) && $message): ?>
            <script>
                document.getElementById('cv-error').textContent = <?php echo json_encode($message); ?>;
            </script>
            <?php endif; ?>
            <?php else: ?>
            <div style="text-align:center; color:#b00; font-weight:bold; margin:30px 0;">Actualmente no hay búsquedas activas de puestos.</div>
            <?php endif; ?>
        </section>
    </main>

    <!-- Modal Q reutilizable -->
    <div id="modal-q">
      <div class="modal-content">
        <h2 id="modal-q-title"></h2>
        <p id="modal-q-msg"></p>
        <button onclick="closeModalQ()">OK</button>
      </div>
    </div>
    <link rel="stylesheet" href="modal-q.css">
    <script src="modal-q.js"></script>
    <script>
    <?php if ($success) { ?>
      showModalQ('Nos pondremos en contacto contigo pronto.', false, 'talentosForm', '¡Postulación Exitosa!');
    <?php } elseif (isset($message)) { ?>
      showModalQ('<?php echo htmlspecialchars($message); ?>', true, null, 'Error en la Postulación');
    <?php } ?>
    </script>
    
    <footer>
        <div class="container">
            <p>&copy;Mat Construcciones</p>
        </div>
    </footer>
</body>
</html>

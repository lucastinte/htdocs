<?php
session_start();
include 'db.php';

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
            <form id="talentosForm" action="talentos.php" method="post" enctype="multipart/form-data">
                <input type="text" name="nombre" placeholder="Nombre" required>
                <input type="text" name="apellido" placeholder="Apellido" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="text" name="puesto" placeholder="Puesto al que aspiras" required>
                <input type="text" name="telefono" placeholder="Teléfono" required>
                <textarea name="comentarios" placeholder="Comentarios adicionales" rows="3"></textarea>
                <input type="file" name="cv" accept=".pdf,.doc,.docx" required>
                <button type="submit">Enviar</button>
            </form>
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

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
        .message {
            text-align: center;
            color: green; /* Cambia esto al color que prefieras para el mensaje de éxito */
        }
        .message.error {
            color: red; /* Cambia esto al color que prefieras para el mensaje de error */
        }
        form {
            text-align: center;
        }
        input[type="text"], input[type="email"], input[type="file"], textarea {
            display: block;
            padding: 10px;
            box-sizing: border-box; /* Para que el padding no afecte el ancho total */
        }
        button {
            padding: 10px 20px;
            font-size: 16px;
        }
        body {
            position: relative;
            background-image: url("./imagen/nosotros/imagen2.png"); 
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
            background-color: rgba(255, 255, 255, 0.7); /* Ajusta la opacidad según lo necesites */
            z-index: -1; /* Asegura que esté detrás del contenido */
        }
    </style>
</head>
<body>
    <main>
        <section id="talentos-form">
            <h1 class="color-acento">Postula tu Talento</h1>
            <?php if ($success) { ?>
                <p class="message">¡Correo enviado exitosamente! Nos pondremos en contacto contigo pronto.</p>
            <?php } ?>
            <?php if (isset($message)) { ?>
                <p class="message <?php echo strpos($message, 'Error') !== false ? 'error' : ''; ?>"><?php echo htmlspecialchars($message); ?></p>
            <?php } ?>

            <form action="talentos.php" method="post" enctype="multipart/form-data">
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

    <footer>
        <div class="container">
            <p>&copy;Mat Construcciones</p>
        </div>
    </footer>
</body>
</html>

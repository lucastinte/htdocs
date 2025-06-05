<?php
include('../../db.php');
session_start();

// Verifica que el usuario esté autenticado
if (!isset($_SESSION['usuario'])) {
    header("Location: ingreso.php");
    exit();
}

// Manejo de la eliminación de talentos
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'eliminar') {
    $id = intval($_POST['id']);

    // Consulta para eliminar el talento
    $query = "DELETE FROM talentos WHERE id = ?";
    $stmt = mysqli_prepare($conexion, $query);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    mysqli_close($conexion);

    // Redirige de vuelta a la vista de gestión de talentos
    header("Location: talentos.php");
    exit();
}

// --- Paginación de talentos ---
$talentos_por_pagina = 5;
$total_talentos_query = "SELECT COUNT(*) as total FROM talentos";
$total_talentos_result = mysqli_query($conexion, $total_talentos_query);
$total_talentos_row = mysqli_fetch_assoc($total_talentos_result);
$total_talentos = $total_talentos_row['total'];
$total_paginas_talentos = ceil($total_talentos / $talentos_por_pagina);
$pagina_actual_talentos = isset($_GET['pagina_talentos']) ? max(1, intval($_GET['pagina_talentos'])) : 1;
$offset_talentos = ($pagina_actual_talentos - 1) * $talentos_por_pagina;

$query_talentos = "SELECT * FROM talentos ORDER BY fecha_postulacion DESC LIMIT $talentos_por_pagina OFFSET $offset_talentos";
$result_talentos = mysqli_query($conexion, $query_talentos);

mysqli_close($conexion);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Talentos</title>
    <link rel="stylesheet" href="usuarioform.css">
    <link rel="stylesheet" href="/modal-q.css">
    <link rel="stylesheet" href="/ingreso/usuario/talentos-modal-q.css">
    <script src="/modal-q.js"></script>
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
        }

        h1 {
            text-align: center;
            color: #333;
            margin-top: 20px;
        }

        table {
            width: 90%;
            margin: 20px auto;
            border-collapse: collapse;
            background-color: #fff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #4CAF50;
            color: white;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        .pagination {
            text-align: center;
            margin: 20px 0;
        }

        .pagination a {
            color: #4CAF50;
            padding: 8px 16px;
            text-decoration: none;
            border: 1px solid #ddd;
            margin: 0 4px;
            border-radius: 4px;
        }

        .pagination a.active {
            background-color: #4CAF50;
            color: white;
            border: 1px solid #4CAF50;
        }

        .pagination a:hover {
            background-color: #ddd;
        }

        .btn-delete {
            background-color: #ff4d4d;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .btn-delete:hover {
            background-color: #e60000;
        }
    </style>
</head>
<body>

<header>
        <div class="container">
             <div class="user-badge">
          <?php if (isset($_SESSION['usuario'])): ?>
           <p class="logo"> <span class="user-icon">&#128100;</span> <?php echo htmlspecialchars($_SESSION['usuario']); ?></p>
          <?php endif; ?>
        </div>
            <nav>
                
                <a href="usuario.php"> <button>Volver</button></a>

            </nav>
        </div>
    </header>

<section id="talentos">
    <h1>Gestión de Talentos</h1>
    <?php if (mysqli_num_rows($result_talentos) == 0) { ?>
        <p>No hay talentos registrados.</p>
    <?php } else { ?>
        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Puesto</th>
                    <th>CV</th>
                    <th>Fecha de Postulación</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result_talentos)) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['nombre']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo htmlspecialchars($row['puesto']); ?></td>
                        <td><a href="/<?php echo htmlspecialchars($row['cv_path']); ?>" download>Descargar CV</a></td>
                        <td><?php echo htmlspecialchars($row['fecha_postulacion']); ?></td>
                        <td>
                            <form action="" method="POST" style="display: inline;" onsubmit="return confirmarEliminarTalento(event, this);">
                                <input type="hidden" name="id" value="<?php echo htmlspecialchars($row['id']); ?>">
                                <input type="hidden" name="action" value="eliminar">
                                <button type="submit" class="btn-delete">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    <?php } ?>

    <!-- Mostrar botones de paginación -->
    <?php if ($total_paginas_talentos > 1) { ?>
        <div class="pagination">
            <?php for ($i = 1; $i <= $total_paginas_talentos; $i++) { ?>
                <a href="talentos.php?pagina_talentos=<?php echo $i; ?>"<?php echo ($i == $pagina_actual_talentos ? ' class="active"' : ''); ?>><?php echo $i; ?></a>
            <?php } ?>
        </div>
    <?php } ?>
</section>

<!-- Modal Q reutilizable -->
<div id="modal-q" style="display:none;position:fixed;z-index:9999;left:0;top:0;width:100vw;height:100vh;background:rgba(20,20,30,0.85);justify-content:center;align-items:center;">
  <div class="modal-content modal-content-talentos">
    <h2 id="modal-q-title"></h2>
    <p id="modal-q-msg"></p>
    <button onclick="closeModalQ()">OK</button>
  </div>
</div>
<script>
function confirmarEliminarTalento(e, form) {
    e.preventDefault();
    showModalQ('¿Estás seguro de que deseas eliminar este talento?', false, null, 'Confirmar Eliminación');
    setTimeout(() => {
        const modal = document.getElementById('modal-q');
        const content = modal.querySelector('.modal-content-talentos');
        let btns = content.querySelectorAll('button');
        btns.forEach(btn => btn.remove());
        // Botón Sí
        const btnSi = document.createElement('button');
        btnSi.textContent = 'Sí';
        btnSi.onclick = function() {
            closeModalQ();
            // Enviar el formulario por AJAX para evitar recarga
            const formData = new FormData(form);
            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (response.redirected) {
                    window.location.href = response.url;
                } else {
                    showModalQ('Talento eliminado exitosamente.', false, null, 'Éxito', 'success');
                    setTimeout(() => {
                        const modal = document.getElementById('modal-q');
                        const content = modal.querySelector('.modal-content-talentos');
                        let btns = content.querySelectorAll('button');
                        btns.forEach(btn => btn.remove());
                        const btnOk = document.createElement('button');
                        btnOk.textContent = 'OK';
                        btnOk.onclick = function() {
                            closeModalQ();
                            window.location.reload();
                        };
                        content.appendChild(btnOk);
                    }, 100);
                }
            });
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
    return false;
}
</script>

</body>
</html>

<?php
include('../../db.php');
session_start();

// Verifica que el usuario esté autenticado
if (!isset($_SESSION['usuario'])) {
    header("Location: ingreso.php");
    exit();
}

// --- Gestión de puestos buscados ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['gestion_puesto'])) {
    $id = isset($_POST['id_puesto']) ? intval($_POST['id_puesto']) : 0;
    $puesto = trim($_POST['puesto']);
    if ($id > 0) {
        // Modificar
        $sql = "UPDATE puestos_talento SET puesto=? WHERE id=?";
        $stmt = mysqli_prepare($conexion, $sql);
        mysqli_stmt_bind_param($stmt, "si", $puesto, $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    } else {
        // Agregar
        $sql = "INSERT INTO puestos_talento (puesto) VALUES (?)";
        $stmt = mysqli_prepare($conexion, $sql);
        mysqli_stmt_bind_param($stmt, "s", $puesto);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    header('Location: talentos.php');
    exit();
}
if (isset($_GET['borrar_puesto'])) {
    $id = intval($_GET['borrar_puesto']);
    mysqli_query($conexion, "DELETE FROM puestos_talento WHERE id=$id");
    header('Location: talentos.php');
    exit();
}
$puestos = mysqli_query($conexion, "SELECT * FROM puestos_talento ORDER BY puesto ASC");

// --- Manejo de la eliminación de talentos ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'eliminar') {
    $id = intval($_POST['id']);
    $query = "DELETE FROM talentos WHERE id = ?";
    $stmt = mysqli_prepare($conexion, $query);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    mysqli_close($conexion);
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
            font-family: 'Roboto', Arial, sans-serif;
            background: #f7f7fa;
            margin: 0;
            padding: 0;
        }
        header {
            background: #22223b;
            color: #fff;
            padding: 18px 0 10px 0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        }
        .container {
            max-width: 1100px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .logo {
            font-weight: 700;
            font-size: 1.2em;
            color: #fff;
        }
        nav button {
            background: #4CAF50;
            color: #fff;
            border: none;
            border-radius: 6px;
            padding: 8px 18px;
            font-size: 1em;
            cursor: pointer;
            transition: background 0.2s;
        }
        nav button:hover {
            background: #388e3c;
        }
        .panel-puestos {
            max-width: 900px;
            margin: 40px auto 20px auto;
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.10);
            padding: 32px 24px;
        }
        .panel-puestos h2 {
            color: #4CAF50;
            text-align: center;
            margin-bottom: 18px;
            font-size: 1.5em;
            letter-spacing: 1px;
        }
        .tabla-homogenea {
            width: 100%;
            border-collapse: collapse;
            margin-top: 18px;
            background: #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        }
        .tabla-homogenea th, .tabla-homogenea td {
            padding: 12px 10px;
            border-bottom: 1px solid #e0e0e0;
            font-size: 1em;
        }
        .tabla-homogenea th {
            background: #4CAF50;
            color: #fff;
            font-weight: 600;
        }
        .tabla-homogenea tr:nth-child(even) {
            background: #f6f6fb;
        }
        .tabla-homogenea tr:hover {
            background: #f1f1f1;
        }
        .acciones {
            display: flex;
            gap: 8px;
        }
        .acciones button,
        .acciones input[type="submit"] {
            background: #7c3aed;
            color: #fff;
            border: none;
            border-radius: 5px;
            padding: 5px 12px;
            font-size: 0.98em;
            cursor: pointer;
            transition: background 0.2s;
        }
        .acciones button:hover,
        .acciones input[type="submit"]:hover {
            background: #5b21b6;
        }
        .form-agregar {
            margin: 24px 0 0 0;
            padding: 18px;
            background: #f3f0ff;
            border-radius: 12px;
        }
        .form-agregar input[type="text"],
        .form-agregar input[type="number"] {
            margin-bottom: 10px;
            padding: 10px;
            border-radius: 6px;
            border: 1px solid #c7d2fe;
            font-size: 1em;
            background: #fff;
        }
        .form-agregar .botones-form {
            display: flex;
            gap: 12px;
            justify-content: center;
            margin-top: 8px;
        }
        .form-agregar input[type="submit"],
        .form-agregar button[type="button"] {
            width: auto;
            min-width: 100px;
            margin-right: 0;
            background: #4CAF50;
            color: #fff;
            border: none;
            border-radius: 6px;
            padding: 8px 18px;
            font-size: 1em;
            cursor: pointer;
            transition: background 0.2s;
        }
        .form-agregar input[type="submit"]:hover,
        .form-agregar button[type="button"]:hover {
            background: #388e3c;
        }
        #talentos h1 {
            color: #4CAF50;
            margin-top: 40px;
            font-size: 2em;
            text-align: center;
        }
        #talentos table.tabla-homogenea {
            width: 95%;
            margin: 24px auto 0 auto;
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
        @media (max-width: 900px) {
            .container, .panel-puestos, #talentos table.tabla-homogenea { width: 98% !important; }
            #talentos table.tabla-homogenea, .panel-puestos table.tabla-homogenea { font-size: 0.95em; }
        }
        @media (max-width: 600px) {
            .container, .panel-puestos, #talentos table.tabla-homogenea { width: 100% !important; }
            .panel-puestos, #talentos table.tabla-homogenea { padding: 0; }
            .panel-puestos { box-shadow: none; border-radius: 0; }
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

<div class="panel-puestos">
    <h2>Puestos Buscados</h2>
    <table class="tabla-homogenea">
        <tr>
            <th>Puesto</th>
            <th>Acciones</th>
        </tr>
        <?php while($row = mysqli_fetch_assoc($puestos)): ?>
        <tr>
            <td><?php echo htmlspecialchars($row['puesto']); ?></td>
            <td class="acciones">
                <button onclick='editarPuesto(<?php echo json_encode($row); ?>)'>Editar</button>
                <form method="get" style="display:inline;">
                    <input type="hidden" name="borrar_puesto" value="<?php echo $row['id']; ?>">
                    <input type="submit" value="Borrar" onclick="return confirm('¿Seguro que deseas borrar este puesto?');">
                </form>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
    <form class="form-agregar" id="formAgregarPuesto" method="post">
        <input type="hidden" name="id_puesto" id="puesto_id">
        <input type="hidden" name="gestion_puesto" value="1">
        <h3 id="formTituloPuesto">Agregar Puesto</h3>
        <input type="text" name="puesto" id="puesto" placeholder="Puesto (ej: Secretaria)" required>
        <div class="botones-form">
            <input type="submit" value="Guardar">
            <button type="button" onclick="limpiarFormPuesto()">Limpiar</button>
        </div>
    </form>
</div>

<section id="talentos">
    <h1>Gestión de Talentos</h1>
    <?php if (mysqli_num_rows($result_talentos) == 0) { ?>
        <p>No hay talentos registrados.</p>
    <?php } else { ?>
        <table class="tabla-homogenea">
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

<script>
function editarPuesto(data) {
    document.getElementById('puesto_id').value = data.id;
    document.getElementById('puesto').value = data.puesto;
    document.getElementById('formTituloPuesto').textContent = 'Modificar Puesto';
}
function limpiarFormPuesto() {
    document.getElementById('puesto_id').value = '';
    document.getElementById('puesto').value = '';
    document.getElementById('formTituloPuesto').textContent = 'Agregar Puesto';
}

// --- Confirmación de eliminación de talento ---
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

<!-- Modal Q reutilizable -->
<div id="modal-q" style="display:none;position:fixed;z-index:9999;left:0;top:0;width:100vw;height:100vh;background:rgba(20,20,30,0.85);justify-content:center;align-items:center;">
  <div class="modal-content modal-content-talentos">
    <h2 id="modal-q-title"></h2>
    <p id="modal-q-msg"></p>
    <button onclick="closeModalQ()">OK</button>
  </div>
</div>
<script>
// ...confirmarEliminarTalento existente...
</script>
</body>
</html>

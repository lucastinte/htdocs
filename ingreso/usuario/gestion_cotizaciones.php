<?php
session_start();
include '../../db.php';

// Verificación de sesión
if (!isset($_SESSION['usuario'])) {
    header("Location: ../ingreso.php");
    exit();
}

// Manejo de Configuración de m2
if (isset($_POST['update_config'])) {
    $m2_unifamiliar = $_POST['m2_unifamiliar'];
    $m2_colectiva = $_POST['m2_colectiva'];
    $m2_quincho = $_POST['m2_quincho'];
    $porcentaje_mo_base = $_POST['porcentaje_mo_base'];

    $sql = "UPDATE cotizacion_config SET valor_unifamiliar = ?, valor_colectiva = ?, valor_quincho = ?, porcentaje_mo = ? WHERE clave = 'm2_base'";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "dddd", $m2_unifamiliar, $m2_colectiva, $m2_quincho, $porcentaje_mo_base);
    mysqli_stmt_execute($stmt);
}

// Manejo de Ítems (Agregar/Editar/Eliminar)
if (isset($_POST['action'])) {
    if ($_POST['action'] == 'add') {
        $item_num = $_POST['item_num'];
        $descripcion = $_POST['descripcion'];
        $unidad = $_POST['unidad'];
        $cantidad = $_POST['cantidad'];
        $precio_unitario = $_POST['precio_unitario'];
        $porcentaje_mo = $_POST['porcentaje_mo'];

        $sql = "INSERT INTO cotizacion_items (item_num, descripcion, unidad, cantidad, precio_unitario, porcentaje_mo, orden) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conexion, $sql);
        mysqli_stmt_bind_param($stmt, "isssddi", $item_num, $descripcion, $unidad, $cantidad, $precio_unitario, $porcentaje_mo, $orden);
        mysqli_stmt_execute($stmt);
    } elseif ($_POST['action'] == 'edit') {
        $id = $_POST['id'];
        $item_num = $_POST['item_num'];
        $descripcion = $_POST['descripcion'];
        $unidad = $_POST['unidad'];
        $cantidad = $_POST['cantidad'];
        $precio_unitario = $_POST['precio_unitario'];
        $porcentaje_mo = $_POST['porcentaje_mo'];
        $orden = $_POST['orden'];

        $sql = "UPDATE cotizacion_items SET item_num = ?, descripcion = ?, unidad = ?, cantidad = ?, precio_unitario = ?, porcentaje_mo = ?, orden = ? WHERE id = ?";
        $stmt = mysqli_prepare($conexion, $sql);
        mysqli_stmt_bind_param($stmt, "isssddii", $item_num, $descripcion, $unidad, $cantidad, $precio_unitario, $porcentaje_mo, $orden, $id);
        mysqli_stmt_execute($stmt);
    } elseif ($_POST['action'] == 'delete') {
        $id = $_POST['id'];
        $sql = "DELETE FROM cotizacion_items WHERE id = ?";
        $stmt = mysqli_prepare($conexion, $sql);
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
    }
}

// Obtener configuración
$sql = "SELECT * FROM cotizacion_config WHERE clave = 'm2_base'";
$res = mysqli_query($conexion, $sql);
$config = mysqli_fetch_assoc($res);

// Obtener ítems
$sql = "SELECT * FROM cotizacion_items ORDER BY orden ASC";
$res_items = mysqli_query($conexion, $sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Cotizaciones</title>
    <link rel="stylesheet" href="usuarioform.css">
    <style>
        .management-section {
            max-width: 1200px;
            margin: 30px auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .config-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        .config-item label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }
        .config-item input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .btn-save {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
        }
        .btn-save:hover {
            background-color: #45a049;
        }
        .btn-add {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-bottom: 20px;
            font-weight: bold;
        }
        .btn-edit {
            background-color: #4CAF50;
            color: white;
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn-delete {
            background-color: #ff4d4d;
            color: white;
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.4);
            justify-content: center;
            align-items: center;
        }
        .modal-content {
            background-color: #fefefe;
            padding: 20px;
            border: 1px solid #888;
            width: 100%;
            max-width: 500px;
            border-radius: 8px;
        }
        .form-group-modal {
            margin-bottom: 15px;
        }
        .form-group-modal label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group-modal input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .modal-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        .modal-actions button {
            flex: 1;
            padding: 10px;
            border-radius: 4px;
            cursor: pointer;
            border: none;
            font-weight: bold;
        }
        .btn-cancel {
            background-color: #6c757d;
            color: white;
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
                <a href="usuario.php"><button>Volver</button></a>
            </nav>
        </div>
    </header>

    <main class="management-section">
        <section>
            <h2>Configuración de Precios Base (m²)</h2>
            <form method="POST">
                <div class="config-grid">
                    <div class="config-item">
                        <label>Unifamiliar ($)</label>
                        <input type="number" step="0.01" name="m2_unifamiliar" value="<?php echo $config['valor_unifamiliar']; ?>">
                    </div>
                    <div class="config-item">
                        <label>Colectiva ($)</label>
                        <input type="number" step="0.01" name="m2_colectiva" value="<?php echo $config['valor_colectiva']; ?>">
                    </div>
                    <div class="config-item">
                        <label>Quincho ($)</label>
                        <input type="number" step="0.01" name="m2_quincho" value="<?php echo $config['valor_quincho']; ?>">
                    </div>
                    <div class="config-item">
                        <label>Mano de Obra (%) - Base</label>
                        <input type="number" step="0.01" name="porcentaje_mo_base" value="<?php echo $config['porcentaje_mo'] ?? 0; ?>">
                    </div>
                    <div class="config-item" style="display: flex; align-items: flex-end;">
                        <button type="submit" name="update_config" class="btn-save" style="width: 100%;">Guardar Precios</button>
                    </div>
                </div>
            </form>
        </section>

        <section style="margin-top: 40px;">
            <h2>Ítems de Referencia</h2>
            <button class="btn-add" onclick="showModal('add')">AGREGAR NUEVO ÍTEM</button>
            
            <table>
                <thead>
                    <tr>
                        <th>Orden</th>
                        <th># Item</th>
                        <th>Descripción</th>
                        <th>Unidad</th>
                        <th>Cantidad</th>
                        <th>Precio Unitario</th>
                        <th>% M.O.</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($item = mysqli_fetch_assoc($res_items)): ?>
                    <tr>
                        <td><?php echo $item['orden']; ?></td>
                        <td><?php echo $item['item_num']; ?></td>
                        <td><?php echo $item['descripcion']; ?></td>
                        <td><?php echo $item['unidad']; ?></td>
                        <td><?php echo $item['cantidad']; ?></td>
                        <td>$<?php echo number_format($item['precio_unitario'], 2, ',', '.'); ?></td>
                        <td><?php echo number_format($item['porcentaje_mo'] ?? 0, 2); ?>%</td>
                        <td style="display: flex; gap: 5px; justify-content: center;">
                            <button class="btn-edit" onclick='showModal("edit", <?php echo json_encode($item); ?>)'>Editar</button>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('¿Eliminar ítem?')">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                                <button type="submit" class="btn-delete">Borrar</button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </section>
    </main>

    <!-- Modal Form -->
    <div id="itemModal" class="modal">
        <div class="modal-content">
            <h3 id="modalTitle">Nuevo Ítem</h3>
            <form id="itemForm" method="POST">
                <input type="hidden" name="action" id="formAction" value="add">
                <input type="hidden" name="id" id="itemId">
                
                <div class="form-group-modal">
                    <label>Orden de Visualización</label>
                    <input type="number" name="orden" id="itemOrden" placeholder="Ej: 1" required>
                </div>
                
                <div class="form-group-modal">
                    <label># de Ítem</label>
                    <input type="number" name="item_num" id="itemNum" placeholder="Ej: 10" required>
                </div>
                
                <div class="form-group-modal">
                    <label>Descripción del Servicio</label>
                    <input type="text" name="descripcion" id="itemDesc" placeholder="Nombre del ítem..." required>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group-modal">
                        <label>Unidad</label>
                        <input type="text" name="unidad" id="itemUnid" placeholder="Global, Un, m²..." required>
                    </div>
                    
                    <div class="form-group-modal">
                        <label>Cantidad</label>
                        <input type="number" step="0.01" name="cantidad" id="itemCant" placeholder="1" required>
                    </div>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group-modal">
                        <label>Precio Unitario ($)</label>
                        <input type="number" step="0.01" name="precio_unitario" id="itemPrice" placeholder="0.00" required>
                    </div>
                    <div class="form-group-modal">
                        <label>Mano de Obra (%)</label>
                        <input type="number" step="0.01" name="porcentaje_mo" id="itemMO" placeholder="Ej: 10" required>
                    </div>
                </div>
                
                <div class="modal-actions">
                    <button type="submit" class="btn-save">Guardar Ítem</button>
                    <button type="button" class="btn-cancel" onclick="closeModal()">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function showModal(action, item = null) {
            document.getElementById('itemModal').style.display = 'flex';
            document.getElementById('formAction').value = action;
            if (action === 'edit') {
                document.getElementById('modalTitle').innerText = 'Editar Ítem';
                document.getElementById('itemId').value = item.id;
                document.getElementById('itemNum').value = item.item_num;
                document.getElementById('itemDesc').value = item.descripcion;
                document.getElementById('itemUnid').value = item.unidad;
                document.getElementById('itemCant').value = item.cantidad;
                document.getElementById('itemPrice').value = item.precio_unitario;
                document.getElementById('itemMO').value = item.porcentaje_mo;
                document.getElementById('itemOrden').value = item.orden;
            } else {
                document.getElementById('modalTitle').innerText = 'Nuevo Ítem';
                document.getElementById('itemForm').reset();
            }
        }
        function closeModal() {
            document.getElementById('itemModal').style.display = 'none';
        }
    </script>
</body>
</html>

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
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: #f5f5f5;
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding-bottom: 50px;
        }
        
        header {
            background: #2d3748 !important;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            font-size: 1.5em;
            font-weight: 700;
            color: white;
        }
        
        header button {
            background: #e53e3e;
            color: white;
            border: none;
            padding: 12px 28px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        header button:hover {
            background: #c53030;
            transform: translateY(-1px);
        }
        
        .management-container {
            max-width: 1200px;
            margin: 40px auto;
            background: white;
            padding: 50px;
            border-radius: 16px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        }
        
        h2 {
            color: #2d3748;
            margin-bottom: 35px;
            font-size: 2em;
            font-weight: 700;
            border-bottom: 3px solid #8a2be2;
            padding-bottom: 15px;
        }
        
        .config-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 25px;
            margin-bottom: 50px;
            padding: 35px;
            background: #f9fafb;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
        }
        
        .config-item label {
            display: block;
            margin-bottom: 10px;
            font-weight: 700;
            color: #4a5568;
            font-size: 0.85em;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .config-item input {
            width: 100%;
            padding: 14px 18px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            transition: all 0.3s ease;
            font-size: 1.05em;
            font-weight: 600;
            background: white;
            color: #2d3748;
        }
        
        .config-item input:focus {
            border-color: #8a2be2;
            outline: none;
            box-shadow: 0 0 0 3px rgba(138, 43, 226, 0.1);
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        
        thead {
            background: #2d3748;
        }
        
        th {
            color: white;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.75em;
            letter-spacing: 1px;
            padding: 18px 16px;
            text-align: center;
        }
        
        tbody tr {
            background: white;
            transition: background 0.2s ease;
        }
        
        tbody tr:nth-child(even) {
            background: #f9fafb;
        }
        
        tbody tr:hover {
            background: #f3f4f6;
        }
        
        td {
            padding: 18px 16px;
            border-bottom: 1px solid #e5e7eb;
            color: #2d3748;
            font-weight: 500;
            text-align: center;
        }
        
        tbody tr:last-child td {
            border-bottom: none;
        }
        
        .btn-add {
            background: #8a2be2;
            color: white;
            padding: 14px 28px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            margin-bottom: 30px;
            font-weight: 700;
            font-size: 0.95em;
            box-shadow: 0 4px 12px rgba(138, 43, 226, 0.3);
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .btn-add:hover {
            background: #7928ca;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(138, 43, 226, 0.4);
        }
        
        .actions {
            display: flex;
            gap: 10px;
            justify-content: center;
        }
        
        .actions button {
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 700;
            transition: all 0.2s ease;
            padding: 10px 18px;
            font-size: 0.8em;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        
        .btn-edit {
            background: #ffc107;
            color: #7c5e00;
        }
        
        .btn-edit:hover {
            background: #e0a800;
            transform: translateY(-1px);
        }
        
        .btn-delete {
            background: #e53e3e;
            color: white;
        }
        
        .btn-delete:hover {
            background: #c53030;
            transform: translateY(-1px);
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 2000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(4px);
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .modal-content {
            background: white;
            padding: 45px;
            border-radius: 12px;
            width: 100%;
            max-width: 550px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: modalFadeIn 0.3s ease-out;
        }
        
        @keyframes modalFadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .modal-content h3 {
            color: #2d3748;
            margin-top: 0;
            margin-bottom: 30px;
            font-size: 1.8em;
            font-weight: 700;
            text-align: center;
            border-bottom: 3px solid #8a2be2;
            padding-bottom: 12px;
        }
        
        .form-group-modal {
            margin-bottom: 22px;
        }
        
        .form-group-modal label {
            display: block;
            margin-bottom: 8px;
            font-weight: 700;
            color: #4a5568;
            font-size: 0.85em;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .modal-content input,
        .modal-content select {
            width: 100%;
            padding: 14px 18px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 1em;
            transition: all 0.3s ease;
            background: #f9fafb;
            color: #2d3748;
            font-weight: 500;
        }
        
        .modal-content input:focus,
        .modal-content select:focus {
            outline: none;
            border-color: #8a2be2;
            box-shadow: 0 0 0 3px rgba(138, 43, 226, 0.1);
            background: white;
        }
        
        .modal-actions {
            display: flex;
            gap: 15px;
            margin-top: 35px;
        }
        
        .modal-actions button {
            flex: 1;
            padding: 16px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 1em;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .btn-save {
            background: #8a2be2;
            color: white;
            box-shadow: 0 4px 12px rgba(138, 43, 226, 0.3);
        }
        
        .btn-save:hover {
            background: #7928ca;
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(138, 43, 226, 0.4);
        }
        
        .btn-cancel {
            background: #e2e8f0;
            color: #4a5568;
        }
        
        .btn-cancel:hover {
            background: #cbd5e0;
            color: #2d3748;
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <p class="logo">Panel de Control - Cotizaciones</p>
            <nav>
                <a href="usuario.php"><button>Volver</button></a>
            </nav>
        </div>
    </header>

    <div class="management-container">
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
                    <button type="submit" name="update_config" class="btn-save" style="width: 100%; font-size: 0.9em; padding: 12px; border-radius: 12px;">Guardar Precios</button>
                </div>
            </div>
        </form>

        <h2>Ítems de Referencia</h2>
        <button class="btn-add" onclick="showModal('add')">Agregar Nuevo Ítem</button>
        
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
                    <td class="actions">
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
    </div>

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

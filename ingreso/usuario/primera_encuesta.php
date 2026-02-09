<?php
include('../../db.php');
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: ingreso.php");
    exit();
}

if (isset($_GET['id'])) {
    $id_presupuesto = intval($_GET['id']);
} else {
    echo "ID de presupuesto no especificado.";
    exit();
}

// Obtener datos de la primera encuesta (tabla presupuestos)
$query = "SELECT * FROM presupuestos WHERE id = ?";
$stmt = mysqli_prepare($conexion, $query);
mysqli_stmt_bind_param($stmt, "i", $id_presupuesto);
mysqli_stmt_execute($stmt);
$result = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$result) {
    echo "No se encontró el presupuesto.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Primera Encuesta - Datos Personales</title>
    <link rel="stylesheet" href="premium_forms.css">
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #8a2be2;
            color: white;
            font-weight: bold;
        }
        tr:hover {
            background-color: #f5f5f5;
        }
        .btn-primary {
            background: linear-gradient(135deg, #8a2be2, #a855f7);
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 20px;
            text-decoration: none;
            display: inline-block;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(138, 43, 226, 0.3);
        }
    </style>
</head>
<body>
<header>
    <div class="container">
        <nav><a href="presupuestos.php"><button>Volver</button></a></nav>
    </div>
</header>
<section id="presupuestos">
    <h2>Primera Encuesta - Datos Personales y Preferencias</h2>
    
    <table>
        <tr>
            <th>Característica</th>
            <th>Detalle</th>
        </tr>
        <tr>
            <td><strong>Nombre</strong></td>
            <td><?php echo htmlspecialchars($result['nombre'] ?? ''); ?></td>
        </tr>
        <tr>
            <td><strong>Email</strong></td>
            <td><?php echo htmlspecialchars($result['email'] ?? ''); ?></td>
        </tr>
        <tr>
            <td><strong>Teléfono</strong></td>
            <td><?php echo htmlspecialchars($result['telefono'] ?? ''); ?></td>
        </tr>
        <tr>
            <td><strong>Dirección</strong></td>
            <td><?php echo htmlspecialchars($result['direccion'] ?? ''); ?></td>
        </tr>
        <tr>
            <td><strong>Metros Cuadrados</strong></td>
            <td><?php echo htmlspecialchars($result['m2_cantidad'] ?? ''); ?> m²</td>
        </tr>
        <tr>
            <td><strong>Tipo de Proyecto</strong></td>
            <td><?php echo htmlspecialchars(ucfirst($result['tipo_proyecto'] ?? '')); ?></td>
        </tr>
        <tr>
            <td><strong>Ocupación</strong></td>
            <td><?php echo htmlspecialchars($result['ocupacion'] ?? 'No especificado'); ?></td>
        </tr>
        <tr>
            <td><strong>Habitantes</strong></td>
            <td><?php echo htmlspecialchars($result['habitantes'] ?? 'No especificado'); ?></td>
        </tr>
        <tr>
            <td><strong>Seguridad / Permanencia en casa</strong></td>
            <td><?php echo htmlspecialchars($result['seguridad'] ?? 'No especificado'); ?></td>
        </tr>
        <tr>
            <td><strong>Trabajo en casa</strong></td>
            <td><?php echo htmlspecialchars($result['trabajo_en_casa'] ?? 'No especificado'); ?></td>
        </tr>
        <tr>
            <td><strong>Salud / Discapacidades</strong></td>
            <td><?php echo htmlspecialchars($result['salud'] ?? 'No especificado'); ?></td>
        </tr>
        <tr>
            <td><strong>Fobias</strong></td>
            <td><?php echo htmlspecialchars($result['fobias'] ?? 'No especificado'); ?></td>
        </tr>
        <tr>
            <td><strong>Intereses y molestias</strong></td>
            <td><?php echo htmlspecialchars($result['intereses'] ?? 'No especificado'); ?></td>
        </tr>
        <tr>
            <td><strong>Rutinas diarias</strong></td>
            <td><?php echo htmlspecialchars($result['rutinas'] ?? 'No especificado'); ?></td>
        </tr>
        <tr>
            <td><strong>Pasatiempos</strong></td>
            <td><?php echo htmlspecialchars($result['pasatiempos'] ?? 'No especificado'); ?></td>
        </tr>
        <tr>
            <td><strong>Visitas</strong></td>
            <td><?php echo htmlspecialchars($result['visitas'] ?? 'No especificado'); ?></td>
        </tr>
        <tr>
            <td><strong>Detalles de visitas</strong></td>
            <td><?php echo htmlspecialchars($result['detalles_visitas'] ?? 'No especificado'); ?></td>
        </tr>
        <tr>
            <td><strong>Vehículos</strong></td>
            <td><?php echo htmlspecialchars($result['vehiculos'] ?? 'No especificado'); ?></td>
        </tr>
        <tr>
            <td><strong>Mascotas</strong></td>
            <td><?php echo htmlspecialchars($result['mascotas'] ?? 'No especificado'); ?></td>
        </tr>
        <tr>
            <td><strong>Aprendizaje</strong></td>
            <td><?php echo htmlspecialchars($result['aprendizaje'] ?? 'No especificado'); ?></td>
        </tr>
        <tr>
            <td><strong>Negocio</strong></td>
            <td><?php echo htmlspecialchars($result['negocio'] ?? 'No especificado'); ?></td>
        </tr>
        <tr>
            <td><strong>Muebles especiales</strong></td>
            <td><?php echo htmlspecialchars($result['muebles'] ?? 'No especificado'); ?></td>
        </tr>
        <tr>
            <td><strong>Detalles de la casa</strong></td>
            <td><?php echo htmlspecialchars($result['detalles_casa'] ?? 'No especificado'); ?></td>
        </tr>
    </table>

    <a href="segunda_encuesta.php?id=<?php echo $id_presupuesto; ?>" class="btn-primary">Siguiente: Ambientes →</a>
</section>
</body>
</html>

<?php
include('../../../db.php');
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario'])) {
    echo json_encode(['status' => 'error', 'message' => 'No estás autenticado.']);
    exit();
}

// Verificar si se envió el ID del proyecto
if (isset($_POST['id'])) {
    $id_proyecto = $_POST['id'];

    // Eliminar el proyecto de la base de datos
    $query = "DELETE FROM proyectos WHERE id = ?";
    $stmt = mysqli_prepare($conexion, $query);
    mysqli_stmt_bind_param($stmt, "i", $id_proyecto);

    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['status' => 'success', 'message' => 'Proyecto eliminado correctamente.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error al eliminar el proyecto.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'ID de proyecto no proporcionado.']);
}
exit();
?>

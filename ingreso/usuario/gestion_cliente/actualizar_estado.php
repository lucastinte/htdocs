<?php
include('../../../db.php');
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_proyecto = $_POST['id_proyecto'];
    $estado = $_POST['estado'];

    $query = "UPDATE proyectos SET estado = ? WHERE id = ?";
    $stmt = mysqli_prepare($conexion, $query);
    mysqli_stmt_bind_param($stmt, "ii", $estado, $id_proyecto);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: proyectos.php");
        exit();
    } else {
        echo "Error al actualizar el estado: " . mysqli_error($conexion);
    }
}
?>

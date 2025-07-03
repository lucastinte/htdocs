<?php
$password = '';

if (file_exists(__DIR__ . '/.env')) {
    $env = parse_ini_file(__DIR__ . '/.env');
    $password = $env['DB_PASS'];
}
$host = 'srv1999.hstgr.io';
$user = 'u917025056_mat';
$dbname = 'u917025056_mat';
$conexion = new mysqli($host, $user, $password, $dbname);

if ($conexion->connect_error) {
    die('Error de conexión: ' . $conexion->connect_error);
}
function getUserData($email) {
    global $conexion;
    $stmt = $conexion->prepare("SELECT * FROM clientes WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function updateUserData($email, $apellido, $nombre, $dni, $caracteristica_tel, $numero_tel, $direccion, $fecha_nacimiento, $localidad, $provincia) {
    global $conexion;
    $stmt = $conexion->prepare("UPDATE clientes SET apellido = ?, nombre = ?, dni = ?, caracteristica_tel = ?, numero_tel = ?, direccion = ?, fecha_nacimiento = ?, localidad = ?, provincia = ? WHERE email = ?");
    $stmt->bind_param("ssssssssss", $apellido, $nombre, $dni, $caracteristica_tel, $numero_tel, $direccion, $fecha_nacimiento, $localidad, $provincia, $email);
    return $stmt->execute();
}
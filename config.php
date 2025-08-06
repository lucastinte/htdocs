<?php
$smtp_password = '';

if (file_exists(__DIR__ . '/.env')) {
    $env = parse_ini_file(__DIR__ . '/.env');
    $smtp_password = $env['SMTP_PASSWORD'];
}

return [
    'smtp_host'     => 'smtp.gmail.com',
    'smtp_port'     => 587,
    'smtp_encryption' => 'tls', // o 'ssl' si usás el puerto 465
    'smtp_username' => 'durandamian523@gmail.com',
    'smtp_password' => $smtp_password,
    'from_email'    => 'durandamian523@gmail.com',
    'from_name'     => 'Mat Construcciones',
    'base_url'      => 'localhost',
    'base_port'     => '8080',
];

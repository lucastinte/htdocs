<?php
$smtp_password = '';

if (file_exists(__DIR__ . '/.env')) {
    $env = parse_ini_file(__DIR__ . '/.env');
    $smtp_password = $env['SMTP_PASSWORD'];
}

return [
    'smtp_username' => 'damian@matconstrucciones.store',
    'smtp_password' => $smtp_password,
    'from_email'    => 'damian@matconstrucciones.store',
    'from_name'     => 'Mat Construcciones',
    'base_url'      => 'localhost',
    'base_port'     => '8080',
];

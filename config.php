<?php
// Prioridad a variables de entorno (Vercel), luego al archivo .env (Local)
$smtp_password = getenv('SMTP_PASSWORD');

if (!$smtp_password && file_exists(__DIR__ . '/.env')) {
    $env = parse_ini_file(__DIR__ . '/.env');
    $smtp_password = $env['SMTP_PASSWORD'] ?? '';
}

return [
    'smtp_host'       => getenv('SMTP_HOST') ?: 'smtp.gmail.com',
    'smtp_port'       => getenv('SMTP_PORT') ?: 587,
    'smtp_encryption' => getenv('SMTP_ENCRYPTION') ?: 'tls',
    'smtp_username'   => getenv('SMTP_USERNAME') ?: 'durandamian523@gmail.com',
    'smtp_password'   => $smtp_password,
    'from_email'      => getenv('FROM_EMAIL') ?: 'durandamian523@gmail.com',
    'from_name'       => getenv('FROM_NAME') ?: 'Mat Construcciones',
    'base_url'        => getenv('BASE_URL') ?: 'localhost',
    'base_port'       => getenv('BASE_PORT') ?: '8080',
];

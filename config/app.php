<?php
/**
 * Configuración de la aplicación
 */

return [
    'app' => [
        'name' => 'Gestión Académica',
        'version' => '1.0.0',
        'debug' => true,
        'timezone' => 'America/La_Paz'
    ],
    
    'database' => [
        'host' => $_ENV['DB_HOST'] ?? 'localhost',
        'port' => $_ENV['DB_PORT'] ?? '5436',
        'name' => $_ENV['DB_NAME'] ?? 'gestion_academica',
        'user' => $_ENV['DB_USER'] ?? 'gestion_user',
        'password' => $_ENV['DB_PASSWORD'] ?? 'gestion_password'
    ],
    
    'paths' => [
        'app' => __DIR__ . '/../app',
        'public' => __DIR__ . '/../public',
        'views' => __DIR__ . '/../app/views',
        'logs' => __DIR__ . '/../logs'
    ]
];

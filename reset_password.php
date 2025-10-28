<?php
require_once __DIR__ . '/app/models/Database.php';

$db = Database::getInstance();

// Resetear contraseña de usuario por ID
$userId = isset($argv[1]) ? $argv[1] : null;
$newPassword = isset($argv[2]) ? $argv[2] : 'password123';

if (!$userId) {
    echo "Uso: php reset_password.php <user_id> [new_password]\n";
    echo "Ejemplo: php reset_password.php 5 password123\n";
    exit(1);
}

try {
    $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
    
    $result = $db->query(
        "UPDATE usuarios SET password_hash = :password WHERE id = :id",
        [':password' => $hashedPassword, ':id' => $userId]
    );
    
    $user = $db->query("SELECT nombre, apellido, email FROM usuarios WHERE id = :id", [':id' => $userId]);
    
    if ($result && !empty($user)) {
        echo "✓ Contraseña actualizada exitosamente\n";
        echo "Usuario: {$user[0]['nombre']} {$user[0]['apellido']}\n";
        echo "Email: {$user[0]['email']}\n";
        echo "Nueva contraseña: $newPassword\n";
    } else {
        echo "✗ Error al actualizar la contraseña\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

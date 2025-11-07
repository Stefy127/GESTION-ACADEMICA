<?php
/**
 * Script temporal para agregar la columna password_changed a la tabla usuarios
 * Ejecutar una vez desde el navegador o línea de comandos
 */

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/app/core/Database.php';

try {
    $db = Database::getInstance();
    
    // Verificar si la columna password_changed existe
    $checkSql = "SELECT COUNT(*) as total 
                 FROM information_schema.columns 
                 WHERE table_name = 'usuarios' 
                 AND column_name = 'password_changed'";
    $result = $db->query($checkSql);
    
    if (($result[0]['total'] ?? 0) == 0) {
        // Agregar la columna password_changed
        $db->query("ALTER TABLE usuarios ADD COLUMN password_changed BOOLEAN DEFAULT false");
        
        // Marcar como true para usuarios existentes (ya han usado el sistema)
        $db->query("UPDATE usuarios SET password_changed = true WHERE password_changed = false");
        
        echo "Columna password_changed agregada exitosamente.\n";
        echo "Usuarios existentes marcados como que ya han cambiado su contraseña.\n";
    } else {
        echo "La columna password_changed ya existe.\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}


<?php
/**
 * Script temporal para agregar la columna hora_marcacion a la tabla asistencia_docente
 * Ejecutar una vez desde el navegador o línea de comandos
 */

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/app/core/Database.php';

try {
    $db = Database::getInstance();
    
    // Verificar si la columna existe
    $checkSql = "SELECT COUNT(*) as total 
                 FROM information_schema.columns 
                 WHERE table_name = 'asistencia_docente' 
                 AND column_name = 'hora_marcacion'";
    $result = $db->query($checkSql);
    
    if (($result[0]['total'] ?? 0) > 0) {
        echo "La columna hora_marcacion ya existe.\n";
        exit;
    }
    
    // Agregar la columna
    $db->query("ALTER TABLE asistencia_docente ADD COLUMN hora_marcacion TIMESTAMP");
    
    // Actualizar registros existentes
    $db->query("UPDATE asistencia_docente 
                SET hora_marcacion = COALESCE(created_at, CURRENT_TIMESTAMP)
                WHERE hora_marcacion IS NULL");
    
    // Hacer la columna NOT NULL
    $db->query("ALTER TABLE asistencia_docente ALTER COLUMN hora_marcacion SET NOT NULL");
    
    echo "Columna hora_marcacion agregada exitosamente.\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}


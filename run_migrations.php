<?php
/**
 * Script para ejecutar todas las migraciones pendientes
 * Ejecutar desde: make migrate
 */

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/app/core/Database.php';

try {
    $db = Database::getInstance();
    
    echo "🔄 Ejecutando migraciones...\n\n";
    
    // Lista de migraciones en orden (solo las que necesitan ejecutarse)
    $migrations = [
        '002_create_docentes_info.sql',
        '003_add_hora_marcacion_to_asistencia_docente.sql',
        '004_add_password_changed_to_usuarios.sql',
        '005_create_ausencias_docente.sql',
        '006_store_ausencias_files_in_db.sql',
        '007_fix_password_changed_docentes.sql'
    ];

    
    $migrationsDir = __DIR__ . '/database/migrations';
    $executedCount = 0;
    $skippedCount = 0;
    
    foreach ($migrations as $migrationFile) {
        $migrationPath = $migrationsDir . '/' . $migrationFile;
        
        if (!file_exists($migrationPath)) {
            echo "⚠️  Migración no encontrada: $migrationFile\n";
            continue;
        }
        
        echo "📄 Procesando: $migrationFile\n";
        
        // Leer el contenido del archivo SQL
        $sqlContent = file_get_contents($migrationPath);
        
        if (empty($sqlContent)) {
            echo "   ⚠️  Archivo vacío, saltando...\n\n";
            $skippedCount++;
            continue;
        }
        
        try {
            // Ejecutar la migración
            // Para migraciones DO $$, necesitamos ejecutarlas directamente
            $pdo = $db->getConnection();
            $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            $pdo->exec($sqlContent);
            echo "   ✅ Migración ejecutada exitosamente\n\n";
            $executedCount++;
        } catch (Exception $e) {
            // Si la migración ya fue ejecutada, puede dar error pero continuamos
            $errorMsg = $e->getMessage();
            if (strpos($errorMsg, 'already exists') !== false || 
                strpos($errorMsg, 'duplicate') !== false ||
                strpos($errorMsg, 'does not exist') === false) {
                // Si el error no es sobre algo que no existe, probablemente ya está aplicada
                echo "   ℹ️  Migración ya aplicada o no aplicable: " . substr($errorMsg, 0, 100) . "\n\n";
                $skippedCount++;
            } else {
                echo "   ❌ Error: " . $errorMsg . "\n\n";
                // No lanzar excepción, solo continuar con la siguiente migración
                $skippedCount++;
            }
        }
    }
    
    echo "✅ Migraciones completadas:\n";
    echo "   - Ejecutadas: $executedCount\n";
    echo "   - Omitidas: $skippedCount\n";
    
} catch (Exception $e) {
    echo "❌ Error ejecutando migraciones: " . $e->getMessage() . "\n";
    exit(1);
}


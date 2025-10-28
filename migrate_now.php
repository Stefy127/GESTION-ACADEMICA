<?php
/**
 * Ejecutar esta migración accediendo a: 
 * https://gestion-academica-1065797435547.us-central1.run.app/migrate_now.php
 */

header('Content-Type: text/plain; charset=utf-8');

echo "=== Ejecutando migración de docentes_info ===\n\n";

try {
    require_once __DIR__ . '/app/config/database.php';
    require_once __DIR__ . '/app/models/Database.php';
    
    $db = Database::getInstance();
    
    // Verificar si la tabla ya existe
    $checkSql = "SELECT EXISTS (
        SELECT FROM information_schema.tables 
        WHERE table_schema = 'public' 
        AND table_name = 'docentes_info'
    )";
    
    $exists = $db->query($checkSql);
    
    if ($exists[0]['exists']) {
        echo "✓ La tabla docentes_info ya existe.\n";
        echo "\n✅ No es necesario ejecutar la migración.\n";
        echo "El módulo de docentes debería funcionar correctamente.\n";
        exit(0);
    }
    
    echo "La tabla NO existe. Creándola...\n\n";
    
    // Crear la tabla
    echo "1. Creando tabla docentes_info...\n";
    
    $sql = "CREATE TABLE docentes_info (
        id SERIAL PRIMARY KEY,
        usuario_id INTEGER REFERENCES usuarios(id) ON DELETE CASCADE UNIQUE NOT NULL,
        titulo_profesional VARCHAR(150),
        especialidad VARCHAR(100),
        departamento VARCHAR(100),
        anos_experiencia INTEGER DEFAULT 0,
        grado_academico VARCHAR(100),
        universidad_egresado VARCHAR(200),
        fecha_ingreso DATE,
        categoria VARCHAR(50),
        dedicacion VARCHAR(50),
        observaciones TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    $db->query($sql);
    echo "   ✓ Tabla creada exitosamente.\n\n";
    
    // Crear índice
    echo "2. Creando índice...\n";
    $indexSql = "CREATE INDEX idx_docentes_info_usuario ON docentes_info(usuario_id)";
    $db->query($indexSql);
    echo "   ✓ Índice creado exitosamente.\n\n";
    
    echo "==========================================\n";
    echo "✅ Migración completada exitosamente!\n";
    echo "==========================================\n\n";
    echo "Ahora puedes acceder al módulo de docentes sin errores:\n";
    echo "https://gestion-academica-1065797435547.us-central1.run.app/docentes\n";
    
} catch (PDOException $e) {
    echo "\n❌ Error durante la migración:\n";
    echo "Mensaje: " . $e->getMessage() . "\n";
    echo "Código: " . $e->getCode() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "\n❌ Error inesperado:\n";
    echo $e->getMessage() . "\n";
    exit(1);
}


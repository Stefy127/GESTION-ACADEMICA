<?php
/**
 * Script para crear información de docente por defecto para usuarios sin info
 * Ejecutar accediendo a: https://gestion-academica-1065797435547.us-central1.run.app/create_default_docente_info.php
 */

header('Content-Type: text/plain; charset=utf-8');

echo "=== Creando información de docente por defecto ===\n\n";

try {
    require_once __DIR__ . '/app/config/database.php';
    require_once __DIR__ . '/app/models/Database.php';
    
    $db = Database::getInstance();
    
    // Obtener todos los usuarios docentes que no tienen información
    $sql = "SELECT u.id, u.nombre, u.apellido, u.email
            FROM usuarios u
            WHERE u.rol_id = (SELECT id FROM roles WHERE nombre = 'docente')
            AND u.activo = true
            AND NOT EXISTS (
                SELECT 1 FROM docentes_info di WHERE di.usuario_id = u.id
            )";
    
    $docentes = $db->query($sql);
    
    if (empty($docentes)) {
        echo "✓ Todos los docentes ya tienen información asignada.\n";
        exit(0);
    }
    
    echo "Encontrados " . count($docentes) . " docentes sin información:\n\n";
    
    foreach ($docentes as $docente) {
        echo "→ {$docente['nombre']} {$docente['apellido']} ({$docente['email']})\n";
        
        // Crear información de docente por defecto
        $insertSql = "INSERT INTO docentes_info 
                      (usuario_id, titulo_profesional, especialidad, departamento, 
                       anos_experiencia, grado_academico, universidad_egresado, 
                       categoria, dedicacion, created_at, updated_at) 
                      VALUES 
                      (:usuario_id, :titulo_profesional, :especialidad, :departamento,
                       :anos_experiencia, :grado_academico, :universidad_egresado,
                       :categoria, :dedicacion, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)";
        
        $params = [
            ':usuario_id' => $docente['id'],
            ':titulo_profesional' => 'Ingeniería de Sistemas',
            ':especialidad' => 'Desarrollo de Software',
            ':departamento' => 'Tecnologías de la Información',
            ':anos_experiencia' => 5,
            ':grado_academico' => 'Ingeniería',
            ':universidad_egresado' => 'Universidad Mayor de San Andrés',
            ':categoria' => 'Titular',
            ':dedicacion' => 'tiempo_completo'
        ];
        
        $db->query($insertSql, $params);
        echo "   ✓ Información creada\n";
    }
    
    echo "\n==========================================\n";
    echo "✅ Proceso completado exitosamente!\n";
    echo "Se creó información de docente para " . count($docentes) . " usuarios.\n";
    echo "==========================================\n";
    
} catch (Exception $e) {
    echo "\n❌ Error durante el proceso:\n";
    echo $e->getMessage() . "\n";
    exit(1);
}


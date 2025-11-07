<?php
/**
 * Controlador para gestión de docentes
 */
class DocentesController extends Controller
{
    private $db;
    
    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance();
    }
    
    public function index()
    {
        if (!Middleware::checkRole(['administrador', 'coordinador'])) {
            return $this->view->renderWithLayout('errors/403', ['title' => 'Acceso Denegado']);
        }

        // Crear tabla si no existe
        $this->ensureDocentesInfoTableExists();

        // Registrar acceso al módulo
        ActivityLogger::logView('docentes', null);

        $data = [
            'title' => 'Gestión de Docentes',
            'user' => $this->getCurrentUser(),
            'docentes' => $this->getDocentes()
        ];

        return $this->view->renderWithLayout('docentes/index', $data);
    }
    
    private function ensureDocentesInfoTableExists()
    {
        try {
            // Verificar si la tabla existe
            $checkSql = "SELECT EXISTS (
                SELECT FROM information_schema.tables 
                WHERE table_schema = 'public' 
                AND table_name = 'docentes_info'
            )";
            
            $exists = $this->db->query($checkSql);
            
            if (!$exists[0]['exists']) {
                // Crear la tabla
                $createTableSql = "CREATE TABLE docentes_info (
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
                
                $this->db->query($createTableSql);
                
                // Crear índice
                $this->db->query("CREATE INDEX IF NOT EXISTS idx_docentes_info_usuario ON docentes_info(usuario_id)");
                
                error_log("✓ Tabla docentes_info creada automáticamente");
            }
        } catch (Exception $e) {
            error_log("Error al verificar/crear tabla docentes_info: " . $e->getMessage());
        }
    }


    public function edit($id)
    {
        if (!Middleware::checkRole(['administrador', 'coordinador'])) {
            return $this->view->renderWithLayout('errors/403', ['title' => 'Acceso Denegado']);
        }

        $data = [
            'title' => 'Editar Docente',
            'user' => $this->getCurrentUser(),
            'docente' => $this->getDocente($id)
        ];

        return $this->view->renderWithLayout('docentes/edit', $data);
    }

    public function update($id)
    {
        if (!Middleware::checkRole(['administrador', 'coordinador'])) {
            echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
            return;
        }
        
        try {
            // Actualizar información del usuario
            $sql = "UPDATE usuarios SET 
                    ci = :ci, nombre = :nombre, apellido = :apellido, 
                    email = :email, telefono = :telefono, updated_at = CURRENT_TIMESTAMP
                    WHERE id = :id";
            
            $this->db->query($sql, [
                ':ci' => $_POST['ci'] ?? '',
                ':nombre' => $_POST['nombre'] ?? '',
                ':apellido' => $_POST['apellido'] ?? '',
                ':email' => $_POST['email'] ?? '',
                ':telefono' => $_POST['telefono'] ?? '',
                ':id' => $id
            ]);
            
            // Actualizar o crear información adicional
            $sql = "SELECT id FROM docentes_info WHERE usuario_id = :usuario_id";
            $info = $this->db->query($sql, [':usuario_id' => $id]);
            
            if (!empty($info)) {
                // Actualizar
                $sql = "UPDATE docentes_info SET 
                        titulo_profesional = :titulo_profesional, 
                        especialidad = :especialidad, 
                        departamento = :departamento,
                        anos_experiencia = :anos_experiencia,
                        grado_academico = :grado_academico,
                        universidad_egresado = :universidad_egresado,
                        categoria = :categoria,
                        dedicacion = :dedicacion,
                        updated_at = CURRENT_TIMESTAMP
                        WHERE usuario_id = :usuario_id";
            } else {
                // Crear
                $sql = "INSERT INTO docentes_info 
                        (usuario_id, titulo_profesional, especialidad, departamento, 
                         anos_experiencia, grado_academico, universidad_egresado, categoria, dedicacion) 
                        VALUES (:usuario_id, :titulo_profesional, :especialidad, :departamento, 
                        :anos_experiencia, :grado_academico, :universidad_egresado, :categoria, :dedicacion)";
            }
            
            $params = [
                ':usuario_id' => $id,
                ':titulo_profesional' => $_POST['titulo_profesional'] ?? '',
                ':especialidad' => $_POST['especialidad'] ?? '',
                ':departamento' => $_POST['departamento'] ?? '',
                ':anos_experiencia' => intval($_POST['anos_experiencia'] ?? 0),
                ':grado_academico' => $_POST['grado_academico'] ?? '',
                ':universidad_egresado' => $_POST['universidad_egresado'] ?? '',
                ':categoria' => $_POST['categoria'] ?? '',
                ':dedicacion' => $_POST['dedicacion'] ?? ''
            ];
            
            $this->db->query($sql, $params);
            
            // Registrar actividad
            ActivityLogger::logUpdate('docentes', $id, [], $_POST);
            
            echo json_encode([
                'success' => true,
                'message' => 'Docente actualizado exitosamente',
                'redirect' => '/docentes'
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al actualizar el docente: ' . $e->getMessage()
            ]);
        }
    }

    public function delete($id)
    {
        if (!Middleware::checkRole(['administrador'])) {
            echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
            return;
        }
        
        try {
            $sql = "UPDATE usuarios SET activo = false, updated_at = CURRENT_TIMESTAMP WHERE id = :id";
            // Obtener datos antes de eliminar
            $userSql = "SELECT * FROM usuarios WHERE id = :id";
            $userData = $this->db->query($userSql, [':id' => $id]);
            
            $this->db->query($sql, [':id' => $id]);
            
            // Registrar actividad
            ActivityLogger::logDelete('docentes', $id, $userData);
            
            echo json_encode([
                'success' => true,
                'message' => 'Docente eliminado exitosamente'
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al eliminar el docente: ' . $e->getMessage()
            ]);
        }
    }

    private function getDocentes()
    {
        $sql = "SELECT u.id, u.ci, u.nombre, u.apellido, u.email, u.telefono, u.activo,
                       di.titulo_profesional, di.especialidad, di.departamento, 
                       di.anos_experiencia, di.grado_academico, di.categoria, di.dedicacion
                FROM usuarios u
                LEFT JOIN docentes_info di ON u.id = di.usuario_id
                WHERE u.rol_id = (SELECT id FROM roles WHERE nombre = 'docente')
                AND u.activo = true
                ORDER BY u.nombre, u.apellido";
        
        return $this->db->query($sql);
    }

    private function getDocente($id)
    {
        $sql = "SELECT u.id, u.ci, u.nombre, u.apellido, u.email, u.telefono, u.activo,
                       di.titulo_profesional, di.especialidad, di.departamento, 
                       di.anos_experiencia, di.grado_academico, di.universidad_egresado, 
                       di.categoria, di.dedicacion, di.observaciones, di.fecha_ingreso
                FROM usuarios u
                LEFT JOIN docentes_info di ON u.id = di.usuario_id
                WHERE u.id = :id AND u.rol_id = (SELECT id FROM roles WHERE nombre = 'docente')";
        
        $docentes = $this->db->query($sql, [':id' => $id]);
        return $docentes[0] ?? null;
    }
}

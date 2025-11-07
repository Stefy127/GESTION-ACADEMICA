<?php
/**
 * Controlador para gestión de aulas
 */
class AulasController extends Controller
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

        // Registrar acceso al módulo
        ActivityLogger::logView('aulas', null);

        $data = [
            'title' => 'Gestión de Aulas',
            'user' => $this->getCurrentUser(),
            'aulas' => $this->getAulas()
        ];

        return $this->view->renderWithLayout('aulas/index', $data);
    }

    public function create()
    {
        if (!Middleware::checkRole(['administrador', 'coordinador'])) {
            return $this->view->renderWithLayout('errors/403', ['title' => 'Acceso Denegado']);
        }

        $data = [
            'title' => 'Crear Aula',
            'user' => $this->getCurrentUser()
        ];

        return $this->view->renderWithLayout('aulas/create', $data);
    }

    public function store()
    {
        if (!Middleware::checkRole(['administrador', 'coordinador'])) {
            echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
            return;
        }
        
        try {
            $sql = "INSERT INTO aulas (nombre, codigo, capacidad, tipo, ubicacion, equipamiento) 
                    VALUES (:nombre, :codigo, :capacidad, :tipo, :ubicacion, :equipamiento)";
            
            $params = [
                ':nombre' => $_POST['nombre'] ?? '',
                ':codigo' => $_POST['codigo'] ?? '',
                ':capacidad' => intval($_POST['capacidad'] ?? 0),
                ':tipo' => $_POST['tipo'] ?? '',
                ':ubicacion' => $_POST['ubicacion'] ?? '',
                ':equipamiento' => $_POST['equipamiento'] ?? ''
            ];
            
            $this->db->query($sql, $params);
            
            // Obtener el ID del aula creada
            $aulaIdSql = "SELECT id FROM aulas WHERE codigo = :codigo ORDER BY created_at DESC LIMIT 1";
            $aulaIdResult = $this->db->query($aulaIdSql, [':codigo' => $_POST['codigo']]);
            $aulaId = $aulaIdResult && count($aulaIdResult) > 0 ? $aulaIdResult[0]['id'] : null;
            
            // Registrar actividad
            ActivityLogger::logCreate('aulas', $aulaId, [
                'nombre' => $_POST['nombre'] ?? '',
                'codigo' => $_POST['codigo'] ?? '',
                'tipo' => $_POST['tipo'] ?? ''
            ]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Aula creada exitosamente',
                'redirect' => '/aulas'
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al crear el aula: ' . $e->getMessage()
            ]);
        }
    }

    public function edit($id)
    {
        if (!Middleware::checkRole(['administrador', 'coordinador'])) {
            return $this->view->renderWithLayout('errors/403', ['title' => 'Acceso Denegado']);
        }

        $data = [
            'title' => 'Editar Aula',
            'user' => $this->getCurrentUser(),
            'aula' => $this->getAula($id)
        ];

        return $this->view->renderWithLayout('aulas/edit', $data);
    }

    public function update($id)
    {
        if (!Middleware::checkRole(['administrador', 'coordinador'])) {
            echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
            return;
        }
        
        try {
            $sql = "UPDATE aulas SET 
                    nombre = :nombre, codigo = :codigo, capacidad = :capacidad, 
                    tipo = :tipo, ubicacion = :ubicacion, equipamiento = :equipamiento,
                    activa = :activa, updated_at = CURRENT_TIMESTAMP
                    WHERE id = :id";
            
            // Obtener datos anteriores
            $oldAulaSql = "SELECT * FROM aulas WHERE id = :id";
            $oldAula = $this->db->query($oldAulaSql, [':id' => $id]);
            
            $this->db->query($sql, [
                ':nombre' => $_POST['nombre'] ?? '',
                ':codigo' => $_POST['codigo'] ?? '',
                ':capacidad' => intval($_POST['capacidad'] ?? 0),
                ':tipo' => $_POST['tipo'] ?? '',
                ':ubicacion' => $_POST['ubicacion'] ?? '',
                ':equipamiento' => $_POST['equipamiento'] ?? '',
                ':activa' => isset($_POST['activa']) ? 'true' : 'false',
                ':id' => $id
            ]);
            
            // Registrar actividad
            ActivityLogger::logUpdate('aulas', $id, $oldAula, [
                'nombre' => $_POST['nombre'] ?? '',
                'codigo' => $_POST['codigo'] ?? ''
            ]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Aula actualizada exitosamente',
                'redirect' => '/aulas'
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al actualizar el aula: ' . $e->getMessage()
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
            // Obtener datos antes de eliminar
            $aulaSql = "SELECT * FROM aulas WHERE id = :id";
            $aulaData = $this->db->query($aulaSql, [':id' => $id]);
            
            $sql = "UPDATE aulas SET activa = false, updated_at = CURRENT_TIMESTAMP WHERE id = :id";
            $this->db->query($sql, [':id' => $id]);
            
            // Registrar actividad
            ActivityLogger::logDelete('aulas', $id, $aulaData);
            
            echo json_encode([
                'success' => true,
                'message' => 'Aula eliminada exitosamente'
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al eliminar el aula: ' . $e->getMessage()
            ]);
        }
    }

    public function horarios($id)
    {
        if (!Middleware::checkRole(['administrador', 'coordinador'])) {
            return $this->view->renderWithLayout('errors/403', ['title' => 'Acceso Denegado']);
        }

        $aula = $this->getAula($id);
        $horarios = $this->getHorariosAula($id);

        $data = [
            'title' => 'Horarios del Aula',
            'user' => $this->getCurrentUser(),
            'aula' => $aula,
            'horarios' => $horarios
        ];

        return $this->view->renderWithLayout('aulas/horarios', $data);
    }

    private function getAulas()
    {
        $sql = "SELECT * FROM aulas WHERE activa = true ORDER BY nombre";
        return $this->db->query($sql);
    }

    private function getAula($id)
    {
        $sql = "SELECT * FROM aulas WHERE id = :id";
        $aulas = $this->db->query($sql, [':id' => $id]);
        return $aulas[0] ?? null;
    }

    private function getHorariosAula($aulaId)
    {
        $sql = "SELECT h.id, h.dia_semana, h.hora_inicio, h.hora_fin, g.numero as grupo,
                       m.nombre as materia, u.nombre || ' ' || u.apellido as docente
                FROM horarios h
                LEFT JOIN grupos g ON h.grupo_id = g.id
                LEFT JOIN materias m ON g.materia_id = m.id
                LEFT JOIN usuarios u ON h.docente_id = u.id
                WHERE h.aula_id = :aula_id AND h.activo = true
                ORDER BY h.dia_semana, h.hora_inicio";
        
        return $this->db->query($sql, [':aula_id' => $aulaId]);
    }
}

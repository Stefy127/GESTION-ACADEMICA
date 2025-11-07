<?php
/**
 * Controlador para gestión de materias
 */
class MateriasController extends Controller
{
    public function index()
    {
        if (!Middleware::checkRole(['administrador', 'coordinador'])) {
            return $this->view->renderWithLayout('errors/403', ['title' => 'Acceso Denegado']);
        }

        // Registrar acceso al módulo
        ActivityLogger::logView('materias', null);

        $data = [
            'title' => 'Gestión de Materias',
            'user' => $this->getCurrentUser(),
            'materias' => $this->getMaterias()
        ];

        return $this->view->renderWithLayout('materias/index', $data);
    }

    public function create()
    {
        if (!Middleware::checkRole(['administrador', 'coordinador'])) {
            return $this->view->renderWithLayout('errors/403', ['title' => 'Acceso Denegado']);
        }

        $data = [
            'title' => 'Crear Materia',
            'user' => $this->getCurrentUser()
        ];

        return $this->view->renderWithLayout('materias/create', $data);
    }

    public function edit($id)
    {
        if (!Middleware::checkRole(['administrador', 'coordinador'])) {
            return $this->view->renderWithLayout('errors/403', ['title' => 'Acceso Denegado']);
        }

        $data = [
            'title' => 'Editar Materia',
            'user' => $this->getCurrentUser(),
            'materia' => $this->getMateria($id)
        ];

        return $this->view->renderWithLayout('materias/edit', $data);
    }
    
    private function getMaterias()
    {
        try {
            $db = Database::getInstance();
            return $db->query("SELECT * FROM materias WHERE activa = true ORDER BY nombre");
        } catch (Exception $e) {
            return [];
        }
    }

    private function getMateria($id)
    {
        try {
            $db = Database::getInstance();
            $result = $db->query("SELECT * FROM materias WHERE id = :id", [':id' => $id]);
            return $result ? $result[0] : null;
        } catch (Exception $e) {
            return null;
        }
    }
    
    public function store()
    {
        if (!Middleware::checkRole(['administrador', 'coordinador'])) {
            echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
            return;
        }
        try {
            $db = Database::getInstance();
            $sql = "INSERT INTO materias (codigo, nombre, descripcion, nivel, carga_horaria) VALUES (:codigo, :nombre, :descripcion, :nivel, :carga_horaria)";
            $params = [':codigo' => $_POST['codigo'] ?? '', ':nombre' => $_POST['nombre'] ?? '', ':descripcion' => $_POST['descripcion'] ?? '', ':nivel' => $_POST['nivel'] ?? '', ':carga_horaria' => intval($_POST['carga_horaria'] ?? 0)];
            $db->query($sql, $params);
            
            // Obtener ID de la materia creada
            $materiaIdSql = "SELECT id FROM materias WHERE codigo = :codigo ORDER BY created_at DESC LIMIT 1";
            $materiaIdResult = $db->query($materiaIdSql, [':codigo' => $_POST['codigo']]);
            $materiaId = $materiaIdResult && count($materiaIdResult) > 0 ? $materiaIdResult[0]['id'] : null;
            
            // Registrar actividad
            ActivityLogger::logCreate('materias', $materiaId, [
                'codigo' => $_POST['codigo'] ?? '',
                'nombre' => $_POST['nombre'] ?? ''
            ]);
            
            echo json_encode(['success' => true, 'message' => 'Materia creada', 'redirect' => '/materias']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }
    
    public function update($id)
    {
        if (!Middleware::checkRole(['administrador', 'coordinador'])) {
            echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
            return;
        }
        try {
            $db = Database::getInstance();
            $sql = "UPDATE materias SET codigo = :codigo, nombre = :nombre, descripcion = :descripcion, nivel = :nivel, carga_horaria = :carga_horaria WHERE id = :id";
            $params = [':codigo' => $_POST['codigo'] ?? '', ':nombre' => $_POST['nombre'] ?? '', ':descripcion' => $_POST['descripcion'] ?? '', ':nivel' => $_POST['nivel'] ?? '', ':carga_horaria' => intval($_POST['carga_horaria'] ?? 0), ':id' => $id];
            // Obtener datos anteriores
            $oldSql = "SELECT * FROM materias WHERE id = :id";
            $oldData = $db->query($oldSql, [':id' => $id]);
            
            $db->query($sql, $params);
            
            // Registrar actividad
            ActivityLogger::logUpdate('materias', $id, $oldData, [
                'codigo' => $_POST['codigo'] ?? '',
                'nombre' => $_POST['nombre'] ?? ''
            ]);
            
            echo json_encode(['success' => true, 'message' => 'Materia actualizada', 'redirect' => '/materias']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }
    
    public function delete($id)
    {
        if (!Middleware::checkRole(['administrador', 'coordinador'])) {
            echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
            return;
        }
        
        try {
            // Obtener datos antes de eliminar
            $db = Database::getInstance();
            $sql = "SELECT * FROM materias WHERE id = :id";
            $materiaData = $db->query($sql, [':id' => $id]);
            
            $sql = "UPDATE materias SET activa = false WHERE id = :id";
            $db->query($sql, [':id' => $id]);
            
            // Registrar actividad
            ActivityLogger::logDelete('materias', $id, $materiaData);
            
            echo json_encode([
                'success' => true,
                'message' => 'Materia eliminada exitosamente'
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al eliminar la materia: ' . $e->getMessage()
            ]);
        }
    }
    
    public function grupos($id)
    {
        if (!Middleware::checkRole(['administrador', 'coordinador'])) {
            return $this->view->renderWithLayout('errors/403', ['title' => 'Acceso Denegado']);
        }

        $materia = $this->getMateria($id);
        if (!$materia) {
            return $this->view->renderWithLayout('errors/404', ['title' => 'Materia no encontrada']);
        }

        $data = [
            'title' => 'Grupos de la Materia: ' . $materia['nombre'],
            'user' => $this->getCurrentUser(),
            'materia' => $materia,
            'grupos' => $this->getGruposByMateria($id)
        ];

        return $this->view->renderWithLayout('materias/grupos', $data);
    }
    
    private function getGruposByMateria($materiaId)
    {
        try {
            $db = Database::getInstance();
            return $db->query("SELECT g.*, u.nombre || ' ' || u.apellido as docente_nombre
                              FROM grupos g
                              LEFT JOIN usuarios u ON g.docente_id = u.id
                              WHERE g.materia_id = :materia_id AND g.activo = true
                              ORDER BY g.numero", [':materia_id' => $materiaId]);
        } catch (Exception $e) {
            return [];
        }
    }
}

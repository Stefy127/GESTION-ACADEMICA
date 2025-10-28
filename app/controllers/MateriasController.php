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
}

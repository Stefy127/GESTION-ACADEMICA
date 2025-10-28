<?php
/**
 * Controlador para gestión de grupos
 */
class GruposController extends Controller
{
    public function index()
    {
        if (!Middleware::checkRole(['administrador', 'coordinador'])) {
            return $this->view->renderWithLayout('errors/403', ['title' => 'Acceso Denegado']);
        }

        // Registrar acceso al módulo
        ActivityLogger::logView('grupos', null);

        $data = [
            'title' => 'Gestión de Grupos',
            'user' => $this->getCurrentUser(),
            'grupos' => $this->getGrupos()
        ];

        return $this->view->renderWithLayout('grupos/index', $data);
    }

    public function create()
    {
        if (!Middleware::checkRole(['administrador', 'coordinador'])) {
            return $this->view->renderWithLayout('errors/403', ['title' => 'Acceso Denegado']);
        }

        $data = [
            'title' => 'Crear Grupo',
            'user' => $this->getCurrentUser(),
            'materias' => $this->getMaterias()
        ];

        return $this->view->renderWithLayout('grupos/create', $data);
    }

    public function edit($id)
    {
        if (!Middleware::checkRole(['administrador', 'coordinador'])) {
            return $this->view->renderWithLayout('errors/403', ['title' => 'Acceso Denegado']);
        }

        $data = [
            'title' => 'Editar Grupo',
            'user' => $this->getCurrentUser(),
            'grupo' => $this->getGrupo($id),
            'materias' => $this->getMaterias()
        ];

        return $this->view->renderWithLayout('grupos/edit', $data);
    }

    private function getGrupos()
    {
        try {
            $db = Database::getInstance();
            return $db->query("SELECT g.*, m.nombre as materia_nombre, m.codigo as materia_codigo, 
                              u.nombre || ' ' || u.apellido as docente_nombre
                              FROM grupos g
                              LEFT JOIN materias m ON g.materia_id = m.id
                              LEFT JOIN usuarios u ON g.docente_id = u.id
                              WHERE g.activo = true
                              ORDER BY g.numero");
        } catch (Exception $e) {
            return [];
        }
    }

    private function getMaterias()
    {
        try {
            $db = Database::getInstance();
            return $db->query("SELECT id, nombre, codigo FROM materias WHERE activa = true ORDER BY nombre");
        } catch (Exception $e) {
            return [];
        }
    }

    private function getGrupo($id)
    {
        try {
            $db = Database::getInstance();
            $result = $db->query("SELECT * FROM grupos WHERE id = :id", [':id' => $id]);
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
            $sql = "INSERT INTO grupos (numero, semestre, turno, materia_id, capacidad_maxima, activo) 
                    VALUES (:numero, :semestre, :turno, :materia_id, :capacidad_maxima, true)";
            $params = [
                ':numero' => $_POST['numero'] ?? '',
                ':semestre' => $_POST['semestre'] ?? '',
                ':turno' => $_POST['turno'] ?? '',
                ':materia_id' => intval($_POST['materia_id'] ?? 0),
                ':capacidad_maxima' => intval($_POST['capacidad_maxima'] ?? 30)
            ];
            $db->query($sql, $params);
            echo json_encode(['success' => true, 'message' => 'Grupo creado exitosamente', 'redirect' => '/grupos']);
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
            $sql = "UPDATE grupos SET numero = :numero, semestre = :semestre, turno = :turno, 
                    materia_id = :materia_id, capacidad_maxima = :capacidad_maxima WHERE id = :id";
            $params = [
                ':numero' => $_POST['numero'] ?? '',
                ':semestre' => $_POST['semestre'] ?? '',
                ':turno' => $_POST['turno'] ?? '',
                ':materia_id' => intval($_POST['materia_id'] ?? 0),
                ':capacidad_maxima' => intval($_POST['capacidad_maxima'] ?? 30),
                ':id' => $id
            ];
            $db->query($sql, $params);
            echo json_encode(['success' => true, 'message' => 'Grupo actualizado', 'redirect' => '/grupos']);
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
            $db = Database::getInstance();
            $sql = "UPDATE grupos SET activo = false WHERE id = :id";
            $db->query($sql, [':id' => $id]);
            echo json_encode(['success' => true, 'message' => 'Grupo eliminado', 'redirect' => '/grupos']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }
}

<?php
/**
 * Controlador para gestión de usuarios
 */
class UsuariosController extends Controller
{
    public function index()
    {
        // Verificar permisos
        if (!Middleware::checkRole(['administrador', 'coordinador'])) {
            return $this->view->renderWithLayout('errors/403', ['title' => 'Acceso Denegado']);
        }

        // Registrar acceso al módulo
        ActivityLogger::logView('usuarios', null);

        $data = [
            'title' => 'Gestión de Usuarios',
            'user' => $this->getCurrentUser(),
            'usuarios' => $this->getUsuarios()
        ];

        return $this->view->renderWithLayout('usuarios/index', $data);
    }

    public function create()
    {
        if (!Middleware::checkRole(['administrador', 'coordinador'])) {
            return $this->view->renderWithLayout('errors/403', ['title' => 'Acceso Denegado']);
        }

        $data = [
            'title' => 'Crear Usuario',
            'user' => $this->getCurrentUser(),
            'roles' => $this->getRoles()
        ];

        return $this->view->renderWithLayout('usuarios/create', $data);
    }

    public function edit($id)
    {
        if (!Middleware::checkRole(['administrador', 'coordinador'])) {
            return $this->view->renderWithLayout('errors/403', ['title' => 'Acceso Denegado']);
        }

        $data = [
            'title' => 'Editar Usuario',
            'user' => $this->getCurrentUser(),
            'usuario' => $this->getUsuario($id),
            'roles' => $this->getRoles()
        ];

        return $this->view->renderWithLayout('usuarios/edit', $data);
    }

    private function getUsuarios()
    {
        try {
            $db = Database::getInstance();
            return $db->query("SELECT u.*, r.nombre as rol 
                              FROM usuarios u 
                              LEFT JOIN roles r ON u.rol_id = r.id 
                              WHERE u.activo = true 
                              ORDER BY u.nombre, u.apellido");
        } catch (Exception $e) {
            return [];
        }
    }

    private function getRoles()
    {
        return [
            ['nombre' => 'administrador'],
            ['nombre' => 'coordinador'],
            ['nombre' => 'docente'],
            ['nombre' => 'autoridad']
        ];
    }

    private function getUsuario($id)
    {
        try {
            $db = Database::getInstance();
            $result = $db->query("SELECT u.*, r.nombre as rol 
                                  FROM usuarios u 
                                  LEFT JOIN roles r ON u.rol_id = r.id 
                                  WHERE u.id = :id", [':id' => $id]);
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
            
            // Obtener rol_id basado en el nombre del rol
            $rolName = $_POST['rol'] ?? 'docente';
            $rolResult = $db->query("SELECT id FROM roles WHERE nombre = :nombre", [':nombre' => $rolName]);
            $rolId = $rolResult && count($rolResult) > 0 ? $rolResult[0]['id'] : 3; // Default a docente (rol_id = 3)
            
            $hashedPassword = password_hash($_POST['password'] ?? '', PASSWORD_BCRYPT);
            $sql = "INSERT INTO usuarios (ci, nombre, apellido, email, password_hash, rol_id, activo) 
                    VALUES (:ci, :nombre, :apellido, :email, :password, :rol_id, true)";
            $params = [
                ':ci' => $_POST['ci'] ?? uniqid('CI'), // Generar CI si no se proporciona
                ':nombre' => $_POST['nombre'] ?? '',
                ':apellido' => $_POST['apellido'] ?? '',
                ':email' => $_POST['email'] ?? '',
                ':password' => $hashedPassword,
                ':rol_id' => $rolId
            ];
            $db->query($sql, $params);
            
            // Obtener el ID del usuario creado
            $userIdSql = "SELECT id FROM usuarios WHERE email = :email ORDER BY created_at DESC LIMIT 1";
            $userIdResult = $db->query($userIdSql, [':email' => $_POST['email']]);
            $userId = $userIdResult && count($userIdResult) > 0 ? $userIdResult[0]['id'] : null;
            
            // Registrar actividad
            ActivityLogger::logCreate('usuarios', $userId, [
                'nombre' => $_POST['nombre'] ?? '',
                'apellido' => $_POST['apellido'] ?? '',
                'email' => $_POST['email'] ?? ''
            ]);
            
            echo json_encode(['success' => true, 'message' => 'Usuario creado exitosamente', 'redirect' => '/usuarios']);
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
            
            // Obtener rol_id basado en el nombre del rol
            $rolName = $_POST['rol'] ?? 'docente';
            $rolResult = $db->query("SELECT id FROM roles WHERE nombre = :nombre", [':nombre' => $rolName]);
            $rolId = $rolResult && count($rolResult) > 0 ? $rolResult[0]['id'] : 3;
            
            $sql = "UPDATE usuarios SET nombre = :nombre, apellido = :apellido, email = :email, rol_id = :rol_id WHERE id = :id";
            $params = [
                ':nombre' => $_POST['nombre'] ?? '',
                ':apellido' => $_POST['apellido'] ?? '',
                ':email' => $_POST['email'] ?? '',
                ':rol_id' => $rolId,
                ':id' => $id
            ];
            
            // Si hay password, actualizarlo
            if (!empty($_POST['password'])) {
                $hashedPassword = password_hash($_POST['password'], PASSWORD_BCRYPT);
                $sql = "UPDATE usuarios SET nombre = :nombre, apellido = :apellido, email = :email, rol_id = :rol_id, password_hash = :password WHERE id = :id";
                $params[':password'] = $hashedPassword;
            }
            
            // Obtener datos anteriores antes de actualizar
            $oldUserSql = "SELECT * FROM usuarios WHERE id = :id";
            $oldUser = $db->query($oldUserSql, [':id' => $id]);
            
            $db->query($sql, $params);
            
            // Registrar actividad
            ActivityLogger::logUpdate('usuarios', $id, $oldUser, [
                'nombre' => $_POST['nombre'] ?? '',
                'apellido' => $_POST['apellido'] ?? '',
                'email' => $_POST['email'] ?? ''
            ]);
            
            echo json_encode(['success' => true, 'message' => 'Usuario actualizado', 'redirect' => '/usuarios']);
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
            
            // Obtener datos del usuario antes de eliminar
            $userSql = "SELECT * FROM usuarios WHERE id = :id";
            $userData = $db->query($userSql, [':id' => $id]);
            
            $sql = "UPDATE usuarios SET activo = false WHERE id = :id";
            $db->query($sql, [':id' => $id]);
            
            // Registrar actividad
            ActivityLogger::logDelete('usuarios', $id, $userData);
            
            echo json_encode(['success' => true, 'message' => 'Usuario eliminado', 'redirect' => '/usuarios']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }
}

<?php
/**
 * Clase para manejo de autenticación y sesiones
 */
class Auth
{
    private $db;
    private $sessionTimeout = 3600; // 1 hora
    
    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->startSession();
    }
    
    /**
     * Iniciar sesión segura
     */
    private function startSession()
    {
        if (session_status() === PHP_SESSION_NONE) {
            ini_set('session.cookie_httponly', 1);
            ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
            ini_set('session.use_strict_mode', 1);
            session_start();
        }
    }
    
    /**
     * Autenticar usuario
     */
    public function login($email, $password)
    {
        $sql = "SELECT u.*, r.nombre as rol_nombre, r.permisos 
                FROM usuarios u 
                JOIN roles r ON u.rol_id = r.id 
                WHERE u.email = :email AND u.activo = true";
        
        $user = $this->db->query($sql, ['email' => $email]);
        
        if (empty($user)) {
            return ['success' => false, 'message' => 'Credenciales inválidas'];
        }
        
        $user = $user[0];
        
        if (!password_verify($password, $user['password_hash'])) {
            return ['success' => false, 'message' => 'Credenciales inválidas'];
        }
        
        // Crear sesión
        $sessionId = $this->generateSessionId();
        $this->createSession($user['id'], $sessionId);
        
        // Actualizar último acceso
        $this->updateLastAccess($user['id']);
        
        // Registrar login en logs
        $this->logActivity($user['id'], 'login', 'usuarios', $user['id']);
        
        return [
            'success' => true,
            'user' => [
                'id' => $user['id'],
                'nombre' => $user['nombre'],
                'apellido' => $user['apellido'],
                'email' => $user['email'],
                'rol' => $user['rol_nombre'],
                'permisos' => json_decode($user['permisos'], true)
            ]
        ];
    }
    
    /**
     * Cerrar sesión
     */
    public function logout()
    {
        if (isset($_SESSION['user_id'])) {
            $this->logActivity($_SESSION['user_id'], 'logout', 'usuarios', $_SESSION['user_id']);
            $this->destroySession();
        }
        
        session_destroy();
        return true;
    }
    
    /**
     * Verificar si el usuario está autenticado
     */
    public function isAuthenticated()
    {
        return isset($_SESSION['user_id']) && isset($_SESSION['session_id']);
    }
    
    /**
     * Obtener usuario actual
     */
    public function getCurrentUser()
    {
        if (!$this->isAuthenticated()) {
            return null;
        }
        
        $sql = "SELECT u.*, r.nombre as rol_nombre, r.permisos 
                FROM usuarios u 
                JOIN roles r ON u.rol_id = r.id 
                WHERE u.id = :id AND u.activo = true";
        
        $user = $this->db->query($sql, ['id' => $_SESSION['user_id']]);
        
        if (empty($user)) {
            $this->logout();
            return null;
        }
        
        $user = $user[0];
        return [
            'id' => $user['id'],
            'nombre' => $user['nombre'],
            'apellido' => $user['apellido'],
            'email' => $user['email'],
            'rol' => $user['rol_nombre'],
            'permisos' => json_decode($user['permisos'], true)
        ];
    }
    
    /**
     * Verificar permisos
     */
    public function hasPermission($module, $action = 'read')
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            return false;
        }
        
        $permissions = $user['permisos'];
        
        if (!isset($permissions[$module])) {
            return false;
        }
        
        $modulePermission = $permissions[$module];
        
        if ($modulePermission === 'all') {
            return true;
        }
        
        if ($action === 'read' && in_array($modulePermission, ['read', 'all'])) {
            return true;
        }
        
        if ($action === 'write' && in_array($modulePermission, ['write', 'all'])) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Generar token de restablecimiento de contraseña
     */
    public function generateResetToken($email)
    {
        $sql = "SELECT id FROM usuarios WHERE email = :email AND activo = true";
        $user = $this->db->query($sql, ['email' => $email]);
        
        if (empty($user)) {
            return false;
        }
        
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        $sql = "UPDATE usuarios SET token_reset = :token, token_expires = :expires WHERE email = :email";
        $this->db->query($sql, [
            'token' => $token,
            'expires' => $expires,
            'email' => $email
        ]);
        
        return $token;
    }
    
    /**
     * Restablecer contraseña con token
     */
    public function resetPassword($token, $newPassword)
    {
        $sql = "SELECT id FROM usuarios WHERE token_reset = :token AND token_expires > NOW()";
        $user = $this->db->query($sql, ['token' => $token]);
        
        if (empty($user)) {
            return false;
        }
        
        $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        
        $sql = "UPDATE usuarios SET password_hash = :password, token_reset = NULL, token_expires = NULL WHERE token_reset = :token";
        $result = $this->db->query($sql, [
            'password' => $passwordHash,
            'token' => $token
        ]);
        
        return $result > 0;
    }
    
    /**
     * Generar ID de sesión único
     */
    private function generateSessionId()
    {
        return bin2hex(random_bytes(32));
    }
    
    /**
     * Crear sesión en base de datos
     */
    private function createSession($userId, $sessionId)
    {
        $_SESSION['user_id'] = $userId;
        $_SESSION['session_id'] = $sessionId;
        
        $expires = date('Y-m-d H:i:s', time() + $this->sessionTimeout);
        
        $sql = "INSERT INTO sesiones (id, usuario_id, ip_address, user_agent, expires_at) 
                VALUES (:id, :user_id, :ip, :user_agent, :expires)";
        
        $this->db->query($sql, [
            'id' => $sessionId,
            'user_id' => $userId,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'expires' => $expires
        ]);
    }
    
    /**
     * Destruir sesión
     */
    private function destroySession()
    {
        if (isset($_SESSION['session_id'])) {
            $sql = "DELETE FROM sesiones WHERE id = :id";
            $this->db->query($sql, ['id' => $_SESSION['session_id']]);
        }
    }
    
    /**
     * Actualizar último acceso
     */
    private function updateLastAccess($userId)
    {
        $sql = "UPDATE usuarios SET ultimo_acceso = NOW() WHERE id = :id";
        $this->db->query($sql, ['id' => $userId]);
    }
    
    /**
     * Registrar actividad en logs
     */
    private function logActivity($userId, $action, $table = null, $recordId = null, $oldData = null, $newData = null)
    {
        $sql = "INSERT INTO logs_actividad (usuario_id, accion, tabla_afectada, registro_id, datos_anteriores, datos_nuevos, ip_address, user_agent) 
                VALUES (:user_id, :action, :table, :record_id, :old_data, :new_data, :ip, :user_agent)";
        
        $this->db->query($sql, [
            'user_id' => $userId,
            'action' => $action,
            'table' => $table,
            'record_id' => $recordId,
            'old_data' => $oldData ? json_encode($oldData) : null,
            'new_data' => $newData ? json_encode($newData) : null,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
        ]);
    }
    
    /**
     * Limpiar sesiones expiradas
     */
    public function cleanExpiredSessions()
    {
        $sql = "DELETE FROM sesiones WHERE expires_at < NOW()";
        return $this->db->query($sql);
    }
}

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
        // Si la sesión ya está iniciada, no hacer nada
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }
        
        // Si la sesión no está iniciada, iniciarla
        if (session_status() === PHP_SESSION_NONE) {
            // Solo configurar si no se ha enviado output
            if (!headers_sent()) {
                ini_set('session.cookie_httponly', 1);
                ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
                ini_set('session.use_strict_mode', 1);
            }
            session_start();
        }
    }
    
    /**
     * Autenticar usuario
     */
    public function login($email, $password)
    {
        error_log("DEBUG Auth: Attempting login for email: $email");
        
        // Verificar si la columna password_changed existe
        $hasPasswordChangedColumn = $this->checkColumnExists('usuarios', 'password_changed');
        $passwordChangedField = $hasPasswordChangedColumn ? 'COALESCE(u.password_changed, true) as password_changed' : 'true as password_changed';
        
        // Intentar primero con email
        $sql = "SELECT u.*, r.nombre as rol_nombre, r.permisos,
                       $passwordChangedField
                FROM usuarios u 
                LEFT JOIN roles r ON u.rol_id = r.id 
                WHERE u.email = :email AND u.activo = true";
        
        try {
            $user = $this->db->query($sql, ['email' => $email]);
            error_log("DEBUG Auth: Found " . count($user) . " users with email");
            
            // Si no se encuentra por email, intentar por CI
            if (empty($user)) {
                error_log("DEBUG Auth: No user found with email, trying CI");
                $sql = "SELECT u.*, r.nombre as rol_nombre, r.permisos,
                               $passwordChangedField
                        FROM usuarios u 
                        LEFT JOIN roles r ON u.rol_id = r.id 
                        WHERE u.ci = :ci AND u.activo = true";
                $user = $this->db->query($sql, ['ci' => $email]);
                error_log("DEBUG Auth: Found " . count($user) . " users with CI");
            }
            
            if (empty($user)) {
                error_log("DEBUG Auth: No user found");
                return ['success' => false, 'message' => 'Credenciales inválidas'];
            }
            
            $user = $user[0];
            error_log("DEBUG Auth: User found - checking password");
            
            if (!password_verify($password, $user['password_hash'])) {
                error_log("DEBUG Auth: Password verification failed");
                return ['success' => false, 'message' => 'Credenciales inválidas'];
            }
            
            error_log("DEBUG Auth: Password verified successfully");
        } catch (Exception $e) {
            error_log("DEBUG Auth: Exception during login: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error de conexión. Inténtalo de nuevo.'];
        }
        
        // Crear sesión
        $sessionId = $this->generateSessionId();
        $this->createSession($user['id'], $sessionId);
        
        // Actualizar último acceso
        $this->updateLastAccess($user['id']);
        
        // Registrar login en logs
        $this->logActivity($user['id'], 'login', 'usuarios', $user['id']);
        
        // Verificar si es docente y necesita cambiar contraseña
        $needsPasswordChange = false;
        if (($user['rol_nombre'] ?? '') === 'docente') {
            // Verificar si el campo password_changed existe y es false
            $passwordChanged = isset($user['password_changed']) ? (bool)$user['password_changed'] : true;
            if (!$passwordChanged) {
                $needsPasswordChange = true;
            }
        }
        
        return [
            'success' => true,
            'user' => [
                'id' => $user['id'],
                'nombre' => $user['nombre'],
                'apellido' => $user['apellido'],
                'email' => $user['email'],
                'rol' => $user['rol_nombre'] ?? 'sin_rol',
                'permisos' => $user['permisos'] ? json_decode($user['permisos'], true) : []
            ],
            'needs_password_change' => $needsPasswordChange
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
        
        // Verificar si la columna password_changed existe
        $hasPasswordChangedColumn = $this->checkColumnExists('usuarios', 'password_changed');
        $passwordChangedField = $hasPasswordChangedColumn ? 'COALESCE(u.password_changed, true) as password_changed' : 'true as password_changed';
        
        $sql = "SELECT u.*, r.nombre as rol_nombre, r.permisos,
                       $passwordChangedField
                FROM usuarios u 
                LEFT JOIN roles r ON u.rol_id = r.id 
                WHERE u.id = :id AND u.activo = true";
        
        $user = $this->db->query($sql, ['id' => $_SESSION['user_id']]);
        
        if (empty($user)) {
            $this->logout();
            return null;
        }
        
        $user = $user[0];
        
        // Verificar si es docente y necesita cambiar contraseña
        $needsPasswordChange = false;
        if (($user['rol_nombre'] ?? '') === 'docente' && $hasPasswordChangedColumn) {
            $passwordChanged = isset($user['password_changed']) ? (bool)$user['password_changed'] : true;
            if (!$passwordChanged) {
                $needsPasswordChange = true;
            }
        }
        
        return [
            'id' => $user['id'],
            'nombre' => $user['nombre'],
            'apellido' => $user['apellido'],
            'email' => $user['email'],
            'rol' => $user['rol_nombre'] ?? 'sin_rol',
            'permisos' => $user['permisos'] ? json_decode($user['permisos'], true) : [],
            'needs_password_change' => $needsPasswordChange
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
        try {
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
        } catch (Exception $e) {
            // Si falla el log, no interrumpir el flujo principal
            error_log("Error logging activity: " . $e->getMessage());
        }
    }
    
    /**
     * Cambiar contraseña del usuario actual
     */
    public function changePassword($userId, $currentPassword, $newPassword)
    {
        // Verificar que el usuario existe y la contraseña actual es correcta
        $sql = "SELECT password_hash FROM usuarios WHERE id = :id AND activo = true";
        $user = $this->db->query($sql, ['id' => $userId]);
        
        if (empty($user)) {
            return ['success' => false, 'message' => 'Usuario no encontrado'];
        }
        
        if (!password_verify($currentPassword, $user[0]['password_hash'])) {
            return ['success' => false, 'message' => 'La contraseña actual es incorrecta'];
        }
        
        // Validar nueva contraseña
        if (strlen($newPassword) < 6) {
            return ['success' => false, 'message' => 'La nueva contraseña debe tener al menos 6 caracteres'];
        }
        
        // Actualizar contraseña y marcar como cambiada
        $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        
        // Verificar si la columna password_changed existe antes de actualizarla
        $hasPasswordChangedColumn = $this->checkColumnExists('usuarios', 'password_changed');
        
        if ($hasPasswordChangedColumn) {
            $sql = "UPDATE usuarios SET password_hash = :password, password_changed = true, updated_at = CURRENT_TIMESTAMP WHERE id = :id";
        } else {
            $sql = "UPDATE usuarios SET password_hash = :password, updated_at = CURRENT_TIMESTAMP WHERE id = :id";
        }
        
        $this->db->query($sql, [
            'password' => $passwordHash,
            'id' => $userId
        ]);
        
        // Registrar actividad
        $this->logActivity($userId, 'change_password', 'usuarios', $userId);
        
        return ['success' => true, 'message' => 'Contraseña cambiada exitosamente'];
    }
    
    /**
     * Verificar si una columna existe en una tabla
     */
    private function checkColumnExists($tableName, $columnName)
    {
        try {
            $sql = "SELECT COUNT(*) as total 
                    FROM information_schema.columns 
                    WHERE table_name = :table_name 
                    AND column_name = :column_name";
            $result = $this->db->query($sql, [
                'table_name' => $tableName,
                'column_name' => $columnName
            ]);
            return (($result[0]['total'] ?? 0) > 0);
        } catch (Exception $e) {
            error_log("Error checking column existence: " . $e->getMessage());
            return false;
        }
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

<?php
/**
 * Middleware para control de acceso y autenticación
 */
class Middleware
{
    private $auth;
    
    public function __construct()
    {
        $this->auth = new Auth();
    }
    
    /**
     * Verificar autenticación
     */
    public function requireAuth()
    {
        if (!$this->auth->isAuthenticated()) {
            $this->redirectToLogin();
        }
    }
    
    /**
     * Verificar permisos específicos
     */
    public function requirePermission($module, $action = 'read')
    {
        $this->requireAuth();
        
        if (!$this->auth->hasPermission($module, $action)) {
            $this->showAccessDenied();
        }
    }
    
    /**
     * Verificar que el usuario sea administrador
     */
    public function requireAdmin()
    {
        $this->requireAuth();
        
        $user = $this->auth->getCurrentUser();
        if ($user['rol'] !== 'administrador') {
            $this->showAccessDenied();
        }
    }
    
    /**
     * Verificar que el usuario sea coordinador o administrador
     */
    public function requireCoordinatorOrAdmin()
    {
        $this->requireAuth();
        
        $user = $this->auth->getCurrentUser();
        if (!in_array($user['rol'], ['administrador', 'coordinador'])) {
            $this->showAccessDenied();
        }
    }
    
    /**
     * Verificar que el usuario solo acceda a sus propios datos
     */
    public function requireOwnData($userId)
    {
        $this->requireAuth();
        
        $user = $this->auth->getCurrentUser();
        if ($user['id'] != $userId && !in_array($user['rol'], ['administrador', 'coordinador'])) {
            $this->showAccessDenied();
        }
    }
    
    /**
     * Generar token CSRF
     */
    public function generateCSRFToken()
    {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Verificar token CSRF
     */
    public function verifyCSRFToken($token)
    {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Validar datos de entrada
     */
    public function validateInput($data, $rules)
    {
        $errors = [];
        
        foreach ($rules as $field => $rule) {
            $value = $data[$field] ?? null;
            
            // Verificar campo requerido
            if (isset($rule['required']) && $rule['required'] && empty($value)) {
                $errors[$field] = "El campo {$field} es requerido";
                continue;
            }
            
            // Si el campo está vacío y no es requerido, continuar
            if (empty($value)) {
                continue;
            }
            
            // Validar tipo de dato
            if (isset($rule['type'])) {
                switch ($rule['type']) {
                    case 'email':
                        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                            $errors[$field] = "El campo {$field} debe ser un email válido";
                        }
                        break;
                    case 'int':
                        if (!is_numeric($value)) {
                            $errors[$field] = "El campo {$field} debe ser un número";
                        }
                        break;
                    case 'date':
                        if (!strtotime($value)) {
                            $errors[$field] = "El campo {$field} debe ser una fecha válida";
                        }
                        break;
                }
            }
            
            // Validar longitud mínima
            if (isset($rule['min_length']) && strlen($value) < $rule['min_length']) {
                $errors[$field] = "El campo {$field} debe tener al menos {$rule['min_length']} caracteres";
            }
            
            // Validar longitud máxima
            if (isset($rule['max_length']) && strlen($value) > $rule['max_length']) {
                $errors[$field] = "El campo {$field} no puede tener más de {$rule['max_length']} caracteres";
            }
            
            // Validar patrón regex
            if (isset($rule['pattern']) && !preg_match($rule['pattern'], $value)) {
                $errors[$field] = "El campo {$field} tiene un formato inválido";
            }
        }
        
        return $errors;
    }
    
    /**
     * Sanitizar datos de entrada
     */
    public function sanitizeInput($data)
    {
        $sanitized = [];
        
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $sanitized[$key] = htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
            } else {
                $sanitized[$key] = $value;
            }
        }
        
        return $sanitized;
    }
    
    /**
     * Redirigir al login
     */
    private function redirectToLogin()
    {
        header('Location: /login');
        exit;
    }
    
    /**
     * Mostrar error de acceso denegado
     */
    private function showAccessDenied()
    {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'Acceso denegado. No tienes permisos para realizar esta acción.'
        ]);
        exit;
    }
    
    /**
     * Obtener usuario actual
     */
    public function getCurrentUser()
    {
        return $this->auth->getCurrentUser();
    }
    
    /**
     * Verificar si el usuario está autenticado
     */
    public function isAuthenticated()
    {
        return $this->auth->isAuthenticated();
    }
    
    /**
     * Verificar rol del usuario (método estático)
     */
    public static function checkRole($allowedRoles)
    {
        $auth = new Auth();
        
        if (!$auth->isAuthenticated()) {
            return false;
        }
        
        $user = $auth->getCurrentUser();
        if (!$user) {
            return false;
        }
        
        return in_array($user['rol'], $allowedRoles);
    }
}

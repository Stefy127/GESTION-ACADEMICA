<?php
/**
 * Controlador de autenticación
 */
class AuthController extends Controller
{
    private $auth;
    private $middleware;
    
    public function __construct()
    {
        parent::__construct();
        $this->auth = new Auth();
        $this->middleware = new Middleware();
    }
    
    /**
     * Mostrar formulario de login
     */
    public function login()
    {
        if ($this->middleware->isAuthenticated()) {
            $this->redirect('/dashboard');
        }
        
        $data = [
            'title' => 'Iniciar Sesión',
            'csrf_token' => $this->middleware->generateCSRFToken()
        ];
        
        return $this->view->renderWithLayout('auth/login', $data);
    }
    
    /**
     * Procesar login
     */
    public function processLogin()
    {
        if (!$this->isPost()) {
            $this->redirect('/login');
        }
        
        $email = $this->getPost('email');
        $password = $this->getPost('password');
        $csrfToken = $this->getPost('csrf_token');
        
        // Verificar CSRF token
        if (!$this->middleware->verifyCSRFToken($csrfToken)) {
            return $this->jsonResponse(['success' => false, 'message' => 'Token CSRF inválido']);
        }
        
        // Validar datos
        $errors = $this->middleware->validateInput([
            'email' => $email,
            'password' => $password
        ], [
            'email' => ['required' => true, 'type' => 'email'],
            'password' => ['required' => true, 'min_length' => 6]
        ]);
        
        if (!empty($errors)) {
            return $this->jsonResponse(['success' => false, 'message' => 'Datos inválidos', 'errors' => $errors]);
        }
        
        // Intentar autenticar
        $result = $this->auth->login($email, $password);
        
        if ($result['success']) {
            $_SESSION['user'] = $result['user'];
            return $this->jsonResponse(['success' => true, 'redirect' => '/dashboard']);
        } else {
            return $this->jsonResponse(['success' => false, 'message' => $result['message']]);
        }
    }
    
    /**
     * Cerrar sesión
     */
    public function logout()
    {
        $this->auth->logout();
        $this->redirect('/login');
    }
    
    /**
     * Mostrar formulario de restablecimiento de contraseña
     */
    public function forgotPassword()
    {
        $data = [
            'title' => 'Restablecer Contraseña',
            'csrf_token' => $this->middleware->generateCSRFToken()
        ];
        
        return $this->view->renderWithLayout('auth/forgot-password', $data);
    }
    
    /**
     * Procesar solicitud de restablecimiento
     */
    public function processForgotPassword()
    {
        if (!$this->isPost()) {
            $this->redirect('/forgot-password');
        }
        
        $email = $this->getPost('email');
        $csrfToken = $this->getPost('csrf_token');
        
        // Verificar CSRF token
        if (!$this->middleware->verifyCSRFToken($csrfToken)) {
            return $this->jsonResponse(['success' => false, 'message' => 'Token CSRF inválido']);
        }
        
        // Validar email
        $errors = $this->middleware->validateInput(['email' => $email], [
            'email' => ['required' => true, 'type' => 'email']
        ]);
        
        if (!empty($errors)) {
            return $this->jsonResponse(['success' => false, 'message' => 'Email inválido']);
        }
        
        // Generar token de restablecimiento
        $token = $this->auth->generateResetToken($email);
        
        if ($token) {
            // Aquí se enviaría el email con el token
            // Por ahora solo retornamos éxito
            return $this->jsonResponse([
                'success' => true, 
                'message' => 'Se ha enviado un enlace de restablecimiento a tu email'
            ]);
        } else {
            return $this->jsonResponse([
                'success' => false, 
                'message' => 'No se encontró una cuenta con ese email'
            ]);
        }
    }
    
    /**
     * Mostrar formulario de nueva contraseña
     */
    public function resetPassword($token = null)
    {
        if (!$token) {
            $this->redirect('/login');
        }
        
        $data = [
            'title' => 'Nueva Contraseña',
            'token' => $token,
            'csrf_token' => $this->middleware->generateCSRFToken()
        ];
        
        return $this->view->renderWithLayout('auth/reset-password', $data);
    }
    
    /**
     * Procesar nueva contraseña
     */
    public function processResetPassword()
    {
        if (!$this->isPost()) {
            $this->redirect('/login');
        }
        
        $token = $this->getPost('token');
        $password = $this->getPost('password');
        $confirmPassword = $this->getPost('confirm_password');
        $csrfToken = $this->getPost('csrf_token');
        
        // Verificar CSRF token
        if (!$this->middleware->verifyCSRFToken($csrfToken)) {
            return $this->jsonResponse(['success' => false, 'message' => 'Token CSRF inválido']);
        }
        
        // Validar datos
        $errors = $this->middleware->validateInput([
            'password' => $password,
            'confirm_password' => $confirmPassword
        ], [
            'password' => ['required' => true, 'min_length' => 8],
            'confirm_password' => ['required' => true]
        ]);
        
        if ($password !== $confirmPassword) {
            $errors['confirm_password'] = 'Las contraseñas no coinciden';
        }
        
        if (!empty($errors)) {
            return $this->jsonResponse(['success' => false, 'message' => 'Datos inválidos', 'errors' => $errors]);
        }
        
        // Restablecer contraseña
        if ($this->auth->resetPassword($token, $password)) {
            return $this->jsonResponse([
                'success' => true, 
                'message' => 'Contraseña restablecida exitosamente',
                'redirect' => '/login'
            ]);
        } else {
            return $this->jsonResponse([
                'success' => false, 
                'message' => 'Token inválido o expirado'
            ]);
        }
    }
    
    /**
     * Respuesta JSON
     */
    private function jsonResponse($data)
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}

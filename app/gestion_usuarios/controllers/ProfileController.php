<?php
/**
 * Controlador para la gestión del perfil de usuario
 */

require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../../core/Middleware.php';
require_once __DIR__ . '/../models/Auth.php';

class ProfileController extends Controller
{
    private $middleware;
    
    public function __construct()
    {
        parent::__construct();
        $this->middleware = new Middleware();
    }

    /**
     * Mostrar el perfil del usuario
     */
    public function index()
    {
        $this->middleware->requireAuth();
        
        $user = $this->middleware->getCurrentUser();
        
        // Obtener información adicional del usuario desde la base de datos
        $auth = new Auth();
        $userData = $auth->getUserById($user['id']);
        
        if (!$userData) {
            header('Location: /dashboard');
            exit;
        }

        // Si es docente, obtener información adicional
        $docenteInfo = null;
        if ($user['rol'] === 'docente') {
            $docenteInfo = $this->getDocenteInfo($user['id']);
        }

        return $this->view->renderWithLayout('profile/index', [
            'title' => 'Mi Perfil',
            'user' => $userData,
            'docenteInfo' => $docenteInfo
        ]);
    }



    /**
     * Obtener información adicional del docente
     */
    private function getDocenteInfo($userId)
    {
        try {
            $db = Database::getInstance();
            $result = $db->query(
                "SELECT * FROM docentes_info WHERE usuario_id = :user_id",
                [':user_id' => $userId]
            );
            
            return !empty($result) ? $result[0] : null;
        } catch (Exception $e) {
            error_log("Error al obtener información del docente: " . $e->getMessage());
            return null;
        }
    }
}
<?php
/**
 * Controlador para la bitácora de actividades
 */
class BitacoraController extends Controller
{
    private $db;
    
    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance();
    }
    
    public function index()
    {
        // Solo administradores pueden ver la bitácora
        if (!Middleware::checkRole(['administrador'])) {
            return $this->view->renderWithLayout('errors/403', ['title' => 'Acceso Denegado']);
        }
        
        // Registrar acceso al módulo
        ActivityLogger::logView('bitacora', null);
        
        $user = $this->getCurrentUser();
        $activities = $this->getActivities();
        
        $data = [
            'title' => 'Bitácora de Actividades',
            'user' => $user,
            'activities' => $activities
        ];
        
        return $this->view->renderWithLayout('bitacora/index', $data);
    }
    
    /**
     * Obtener actividades con paginación
     */
    private function getActivities()
    {
        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $limit = 50; // Más registros por página en la bitácora
        $offset = ($page - 1) * $limit;
        
        try {
            // Contar total
            $countSql = "SELECT COUNT(*) as total FROM logs_actividad";
            $countResult = $this->db->query($countSql);
            $total = $countResult[0]['total'] ?? 0;
            
            // Obtener actividades con paginación
            $sql = "SELECT 
                        l.id,
                        u.nombre || ' ' || u.apellido as nombre_usuario,
                        l.accion,
                        l.tabla_afectada,
                        l.registro_id,
                        l.created_at,
                        l.ip_address,
                        l.user_agent
                    FROM logs_actividad l
                    LEFT JOIN usuarios u ON l.usuario_id = u.id
                    ORDER BY l.created_at DESC
                    LIMIT $limit OFFSET $offset";
            
            $activities = $this->db->query($sql);
            
            return [
                'activities' => $activities,
                'total' => $total,
                'page' => $page,
                'limit' => $limit
            ];
        } catch (Exception $e) {
            error_log("Error loading activities: " . $e->getMessage());
            return ['activities' => [], 'total' => 0, 'page' => 1, 'limit' => $limit];
        }
    }
}


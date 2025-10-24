<?php
/**
 * Controlador del Dashboard
 */
class DashboardController extends Controller
{
    private $middleware;
    private $db;
    
    public function __construct()
    {
        parent::__construct();
        $this->middleware = new Middleware();
        $this->db = Database::getInstance();
    }
    
    /**
     * Dashboard principal
     */
    public function index()
    {
        $this->middleware->requireAuth();
        
        $user = $this->middleware->getCurrentUser();
        $stats = $this->getDashboardStats($user);
        
        $data = [
            'title' => 'Dashboard',
            'user' => $user,
            'stats' => $stats
        ];
        
        return $this->view->renderWithLayout('dashboard/index', $data);
    }
    
    /**
     * Obtener estadísticas del dashboard
     */
    private function getDashboardStats($user)
    {
        $stats = [];
        
        switch ($user['rol']) {
            case 'administrador':
                $stats = $this->getAdminStats();
                break;
            case 'coordinador':
                $stats = $this->getCoordinatorStats();
                break;
            case 'docente':
                $stats = $this->getTeacherStats($user['id']);
                break;
            case 'autoridad':
                $stats = $this->getAuthorityStats();
                break;
        }
        
        return $stats;
    }
    
    /**
     * Estadísticas para administrador
     */
    private function getAdminStats()
    {
        $stats = [];
        
        // Total de usuarios
        $sql = "SELECT COUNT(*) as total FROM usuarios WHERE activo = true";
        $result = $this->db->query($sql);
        $stats['total_usuarios'] = $result[0]['total'];
        
        // Total de docentes
        $sql = "SELECT COUNT(*) as total FROM usuarios u 
                JOIN roles r ON u.rol_id = r.id 
                WHERE r.nombre = 'docente' AND u.activo = true";
        $result = $this->db->query($sql);
        $stats['total_docentes'] = $result[0]['total'];
        
        // Total de materias
        $sql = "SELECT COUNT(*) as total FROM materias WHERE activa = true";
        $result = $this->db->query($sql);
        $stats['total_materias'] = $result[0]['total'];
        
        // Total de grupos
        $sql = "SELECT COUNT(*) as total FROM grupos WHERE activo = true";
        $result = $this->db->query($sql);
        $stats['total_grupos'] = $result[0]['total'];
        
        // Total de aulas
        $sql = "SELECT COUNT(*) as total FROM aulas WHERE activa = true";
        $result = $this->db->query($sql);
        $stats['total_aulas'] = $result[0]['total'];
        
        // Horarios activos
        $sql = "SELECT COUNT(*) as total FROM horarios WHERE activo = true";
        $result = $this->db->query($sql);
        $stats['horarios_activos'] = $result[0]['total'];
        
        // Asistencia del día
        $sql = "SELECT COUNT(*) as total FROM asistencia_docente WHERE fecha = CURRENT_DATE";
        $result = $this->db->query($sql);
        $stats['asistencia_hoy'] = $result[0]['total'];
        
        return $stats;
    }
    
    /**
     * Estadísticas para coordinador
     */
    private function getCoordinatorStats()
    {
        $stats = [];
        
        // Docentes activos
        $sql = "SELECT COUNT(*) as total FROM usuarios u 
                JOIN roles r ON u.rol_id = r.id 
                WHERE r.nombre = 'docente' AND u.activo = true";
        $result = $this->db->query($sql);
        $stats['total_docentes'] = $result[0]['total'];
        
        // Materias activas
        $sql = "SELECT COUNT(*) as total FROM materias WHERE activa = true";
        $result = $this->db->query($sql);
        $stats['total_materias'] = $result[0]['total'];
        
        // Grupos activos
        $sql = "SELECT COUNT(*) as total FROM grupos WHERE activo = true";
        $result = $this->db->query($sql);
        $stats['total_grupos'] = $result[0]['total'];
        
        // Horarios activos
        $sql = "SELECT COUNT(*) as total FROM horarios WHERE activo = true";
        $result = $this->db->query($sql);
        $stats['horarios_activos'] = $result[0]['total'];
        
        // Asistencia del día
        $sql = "SELECT COUNT(*) as total FROM asistencia_docente WHERE fecha = CURRENT_DATE";
        $result = $this->db->query($sql);
        $stats['asistencia_hoy'] = $result[0]['total'];
        
        return $stats;
    }
    
    /**
     * Estadísticas para docente
     */
    private function getTeacherStats($userId)
    {
        $stats = [];
        
        // Grupos asignados
        $sql = "SELECT COUNT(*) as total FROM grupos WHERE docente_id = :user_id AND activo = true";
        $result = $this->db->query($sql, ['user_id' => $userId]);
        $stats['grupos_asignados'] = $result[0]['total'];
        
        // Horarios asignados
        $sql = "SELECT COUNT(*) as total FROM horarios WHERE docente_id = :user_id AND activo = true";
        $result = $this->db->query($sql, ['user_id' => $userId]);
        $stats['horarios_asignados'] = $result[0]['total'];
        
        // Asistencia del día
        $sql = "SELECT COUNT(*) as total FROM asistencia_docente 
                WHERE docente_id = :user_id AND fecha = CURRENT_DATE";
        $result = $this->db->query($sql, ['user_id' => $userId]);
        $stats['asistencia_hoy'] = $result[0]['total'];
        
        // Asistencia de la semana
        $sql = "SELECT COUNT(*) as total FROM asistencia_docente 
                WHERE docente_id = :user_id AND fecha >= CURRENT_DATE - INTERVAL '7 days'";
        $result = $this->db->query($sql, ['user_id' => $userId]);
        $stats['asistencia_semana'] = $result[0]['total'];
        
        return $stats;
    }
    
    /**
     * Estadísticas para autoridad
     */
    private function getAuthorityStats()
    {
        $stats = [];
        
        // Total de docentes
        $sql = "SELECT COUNT(*) as total FROM usuarios u 
                JOIN roles r ON u.rol_id = r.id 
                WHERE r.nombre = 'docente' AND u.activo = true";
        $result = $this->db->query($sql);
        $stats['total_docentes'] = $result[0]['total'];
        
        // Total de grupos
        $sql = "SELECT COUNT(*) as total FROM grupos WHERE activo = true";
        $result = $this->db->query($sql);
        $stats['total_grupos'] = $result[0]['total'];
        
        // Asistencia del día
        $sql = "SELECT COUNT(*) as total FROM asistencia_docente WHERE fecha = CURRENT_DATE";
        $result = $this->db->query($sql);
        $stats['asistencia_hoy'] = $result[0]['total'];
        
        // Porcentaje de asistencia del mes
        $sql = "SELECT 
                    COUNT(*) as total_clases,
                    SUM(CASE WHEN estado = 'presente' THEN 1 ELSE 0 END) as clases_presentes
                FROM asistencia_docente 
                WHERE fecha >= DATE_TRUNC('month', CURRENT_DATE)";
        $result = $this->db->query($sql);
        
        if ($result[0]['total_clases'] > 0) {
            $stats['porcentaje_asistencia'] = round(
                ($result[0]['clases_presentes'] / $result[0]['total_clases']) * 100, 2
            );
        } else {
            $stats['porcentaje_asistencia'] = 0;
        }
        
        return $stats;
    }
    
    /**
     * Obtener datos para gráficos
     */
    public function getChartData()
    {
        $this->middleware->requireAuth();
        
        $user = $this->middleware->getCurrentUser();
        $chartType = $this->getGet('type');
        
        $data = [];
        
        switch ($chartType) {
            case 'asistencia_mensual':
                $data = $this->getAsistenciaMensualData();
                break;
            case 'horarios_por_dia':
                $data = $this->getHorariosPorDiaData();
                break;
            case 'aulas_ocupacion':
                $data = $this->getAulasOcupacionData();
                break;
        }
        
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    /**
     * Datos de asistencia mensual
     */
    private function getAsistenciaMensualData()
    {
        $sql = "SELECT 
                    DATE_TRUNC('day', fecha) as dia,
                    COUNT(*) as total_clases,
                    SUM(CASE WHEN estado = 'presente' THEN 1 ELSE 0 END) as clases_presentes
                FROM asistencia_docente 
                WHERE fecha >= CURRENT_DATE - INTERVAL '30 days'
                GROUP BY DATE_TRUNC('day', fecha)
                ORDER BY dia";
        
        $result = $this->db->query($sql);
        
        $labels = [];
        $datasets = [
            'total' => [],
            'presentes' => []
        ];
        
        foreach ($result as $row) {
            $labels[] = date('d/m', strtotime($row['dia']));
            $datasets['total'][] = (int)$row['total_clases'];
            $datasets['presentes'][] = (int)$row['clases_presentes'];
        }
        
        return [
            'labels' => $labels,
            'datasets' => $datasets
        ];
    }
    
    /**
     * Datos de horarios por día
     */
    private function getHorariosPorDiaData()
    {
        $sql = "SELECT 
                    CASE dia_semana
                        WHEN 1 THEN 'Lunes'
                        WHEN 2 THEN 'Martes'
                        WHEN 3 THEN 'Miércoles'
                        WHEN 4 THEN 'Jueves'
                        WHEN 5 THEN 'Viernes'
                        WHEN 6 THEN 'Sábado'
                        WHEN 7 THEN 'Domingo'
                    END as dia,
                    COUNT(*) as total_horarios
                FROM horarios 
                WHERE activo = true
                GROUP BY dia_semana
                ORDER BY dia_semana";
        
        $result = $this->db->query($sql);
        
        $labels = [];
        $data = [];
        
        foreach ($result as $row) {
            $labels[] = $row['dia'];
            $data[] = (int)$row['total_horarios'];
        }
        
        return [
            'labels' => $labels,
            'data' => $data
        ];
    }
    
    /**
     * Datos de ocupación de aulas
     */
    private function getAulasOcupacionData()
    {
        $sql = "SELECT 
                    a.nombre,
                    COUNT(h.id) as horarios_asignados,
                    a.capacidad
                FROM aulas a
                LEFT JOIN horarios h ON a.id = h.aula_id AND h.activo = true
                WHERE a.activa = true
                GROUP BY a.id, a.nombre, a.capacidad
                ORDER BY a.nombre";
        
        $result = $this->db->query($sql);
        
        $labels = [];
        $data = [];
        
        foreach ($result as $row) {
            $labels[] = $row['nombre'];
            $data[] = (int)$row['horarios_asignados'];
        }
        
        return [
            'labels' => $labels,
            'data' => $data
        ];
    }
}
